<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    use HasFactory;

    protected $table = 'disponibilite';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'heure_debut',
        'heure_fin',
        'date_specific',
        'jours_semaine',
        'intervenant_id',
    ];

    protected $casts = [
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
        'date_specific' => 'date',
    ];

    /**
     * Get the intervenant that owns this disponibilite.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
    }
}
