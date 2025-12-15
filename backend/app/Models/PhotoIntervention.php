<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoIntervention extends Model
{
    use HasFactory;

    protected $table = 'photo_intervention';

    protected $fillable = [
        'intervention_id',
        'photo_path',
        'phase_prise',
        'description',
    ];

    /**
     * Get the intervention that owns this photo.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
    }
}
