<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'operator', 'phone', 'amount', 'currency',
        'fedapay_id', 'reference', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount'   => 'float',
            'metadata' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
