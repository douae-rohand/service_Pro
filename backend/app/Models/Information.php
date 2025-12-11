<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;

    protected $table = 'information';

    

    protected $fillable = [
        'nom',
        'description',
        'type',
    ];

    /**
     * Get the services that require this information.
     */
    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'serviceinformation',
            'informationId',
            'serviceId'
        )->withTimestamps();
    }

    /**
     * Get the interventions that have this information.
     */
    public function interventions()
    {
        return $this->belongsToMany(
            Intervention::class,
            'interventioninformation',
            'informationId',
            'interventionId'
        )->withPivot('valeur')
            ->withTimestamps();
    }
}
