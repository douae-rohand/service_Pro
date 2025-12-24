<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Reclamation extends Model
{
    use HasFactory;

    protected $table = 'reclamations';

    protected $fillable = [
        'signale_par_id',
        'signale_par_type',
        'concernant_id',
        'concernant_type',
        'intervention_id',
        'raison',
        'message',
        'priorite',
        'statut',
        'notes_admin',
        'archived',
    ];

    /**
     * Boot the model and set up morph map for short class names.
     */
    protected static function boot()
    {
        parent::boot();

        // Map short names to full class names for morphTo relationships
        Relation::enforceMorphMap([
            'Client' => Client::class,
            'Intervenant' => Intervenant::class,
        ]);
    }

    /**
     * Get the user who reported the reclamation.
     */
    public function signalePar()
    {
        return $this->morphTo('signale_par', 'signale_par_type', 'signale_par_id');
    }

    /**
     * Get the user who is concerned by the reclamation.
     */
    public function concernant()
    {
        return $this->morphTo('concernant', 'concernant_type', 'concernant_id');
    }

    /**
     * Get the intervention related to this reclamation.
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id');
    }

    /**
     * Scope a query to only include pending reclamations.
     */
    public function scopePending($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Mutator for signale_par_type: ensure first letter is uppercase
     */
    public function setSignaleParTypeAttribute($value)
    {
        $this->attributes['signale_par_type'] = ucfirst($value);
    }

    /**
     * Mutator for concernant_type: ensure first letter is uppercase
     */
    public function setConcernantTypeAttribute($value)
    {
        if ($value === null) {
            $this->attributes['concernant_type'] = null;
            return;
        }
        
        $this->attributes['concernant_type'] = ucfirst($value);
    }
}
