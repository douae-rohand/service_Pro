<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InterventionMateriel extends Pivot
{
    use HasFactory;

    protected $table = 'interventionmateriel';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'idIntervention',
        'idMateriel',
    ];

    /**
     * Get the intervention that owns this record.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'idIntervention', 'id');
    }

    /**
     * Get the materiel associated with this record.
     */
    public function materiel()
    {
        return $this->belongsTo(Materiel::class, 'idMateriel', 'id');
    }
}
