<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'service';
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom_service',
        'nom_service',
        'description',
        'status',
    ];

    /**
     * Get the materiels for this service.
     */
    public function materiels()
    {
        return $this->hasMany(Materiel::class, 'service_id', 'id');
    }

    /**
     * Get the taches for this service.
     */
    public function taches()
    {
        return $this->hasMany(Tache::class, 'service_id', 'id');
    }

    /**
     * Get the informations for this service.
     */
    public function informations()
    {
        return $this->belongsToMany(
            Information::class,
            'service_information',
            'service_id',
            'information_id'
        )->withPivot('created_at', 'updated_at');
    }

    /**
     * Get the justificatifs for this service.
     */
    public function justificatifs()
    {
        return $this->belongsToMany(
            Justificatif::class,
            'service_justificatif',
            'service_id',
            'justificatif_id'
        )->withPivot('created_at', 'updated_at');
    }

    /**
     * Get the intervenants that provide this service.
     */
    public function intervenants()
    {
        return $this->belongsToMany(
            Intervenant::class,
            'intervenant_service',
            'service_id',
            'intervenant_id'
        )->withPivot('status', 'experience', 'presentation')
         ->withTimestamps();
    }
}
