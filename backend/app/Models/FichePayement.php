<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichePayement extends Model
{
    use HasFactory;

    protected $table = 'fiche_payement';

    protected $fillable = [
        'intervention_id',
        'ht_tache',
        'ht_materiel',
        'ht_total',
        'tva_taux',
        'tva_montant',
        'ttc',
        'fichier_path',
    ];

    /**
     * Get the intervention that owns the payment slip.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id');
    }
}
