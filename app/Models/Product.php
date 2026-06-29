<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'category_id', 'name', 'description', 'price',
        'stock', 'images', 'tags', 'options', 'status', 'rating', 'reviews_count',
    ];

    protected function casts(): array
    {
        return [
            'images'        => 'array',
            'tags'          => 'array',
            'options'       => 'array',
            'price'         => 'float',
            'rating'        => 'float',
            'stock'         => 'integer',
            'reviews_count' => 'integer',
        ];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
