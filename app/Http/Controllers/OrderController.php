<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Services\DeliveryFeeService;
use App\Services\EscrowService;
use App\Services\StockService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private StockService       $stock,
        private DeliveryFeeService $deliveryFee,
        private EscrowService      $escrow,
    ) {}

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Order::with(['items.product', 'items.shop', 'client', 'delivery.courier', 'payment'])->latest();

        if ($user->role === 'client') {
            $query->where('client_id', $user->id);
        } elseif ($user->role === 'vendor') {
            $query->whereHas('items', fn($q) =>
                $q->whereHas('shop', fn($q2) => $q2->where('vendor_id', $user->id))
            );
        }
        // admin → toutes les commandes

        return response()->json($query->paginate(20));
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'items.shop', 'client', 'delivery.courier', 'payment', 'dispute'])
            ->findOrFail($id);

        $user = $request->user();
        if ($user->role === 'client' && $order->client_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
        // Un vendeur ne peut consulter qu'une commande contenant l'un de ses produits.
        if ($user->role === 'vendor'
            && !$order->items()->whereHas('shop', fn($q) => $q->where('vendor_id', $user->id))->exists()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($order);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'delivery_name'          => 'required|string|max:255',
            'delivery_phone'         => 'required|string|max:20',
            'delivery_neighborhood'  => 'nullable|string|max:255',
            'delivery_instructions'  => 'nullable|string|max:500',
            'delivery_coordinates'   => 'nullable|string|max:100',
            'delivery_zone'          => 'nullable|in:center,suburb,rural,remote',
            'delivery_distance_km'   => 'nullable|numeric|min:0',
            'delivery_weight_kg'     => 'nullable|numeric|min:0',
            'payment_method'         => 'required|in:mobile_money,cash',
            'payment_operator'       => 'nullable|string',
            'payment_phone'          => 'nullable|string|max:20',
            'items'                  => 'nullable|array|min:1',
            'items.*.product_id'     => 'required_with:items|integer|exists:products,id',
            'items.*.qty'            => 'required_with:items|integer|min:1',
            'items.*.notes'          => 'nullable|string|max:500',
        ]);

        // Résolution des articles (localStorage frontend ou cart_items DB)
        if (!empty($data['items'])) {
            $orderItems = collect($data['items'])->map(function ($item) {
                $product = Product::findOrFail($item['product_id']);
                return (object) [
                    'product_id'   => $product->id,
                    'product'      => $product,
                    'unit_price'   => $product->price,
                    'qty'          => $item['qty'],
                    'option_label' => null,
                    'notes'        => $item['notes'] ?? null,
                ];
            });
        } else {
            $dbItems = CartItem::with('product')->where('user_id', $user->id)->get();
            if ($dbItems->isEmpty()) {
                return response()->json(['message' => 'Votre panier est vide.'], 422);
            }
            $orderItems = $dbItems;
        }

        // ── Vérification du stock avant toute création ──────────────
        $this->stock->checkAvailability($orderItems);

        // ── Calcul du total ─────────────────────────────────────────
        $subtotal    = $orderItems->sum(fn($i) => $i->unit_price * $i->qty);
        $serviceFee  = round($subtotal * 0.15);
        $itemsCount  = $orderItems->sum(fn($i) => $i->qty);

        $feeResult = $this->deliveryFee->calculate(
            distanceKm: (float) ($data['delivery_distance_km'] ?? 3),
            weightKg:   (float) ($data['delivery_weight_kg']   ?? 1),
            itemsCount: (int)   $itemsCount,
            zone:       (string)($data['delivery_zone']        ?? 'center'),
        );

        $deliveryFee = $feeResult['client_share']; // le client paie sa part
        $total       = $subtotal + $serviceFee + $deliveryFee;
        $otp         = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            ...$data,
            'client_id'            => $user->id,
            'subtotal'             => $subtotal,
            'service_fee'          => $serviceFee,
            'delivery_fee'         => $deliveryFee,
            'total'                => $total,
            'otp'                  => $otp,
            'items_count'          => $itemsCount,
            'delivery_fee_breakdown' => $feeResult['breakdown'],
            'status'               => 'pending',
            'escrow_status'        => 'held',
        ]);

        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item->product_id,
                'shop_id'      => $item->product->shop_id ?? null,
                'product_name' => $item->product->name,
                'option_label' => $item->option_label ?? null,
                'unit_price'   => $item->unit_price,
                'qty'          => $item->qty,
                'total'        => $item->unit_price * $item->qty,
                'notes'        => $item->notes ?? null,
            ]);
        }

        // ── Décrémentation du stock ─────────────────────────────────
        $this->stock->decrementStock($orderItems);

        Delivery::create(['order_id' => $order->id]);
        CartItem::where('user_id', $user->id)->delete();

        Notification::create([
            'user_id' => $user->id,
            'type'    => 'order_created',
            'title'   => 'Commande passée avec succès',
            'body'    => "Votre commande {$order->reference} a été enregistrée.",
            'data'    => ['order_id' => $order->id, 'reference' => $order->reference],
        ]);

        // Notifier chaque vendeur concerné par la commande.
        $shopIds   = $orderItems->map(fn($i) => $i->product->shop_id)->filter()->unique();
        $vendorIds = Shop::whereIn('id', $shopIds)->pluck('vendor_id')->filter()->unique();
        foreach ($vendorIds as $vendorId) {
            Notification::create([
                'user_id' => $vendorId,
                'type'    => 'order_received',
                'title'   => 'Nouvelle commande reçue 🛒',
                'body'    => "Vous avez reçu une nouvelle commande ({$order->reference}). Ouvrez-la pour voir les produits, les quantités et l'adresse de livraison.",
                'data'    => ['order_id' => $order->id, 'reference' => $order->reference],
            ]);
        }

        return response()->json($order->load(['items', 'delivery']), 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $user  = $request->user();

        $allowed = ['confirmed', 'preparing', 'shipping', 'delivered', 'cancelled'];
        $data    = $request->validate(['status' => 'required|in:' . implode(',', $allowed)]);

        $order->update(['status' => $data['status']]);

        // Confirmation de réception client → libération automatique des fonds
        if ($data['status'] === 'delivered') {
            $order->update(['received_at' => now()]);
            $this->escrow->release($order->fresh());
        }

        // Annulation → restauration du stock + remboursement
        if ($data['status'] === 'cancelled' && $order->payment_status === 'paid') {
            app(\App\Services\StockService::class)->restoreStock($order->items);
            $this->escrow->refund($order->fresh());
        }

        Notification::create([
            'user_id' => $order->client_id,
            'type'    => 'order_status',
            'title'   => 'Mise à jour de commande',
            'body'    => "Votre commande {$order->reference} est maintenant : {$data['status']}.",
            'data'    => ['order_id' => $order->id, 'status' => $data['status']],
        ]);

        return response()->json($order->load(['items', 'delivery']));
    }

    /**
     * Le client confirme la réception de sa commande → libère les fonds.
     */
    public function confirmReceived(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $user  = $request->user();

        if ($order->client_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (!in_array($order->status, ['shipping', 'delivered'])) {
            return response()->json(['message' => 'La commande ne peut pas être confirmée pour le moment.'], 422);
        }

        if ($order->escrow_status === 'released') {
            return response()->json(['message' => 'Réception déjà confirmée.'], 422);
        }

        $order->update(['status' => 'delivered', 'received_at' => now()]);
        $this->escrow->release($order->fresh());

        return response()->json([
            'message' => 'Réception confirmée. Les fonds ont été libérés au vendeur.',
            'order'   => $order->fresh(['items', 'delivery']),
        ]);
    }

    /**
     * Devis des frais de livraison (appelé depuis le checkout frontend).
     */
    public function deliveryFeeQuote(Request $request)
    {
        $params = $request->validate([
            'distance_km'  => 'nullable|numeric|min:0',
            'weight_kg'    => 'nullable|numeric|min:0',
            'items_count'  => 'nullable|integer|min:1',
            'zone'         => 'nullable|in:center,suburb,rural,remote',
        ]);

        $result = $this->deliveryFee->quote($params);

        return response()->json($result);
    }
}
