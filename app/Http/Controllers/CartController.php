<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = CartItem::with(['product.shop', 'product.category'])
            ->where('user_id', $request->user()->id)
            ->get();

        $subtotal    = $items->sum(fn($i) => $i->unit_price * $i->qty);
        $serviceFee  = round($subtotal * 0.15); // identique au calcul de la commande
        $deliveryFee = 600;                     // 600 F reversés au livreur
        $total       = $subtotal + $serviceFee + $deliveryFee;

        return response()->json([
            'items'        => $items,
            'subtotal'     => $subtotal,
            'service_fee'  => $serviceFee,
            'delivery_fee' => $deliveryFee,
            'total'        => $total,
            'count'        => $items->sum('qty'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'qty'          => 'required|integer|min:1',
            'option_label' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($data['product_id']);

        $item = CartItem::updateOrCreate(
            [
                'user_id'      => $request->user()->id,
                'product_id'   => $data['product_id'],
                'option_label' => $data['option_label'] ?? null,
            ],
            [
                'qty'        => $data['qty'],
                'unit_price' => $product->price,
                'notes'      => $data['notes'] ?? null,
            ]
        );

        return response()->json($item->load('product.shop'), 201);
    }

    public function update(Request $request, $id)
    {
        $item = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'qty'   => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->update($data);
        return response()->json($item);
    }

    public function destroy(Request $request, $id)
    {
        CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Article retiré.']);
    }

    public function clear(Request $request)
    {
        CartItem::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Panier vidé.']);
    }
}
