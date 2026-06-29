<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference', 'client_id', 'status', 'subtotal', 'service_fee', 'delivery_fee', 'total',
        'delivery_name', 'delivery_phone', 'delivery_neighborhood', 'delivery_instructions',
        'delivery_coordinates', 'delivery_zone', 'delivery_distance_km', 'delivery_weight_kg',
        'items_count', 'delivery_fee_breakdown',
        'otp', 'payment_method', 'payment_operator', 'payment_phone',
        'payment_status', 'payment_reference',
        'escrow_status', 'funds_released_at', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'               => 'float',
            'service_fee'            => 'float',
            'delivery_fee'           => 'float',
            'total'                  => 'float',
            'delivery_distance_km'   => 'float',
            'delivery_weight_kg'     => 'float',
            'delivery_fee_breakdown' => 'array',
            'funds_released_at'      => 'datetime',
            'received_at'            => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function dispute()
    {
        return $this->hasOne(Dispute::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->reference = 'GM-' . strtoupper(uniqid());
        });
    }

    /** Vérifie si les fonds peuvent être libérés automatiquement. */
    public function canAutoReleaseFunds(): bool
    {
        return $this->escrow_status === 'held'
            && $this->payment_status === 'paid'
            && $this->status === 'delivered';
    }
}
