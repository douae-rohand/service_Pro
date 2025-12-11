<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $table = 'evaluation';

    

    protected $fillable = [
        'note',
        'intervention_id',
        'critaire_id',
        'type_auteur',
    ];

    protected function casts(): array
    {
        return [
            'note' => 'integer',
        ];
    }

    /**
     * Get the intervention that owns this evaluation.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
    }

    /**
     * Get the critaire that this evaluation is based on.
     */
    public function critaire()
    {
        return $this->belongsTo(Critaire::class, 'critaire_id', 'id');
    }
}
