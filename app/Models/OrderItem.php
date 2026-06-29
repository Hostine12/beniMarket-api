<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'shop_id', 'product_name',
        'option_label', 'unit_price', 'qty', 'total', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
            'total'      => 'float',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
