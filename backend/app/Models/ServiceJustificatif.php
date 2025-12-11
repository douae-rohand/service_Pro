<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ServiceJustificatif extends Pivot
{
    use HasFactory;

    protected $table = 'servicejustificatif';

    

    protected $fillable = [
        'idService',
        'idJustificatif',
    ];

    /**
     * Get the service that owns this record.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'idService', 'id');
    }

    /**
     * Get the justificatif associated with this record.
     */
    public function justificatif()
    {
        return $this->belongsTo(Justificatif::class, 'idJustificatif', 'id');
    }
}
