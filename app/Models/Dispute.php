<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'order_id', 'client_id', 'vendor_id', 'courier_id',
        'opened_by_id', 'opened_by_role', 'reason', 'description',
        'priority', 'status', 'resolution_type', 'refund_amount',
        'admin_note', 'resolved_at', 'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at'       => 'datetime',
            'last_activity_at'  => 'datetime',
            'refund_amount'     => 'float',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by_id');
    }

    public function messages()
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at');
    }

    public function evidences()
    {
        return $this->hasMany(DisputeEvidence::class);
    }
}
