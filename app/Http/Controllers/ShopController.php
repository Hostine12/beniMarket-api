<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shop::with('vendor')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->city, fn($q) => $q->where('city', $request->city))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->where('status', 'active')
            ->latest()
            ->paginate(20);

        return response()->json($shops);
    }

    public function show($id)
    {
        $shop = Shop::with(['vendor', 'products.category'])->findOrFail($id);
        return response()->json($shop);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'vendor') {
            return response()->json(['message' => 'Réservé aux vendeurs.'], 403);
        }

        if (Shop::where('vendor_id', $user->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà une boutique.'], 422);
        }

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:255',
            'opening_hours'       => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'logo'                => 'nullable|string',
            'cover'               => 'nullable|string',
            'documents_submitted' => 'boolean',
        ]);

        $data['vendor_id'] = $user->id;
        $data['slug']      = Str::slug($data['name']) . '-' . $user->id;
        $data['status']    = 'pending';

        $shop = Shop::create($data);

        return response()->json($shop, 201);
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);
        $user = $request->user();

        if ($shop->vendor_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'name'                => 'sometimes|string|max:255',
            'description'         => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:255',
            'opening_hours'       => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'logo'                => 'nullable|string',
            'cover'               => 'nullable|string',
            'documents_submitted' => 'boolean',
        ]);

        $shop->update($data);

        return response()->json($shop);
    }

    public function destroy(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);
        $user = $request->user();

        if ($shop->vendor_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $shop->delete();
        return response()->json(['message' => 'Boutique supprimée.']);
    }

    public function myShop(Request $request)
    {
        $shop = Shop::where('vendor_id', $request->user()->id)
            ->with(['products.category'])
            ->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée.'], 404);
        }

        return response()->json($shop);
    }
}
