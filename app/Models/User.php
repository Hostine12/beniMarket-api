<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'status',
        'avatar', 'zone', 'vehicle_type', 'plate_number',
        'must_change_password', 'reset_token', 'reset_token_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'reset_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'must_change_password'   => 'boolean',
            'reset_token_expires_at' => 'datetime',
        ];
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'vendor_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'courier_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'client_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /** Solde courant du portefeuille (crédits − débits), en FCFA. */
    public function walletBalance(): float
    {
        return (float) $this->walletTransactions()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'debit' THEN -amount ELSE amount END), 0) AS balance")
            ->value('balance');
    }

    public function hasValidResetToken(): bool
    {
        return $this->reset_token !== null
            && $this->reset_token_expires_at !== null
            && $this->reset_token_expires_at->isFuture();
    }
}
