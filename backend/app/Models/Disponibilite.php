<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    use HasFactory;

    protected $table = 'disponibilite';

    protected $fillable = [
        'intervenant_id',
        'type',
        'jours_semaine',
        'date_specific',
        'heure_debut',
        'heure_fin',
    ];

    protected $casts = [
        'date_specific' => 'date',
        'heure_debut' => 'string',
        'heure_fin' => 'string',
    ];

    /**
     * Get the intervenant that owns this disponibilite.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
    }
}