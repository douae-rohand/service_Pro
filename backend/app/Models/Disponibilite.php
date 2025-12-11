<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    use HasFactory;

    protected $table = 'disponibilite';

    

    protected $fillable = [
        'intervenantId',
        'dateDebut',
        'dateFin',
        'periode',
    ];

    protected function casts(): array
    {
        return [
            'dateDebut' => 'date',
            'dateFin' => 'date',
        ];
    }

    /**
     * Get the intervenant that owns this disponibilite.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenantId', 'id');
    }
}
