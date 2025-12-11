<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    use HasFactory;

    protected $table = 'justificatif';

    

    protected $fillable = [
        'nom',
        'description',
        'type',
    ];

    /**
     * Get the services that require this justificatif.
     */
    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'servicejustificatif',
            'justificatifId',
            'serviceId'
        )->withTimestamps();
    }
}
