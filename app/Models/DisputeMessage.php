<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeMessage extends Model
{
    protected $fillable = ['dispute_id', 'sender_id', 'sender_role', 'message', 'is_internal'];

    protected function casts(): array
    {
        return ['is_internal' => 'boolean'];
    }

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
