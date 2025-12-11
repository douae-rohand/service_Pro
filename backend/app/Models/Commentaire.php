<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    use HasFactory;

    protected $table = 'commentaire';

    

    protected $fillable = [
        'commentaire',
        'intervention_id',
        'type_auteur',
    ];

    /**
     * Get the intervention that owns this commentaire.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
    }
}
