<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materiel extends Model
{
    use HasFactory;

    protected $table = 'materiel';

    

    protected $fillable = [
        'nom',
        'description',
    ];

    /**
     * Get the taches that require this materiel.
     */
    public function taches()
    {
        return $this->belongsToMany(
            Tache::class,
            'tache_materiel',
            'materiel_id',
            'tache_id'
        )->withPivot('prix_materiel')
            ->withTimestamps();
    }

    /**
     * Get the intervenants who own this materiel.
     */
    public function intervenants()
    {
        return $this->belongsToMany(
            Intervenant::class,
            'intervenant_materiel',
            'materiel_id',
            'intervenant_id'
        )->withTimestamps();
    }

    /**
     * Get the interventions that use this materiel.
     */
    public function interventions()
    {
        return $this->belongsToMany(
            Intervention::class,
            'intervention_materiel',
            'materiel_id',
            'intervention_id'
        )->withTimestamps();
    }
}
