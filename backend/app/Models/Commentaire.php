<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    use HasFactory;

    protected $table = 'commentaire';
    protected $primaryKey = 'id';

    // La migration utilise created_at et updated_at par défaut, donc pas besoin de redéfinir les constantes si on suit la convention Laravel
    // Si la migration a explicitement créé created_at et updated_at, Eloquent les gérera automatiquement.

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
