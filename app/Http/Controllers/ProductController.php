<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['shop', 'category'])
            ->when($request->category, fn($q) => $q->whereHas('category', fn($q2) => $q2->where('slug', $request->category)))
            ->when($request->shop_id, fn($q) => $q->where('shop_id', $request->shop_id))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->where('status', 'active')
            ->latest()
            ->paginate(24);

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['shop.vendor', 'category'])->findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $shop = Shop::where('vendor_id', $user->id)->first();

        if (!$shop) {
            return response()->json(['message' => 'Vous devez d\'abord créer une boutique.'], 422);
        }
        if ($shop->status !== 'active') {
            return response()->json(['message' => 'Votre boutique doit être validée pour ajouter des produits.'], 403);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'images'      => 'nullable|array',
            'tags'        => 'nullable|array',
            'options'     => 'nullable|array',
        ]);

        $data['shop_id'] = $shop->id;
        $product = Product::create($data);
        $product->load(['shop', 'category']);

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user    = $request->user();
        $shop    = Shop::where('vendor_id', $user->id)->first();

        if (!$shop || $product->shop_id !== $shop->id) {
            if ($user->role !== 'admin') {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }
        }

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'images'      => 'nullable|array',
            'tags'        => 'nullable|array',
            'options'     => 'nullable|array',
            'status'      => 'sometimes|in:active,inactive,out_of_stock',
        ]);

        $product->update($data);
        return response()->json($product->load(['shop', 'category']));
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user    = $request->user();
        $shop    = Shop::where('vendor_id', $user->id)->first();

        if (!$shop || $product->shop_id !== $shop->id) {
            if ($user->role !== 'admin') {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }
        }

        $product->delete();
        return response()->json(['message' => 'Produit supprimé.']);
    }

    public function vendorProducts(Request $request)
    {
        $user = $request->user();
        $shop = Shop::where('vendor_id', $user->id)->first();

        if (!$shop) {
            return response()->json([]);
        }

        $products = Product::with('category')
            ->where('shop_id', $shop->id)
            ->latest()
            ->get();

        return response()->json($products);
    }

    /**
     * Recommandations : autres produits du même vendeur (excluant le produit courant).
     * Utilisé après un achat ou sur la page produit.
     */
    public function sellerRecommendations($id)
    {
        $product = Product::findOrFail($id);

        $recommendations = Product::with('category')
            ->where('shop_id', $product->shop_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->orderByDesc('rating')
            ->limit(8)
            ->get();

        return response()->json([
            'shop'            => $product->shop()->with('vendor:id,name,avatar')->first(),
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Recommandations post-achat : autres produits des vendeurs de la commande.
     * Appelé avec ?order_id=XX pour cibler la commande passée.
     */
    public function orderRecommendations(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);

        $order = \App\Models\Order::with('items.product')->findOrFail($request->order_id);

        if ($order->client_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $shopIds     = $order->items->pluck('shop_id')->unique()->filter();
        $productIds  = $order->items->pluck('product_id')->unique()->filter();

        $recommendations = Product::with(['shop.vendor:id,name,avatar', 'category'])
            ->whereIn('shop_id', $shopIds)
            ->whereNotIn('id', $productIds)
            ->where('status', 'active')
            ->orderByDesc('rating')
            ->limit(12)
            ->get();

        return response()->json($recommendations);
    }
}
