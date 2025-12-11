<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'service';

    protected $fillable = [
        'nom_service',
        'description',
    ];

    /**
     * Get the taches for this service.
     */
    public function taches()
    {
        return $this->hasMany(Tache::class, 'service_id', 'id');
    }

    /**
     * Get the informations required for this service.
     */
    public function informations()
    {
        return $this->belongsToMany(
            Information::class,
            'serviceinformation',
            'service_id',
            'information_id'
        )->withTimestamps();
    }

    /**
     * Get the justificatifs required for this service.
     */
    public function justificatifs()
    {
        return $this->belongsToMany(
            Justificatif::class,
            'servicejustificatif',
            'service_id',
            'justificatif_id'
        )->withTimestamps();
    }
}
