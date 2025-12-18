<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoIntervention extends Model
{
    use HasFactory;

    protected $table = 'photo_intervention';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'intervention_id',
        'photo_path', // changed from url to match migration
        'phase_prise', 
        'description',
        'phase_prise',
    ];

    /**
     * Get the intervention that owns this photo.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
    }
}
