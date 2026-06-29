<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'order_id', 'courier_id', 'status', 'distance_km',
        'accepted_at', 'otp_requested_at', 'otp_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at'      => 'datetime',
            'otp_requested_at' => 'datetime',
            'otp_verified_at'  => 'datetime',
            'distance_km'      => 'float',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class)->with(['items', 'client']);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
