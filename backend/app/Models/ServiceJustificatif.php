<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ServiceJustificatif extends Pivot
{
    use HasFactory;

    protected $table = 'servicejustificatif';

    

    protected $fillable = [
        'service_id',
        'justificatif_id',
    ];

    /**
     * Get the service that owns this record.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    /**
     * Get the justificatif associated with this record.
     */
    public function justificatif()
    {
        return $this->belongsTo(Justificatif::class, 'justificatif_id', 'id');
    }
}
