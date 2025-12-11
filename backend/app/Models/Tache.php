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
        'idService',
        'nomTache',
        'description',
        'status',
    ];

    /**
     * Get the service that owns this tache.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'idService', 'id');
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
            'tachemateriel',
            'idTache',
            'idMateriel'
        )->withPivot('prixMateriel')
            ->withTimestamps();
    }

    /**
     * Get the intervenants who can perform this tache.
     */
    public function intervenants()
    {
        return $this->belongsToMany(
            Intervenant::class,
            'intervenanttache',
            'idTache',
            'idIntervenant'
        )->withPivot('prixTache', 'status')
            ->withTimestamps();
    }
}
