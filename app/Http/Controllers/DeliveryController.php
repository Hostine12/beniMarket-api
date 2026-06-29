<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Order;
use App\Services\EscrowService;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'courier') {
            $with = ['order.items.product', 'order.items.shop', 'order.client'];

            $assigned = Delivery::with($with)
                ->where('courier_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress', 'otp_requested'])
                ->get();

            $available = Delivery::with($with)
                ->whereNull('courier_id')
                ->where('status', 'pending')
                ->whereHas('order', fn($q) => $q->where('payment_status', 'paid'))
                ->get();

            $history = Delivery::with($with)
                ->where('courier_id', $user->id)
                ->where('status', 'delivered')
                ->latest()
                ->take(20)
                ->get();

            return response()->json([
                'assigned'  => $assigned,
                'available' => $available,
                'history'   => $history,
            ]);
        }

        if ($user->role === 'admin') {
            return response()->json(
                Delivery::with(['order.client', 'courier'])->latest()->paginate(30)
            );
        }

        return response()->json(['message' => 'Non autorisé.'], 403);
    }

    public function accept(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);
        $user     = $request->user();

        if ($delivery->courier_id && $delivery->courier_id !== $user->id) {
            return response()->json(['message' => 'Livraison déjà assignée.'], 422);
        }

        $delivery->update([
            'courier_id'  => $user->id,
            'status'      => 'in_progress',
            'accepted_at' => now(),
        ]);

        $delivery->order->update(['status' => 'shipping']);

        Notification::create([
            'user_id' => $delivery->order->client_id,
            'type'    => 'delivery_assigned',
            'title'   => 'Livreur assigné',
            'body'    => 'Un livreur a pris en charge votre commande et est en route.',
            'data'    => ['order_id' => $delivery->order_id],
        ]);

        return response()->json($delivery->load(['order.client', 'order.items.product', 'order.items.shop']));
    }

    public function requestOtp(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        if ($delivery->courier_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $delivery->update([
            'status'            => 'otp_requested',
            'otp_requested_at'  => now(),
        ]);

        Notification::create([
            'user_id' => $delivery->order->client_id,
            'type'    => 'otp_requested',
            'title'   => '🚪 Livreur à votre porte — Code OTP : ' . $delivery->order->otp,
            'body'    => "Votre livreur est arrivé pour la commande {$delivery->order->reference}. Communiquez-lui le code : {$delivery->order->otp}",
            'data'    => [
                'order_id' => $delivery->order_id,
                'otp'      => $delivery->order->otp,
            ],
        ]);

        return response()->json(['message' => 'OTP envoyé au client.']);
    }

    public function verifyOtp(Request $request, $id)
    {
        $delivery = Delivery::with('order')->findOrFail($id);

        if ($delivery->courier_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate(['otp' => 'required|string']);

        if ($data['otp'] !== $delivery->order->otp) {
            return response()->json(['message' => 'Code OTP incorrect.'], 422);
        }

        $delivery->update([
            'status'           => 'delivered',
            'otp_verified_at'  => now(),
        ]);

        $order = $delivery->order;
        $order->update(['status' => 'delivered', 'received_at' => now()]);

        // Libération automatique des fonds après confirmation OTP
        $this->escrow->release($order->fresh());

        Notification::create([
            'user_id' => $order->client_id,
            'type'    => 'order_delivered',
            'title'   => 'Commande livrée !',
            'body'    => "Votre commande {$order->reference} a bien été réceptionnée. "
                       . "Confirmez la réception dans votre espace pour libérer les fonds au vendeur.",
            'data'    => ['order_id' => $delivery->order_id],
        ]);

        return response()->json(['message' => 'Livraison confirmée avec succès.']);
    }
}
