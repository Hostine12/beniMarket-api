<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeEvidence extends Model
{
    // « evidence » étant un nom indénombrable en anglais, l'inflecteur de Laravel
    // déduit la table « dispute_evidence » alors que la migration crée « dispute_evidences ».
    // On fixe explicitement le nom pour éviter une erreur SQL au chargement de la relation.
    protected $table = 'dispute_evidences';

    protected $fillable = [
        'dispute_id', 'uploader_id', 'uploader_role',
        'file_path', 'file_name', 'mime_type', 'file_size', 'description',
    ];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
