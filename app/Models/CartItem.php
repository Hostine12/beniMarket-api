<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'qty', 'option_label', 'unit_price', 'notes'];

    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->with(['shop', 'category']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
