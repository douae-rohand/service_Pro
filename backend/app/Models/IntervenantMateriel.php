<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IntervenantMateriel extends Pivot
{
    use HasFactory;

    protected $table = 'intervenant_materiel';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'intervenant_id',
        'materiel_id',
        'prix_materiel',
    ];



    /**
     * Get the intervenant that owns this record.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
    }

    /**
     * Get the materiel associated with this record.
     */
    public function materiel()
    {
        return $this->belongsTo(Materiel::class, 'materiel_id', 'id');
        return $this->belongsTo(Materiel::class, 'materiel_id', 'id');
    }
}
