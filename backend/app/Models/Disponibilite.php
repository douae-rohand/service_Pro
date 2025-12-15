<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    use HasFactory;

    protected $table = 'disponibilite';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'intervenant_id',
        'heure_debut',
        'heure_fin',
        'date_specific',
        'jours_semaine',
        'type'
    ];

    protected function casts(): array
    {
        return [
            'heure_debut' => 'datetime:H:i:s',
            'heure_fin' => 'datetime:H:i:s',
            'date_specific' => 'date',
        ];
    }

    /**
     * Get the intervenant that owns this disponibilite.
     */
    public function intervenant()
    {
        return $this->belongsTo(
            Intervenant::class,
            'intervenant_id', // ✅ même colonne
            'id'
        );
    }
}
