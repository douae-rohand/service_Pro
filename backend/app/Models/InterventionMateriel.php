<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InterventionMateriel extends Pivot
{
    use HasFactory;

    protected $table = 'intervention_materiel';

    

    protected $fillable = [
        'intervention_id',
        'materiel_id',
    ];

    /**
     * Get the intervention that owns this record.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
    }

    /**
     * Get the materiel associated with this record.
     */
    public function materiel()
    {
        return $this->belongsTo(Materiel::class, 'materiel_id', 'id');
    }
}
