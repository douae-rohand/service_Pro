<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IntervenantTache extends Pivot
{
    use HasFactory;

    protected $table = 'intervenant_tache';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'intervenant_id',
        'tache_id',
        'prix_tache',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'prix_tache' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the intervenant that owns this record.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
    }

    /**
     * Get the tache associated with this record.
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'tache_id', 'id');
    }
}
