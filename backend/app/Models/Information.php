<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;

    protected $table = 'information';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom',
        'description',
    ];

    /**
     * Get the services that require this information.
     */
    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'service_information',
            'information_id',
            'service_id'
        )->withPivot('created_at', 'updated_at');
    }

    /**
     * Get the interventions that have this information.
     */
    public function interventions()
    {
        return $this->belongsToMany(
            Intervention::class,
            'intervention_information',
            'information_id',
            'intervention_id'
        )->withPivot('information', 'created_at', 'updated_at');
    }
}
