<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IntervenantTache extends Pivot
{
    use HasFactory;

    protected $table = 'intervenanttache';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'idIntervenant',
        'idTache',
        'prixTache',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'prixTache' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the intervenant that owns this record.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'idIntervenant', 'id');
    }

    /**
     * Get the tache associated with this record.
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'idTache', 'id');
    }
}
