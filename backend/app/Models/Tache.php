<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    use HasFactory;

    protected $table = 'tache';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'service_id',
        'nom_tache',
        'description',
        'status',
    ];

    /**
     * Get the service that owns this tache.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    /**
     * Get the interventions for this tache.
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'tacheId', 'id');
    }

    /**
     * Get the materiels required for this tache.
     */
    public function materiels()
    {
        return $this->belongsToMany(
            Materiel::class,
            'tache_materiel',
            'tache_id',
            'materiel_id'
        )->withPivot('prix_materiel')
            ->withTimestamps();
    }

    /**
     * Get the intervenants who can perform this tache.
     */
    public function intervenants()
    {
        return $this->belongsToMany(
            Intervenant::class,
            'intervenant_tache',
            'tache_id',
            'intervenant_id'
        )->withPivot('prix_tache', 'status')
            ->withTimestamps();
    }
}
