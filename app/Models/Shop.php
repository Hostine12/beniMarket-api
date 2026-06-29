<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'name', 'slug', 'description', 'city', 'address', 'opening_hours',
        'phone', 'logo', 'cover', 'status', 'rejection_reason', 'documents_submitted',
    ];

    protected function casts(): array
    {
        return [
            'documents_submitted' => 'boolean',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
