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
        return $this->hasMany(Tache::class, 'idService', 'id');
    }

    /**
     * Get the informations required for this service.
     */
    public function informations()
    {
        return $this->belongsToMany(
            Information::class,
            'serviceinformation',
            'idService',
            'idInformation'
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
            'idService',
            'idJustificatif'
        )->withTimestamps();
    }
}
