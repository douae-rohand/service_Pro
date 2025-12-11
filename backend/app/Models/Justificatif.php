<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    use HasFactory;

    protected $table = 'justificatif';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

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
            'service_justificatif',
            'justificatif_id',
            'service_id'
        )->withPivot('created_at', 'updated_at');
    }
}
