<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Grand livre du portefeuille : chaque ligne est un mouvement (crédit/débit)
 * destiné à un utilisateur (vendeur ou livreur). Le solde se calcule comme la
 * somme signée des mouvements ; l'historique correspond à la liste des lignes.
 */
class WalletTransaction extends Model
{
    protected $fillable = ['user_id', 'order_id', 'type', 'reason', 'amount', 'description'];

    protected function casts(): array
    {
        return ['amount' => 'float'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** Montant signé : négatif pour un débit, positif pour un crédit. */
    public function getSignedAmountAttribute(): float
    {
        return $this->type === 'debit' ? -$this->amount : $this->amount;
    }
}
