<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $table = 'facture';
    
    protected $fillable = [
        'num_facture',
        'fichier_path',
        'ttc',
        'intervention_id',
    ];

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    protected $primaryKey = 'num_facture';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected function casts(): array
    {
        return [
            'ttc' => 'decimal:2',
        ];
    }
}
