<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Vérifie la disponibilité du stock pour une liste d'articles.
     * Lance une ValidationException si un article est indisponible.
     *
     * @param  Collection  $items  Collection d'objets avec product_id, qty
     * @throws ValidationException
     */
    public function checkAvailability(Collection $items): void
    {
        foreach ($items as $item) {
            $product = $item->product ?? Product::find($item->product_id);

            if (!$product) {
                throw ValidationException::withMessages([
                    'items' => ["Produit introuvable (ID: {$item->product_id})."],
                ]);
            }

            if ($product->status === 'out_of_stock' || $product->stock <= 0) {
                throw ValidationException::withMessages([
                    'items' => ["Le produit « {$product->name} » est en rupture de stock."],
                ]);
            }

            if ($product->stock < $item->qty) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Stock insuffisant pour « {$product->name} ». "
                        . "Disponible : {$product->stock}, demandé : {$item->qty}.",
                    ],
                ]);
            }
        }
    }

    /**
     * Décrémente le stock après validation de commande.
     * Marque le produit en rupture si stock atteint 0.
     *
     * @param  Collection  $items
     */
    public function decrementStock(Collection $items): void
    {
        foreach ($items as $item) {
            $product = $item->product ?? Product::find($item->product_id);
            if (!$product) {
                continue;
            }

            $newStock = max(0, $product->stock - $item->qty);
            $product->update([
                'stock'  => $newStock,
                'status' => $newStock === 0 ? 'out_of_stock' : $product->status,
            ]);
        }
    }

    /**
     * Restaure le stock en cas d'annulation ou de remboursement.
     *
     * @param  Collection  $items
     */
    public function restoreStock(Collection $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if (!$product) {
                continue;
            }

            $newStock = $product->stock + $item->qty;
            $product->update([
                'stock'  => $newStock,
                'status' => $product->status === 'out_of_stock' ? 'active' : $product->status,
            ]);
        }
    }
}
