<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TacheMateriel extends Pivot
{
    use HasFactory;

    protected $table = 'tachemateriel';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'idTache',
        'idMateriel',
        'prixMateriel',
    ];

    protected function casts(): array
    {
        return [
            'prixMateriel' => 'decimal:2',
        ];
    }

    /**
     * Get the tache that owns this record.
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'idTache', 'id');
    }

    /**
     * Get the materiel associated with this record.
     */
    public function materiel()
    {
        return $this->belongsTo(Materiel::class, 'idMateriel', 'id');
    }
}
