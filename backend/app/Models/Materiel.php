<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materiel extends Model
{
    use HasFactory;

    protected $table = 'materiel';

    protected $fillable = [
        'nom_materiel',
        'description',
        'service_id',
    ];

    /**
     * Get the service that owns this materiel.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

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
        )->withTimestamps();
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
