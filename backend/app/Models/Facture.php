<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $table = 'facture';

    

    protected $fillable = [
        'intervention_id',
        'fichier_path',
        'ttc',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
        ];
    }

    /**
     * Get the intervention that owns this facture.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
    }
}
