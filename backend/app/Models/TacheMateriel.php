<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TacheMateriel extends Pivot
{
    use HasFactory;

    protected $table = 'tache_materiel';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'tache_id',
        'materiel_id',
        'prix_materiel',
    ];

//    protected function casts(): array
//    {
//        return [
//            'quantite' => 'integer',
//        ];
//    }

    /**
     * Get the tache that owns this record.
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'tache_id', 'id');
    }

    /**
     * Get the materiel associated with this record.
     */
    public function materiel()
    {
        return $this->belongsTo(Materiel::class, 'materiel_id', 'id');
    }
}
