<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    use HasFactory;

    protected $table = 'justificatif';

    protected $fillable = [
        'intervenant_id',
        'type',
        'chemin_fichier',
    ];

    /**
     * Get the intervenant that owns this justificatif.
     */
    public function intervenant()
    {
        return $this->belongsToMany(
            Service::class,
            'service_justificatif',
            'justificatif_id',
            'service_id'
        )->withPivot('created_at', 'updated_at');
    }
}
