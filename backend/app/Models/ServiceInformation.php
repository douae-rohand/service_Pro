<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ServiceInformation extends Pivot
{
    use HasFactory;

    protected $table = 'serviceinformation';

    

    protected $fillable = [
        'idService',
        'idInformation',
    ];

    /**
     * Get the service that owns this record.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'idService', 'id');
    }

    /**
     * Get the information associated with this record.
     */
    public function information()
    {
        return $this->belongsTo(Information::class, 'idInformation', 'id');
    }
}
