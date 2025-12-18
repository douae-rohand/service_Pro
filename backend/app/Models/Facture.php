<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $table = 'facture';
    protected $primaryKey = 'num_facture';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'fichier_path',
        'ttc',
        'intervention_id',
    ];

    protected function casts(): array
    {
        return [
            'ttc' => 'decimal:2',
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
