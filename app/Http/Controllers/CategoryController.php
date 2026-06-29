<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /** Liste toutes les catégories racine avec leurs sous-catégories. */
    public function index()
    {
        $categories = Category::root()
            ->with(['subcategories' => fn($q) => $q->withCount('products')])
            ->withCount('products')
            ->get();

        return response()->json($categories);
    }

    /** Détail d'une catégorie avec ses sous-catégories et le nombre de produits. */
    public function show($id)
    {
        $category = Category::with(['subcategories' => fn($q) => $q->withCount('products')])
            ->withCount('products')
            ->findOrFail($id);

        return response()->json($category);
    }

    /**
     * Liste les vendeurs proposant des produits dans une (sous-)catégorie.
     * Retourne : shop info + produits + prix min/max + note moyenne.
     */
    public function sellers(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Inclure aussi les produits des sous-catégories
        $categoryIds = collect([$category->id])
            ->merge($category->subcategories()->pluck('id'));

        $shops = Shop::with([
            'vendor:id,name,avatar',
            'products' => fn($q) => $q->whereIn('category_id', $categoryIds)
                ->where('status', 'active')
                ->select(['id', 'shop_id', 'category_id', 'name', 'price', 'stock', 'images', 'rating', 'reviews_count']),
        ])
        ->whereHas('products', fn($q) =>
            $q->whereIn('category_id', $categoryIds)->where('status', 'active')
        )
        ->where('status', 'active')
        ->get()
        ->map(function (Shop $shop) {
            $products       = $shop->products;
            $shop->min_price   = $products->min('price');
            $shop->max_price   = $products->max('price');
            $shop->avg_rating  = round($products->avg('rating'), 1);
            $shop->total_stock = $products->sum('stock');
            return $shop;
        });

        return response()->json([
            'category' => $category,
            'sellers'  => $shops,
        ]);
    }

    /** Produits d'une sous-catégorie avec filtre vendeur optionnel. */
    public function products(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $query = Product::with(['shop.vendor:id,name,avatar'])
            ->where('category_id', $id)
            ->where('status', 'active');

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $query->orderByDesc($request->get('sort', 'rating'));

        return response()->json([
            'category' => $category,
            'products' => $query->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|unique:categories,slug',
            'icon'        => 'nullable|string',
            'accent'      => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        return response()->json(Category::create($data), 201);
    }
}
