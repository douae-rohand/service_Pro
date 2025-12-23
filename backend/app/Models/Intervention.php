<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Intervention extends Model
{
    use HasFactory;

    protected $table = 'intervention';
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'address',
        'ville',
        'status',
        'description',
        'date_intervention',
        'duration_hours',  
        'client_id',
        'intervenant_id',
        'tache_id',
    ];

    protected function casts(): array
    {
        return [
            'date_intervention' => 'datetime',
            'duration_hours' => 'decimal:2',
        ];
    }

    /**
     * Get the client that owns this intervention.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Get the intervenant that performs this intervention.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
    }

    /**
     * Get the tache for this intervention.
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'tache_id', 'id');
    }

    /**
     * Get the photos for this intervention.
     */
    public function photos()
    {
        return $this->hasMany(PhotoIntervention::class, 'intervention_id', 'id');
    }

    /**
     * Get the evaluations for this intervention.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'intervention_id', 'id');
    }

    /**
     * Get the client evaluation for this intervention.
     */
    public function evaluation()
    {
        return $this->hasOne(Evaluation::class, 'intervention_id', 'id')->where('type_auteur', 'client');
    }

    /**
     * Get the commentaires for this intervention.
     */
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'intervention_id', 'id');
    }

    /**
     * Get the facture for this intervention.
     */
    public function facture()
    {
        return $this->hasOne(Facture::class, 'intervention_id', 'id');
    }

    /**
     * Get the payment slip for this intervention.
     */
    public function fichePayement()
    {
        return $this->hasOne(FichePayement::class, 'intervention_id', 'id');
    }

    /**
     * Get the informations for this intervention.
     */
    public function informations()
    {
        return $this->belongsToMany(
            Information::class,
            'intervention_information',
            'intervention_id',
            'information_id'
        )->withPivot('information', 'created_at', 'updated_at')
         ->withTimestamps();
    }

    /**
     * Get the materiels used in this intervention.
     */
    public function materiels()
    {
        return $this->belongsToMany(
            Materiel::class,
            'intervention_materiel',
            'intervention_id',
            'materiel_id'
        )->withTimestamps();
    }

    /**
     * Get the reclamations for this intervention.
     */
    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'intervention_id', 'id');
    }

    /**
     * Scope a query to filter interventions by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include upcoming interventions.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date_intervention', '>=', now())
            ->orderBy('date_intervention', 'asc');
    }

    /**
     * Scope a query to only include completed interventions.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'termine');
    }

    /**
     * Scope a query to only include pending interventions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'en attend');
    }

    /**
     * Determine if ratings for this intervention are public.
     * Rules:
     * 1. Both parties have rated.
     * 2. OR 7 days have passed since the intervention was finished.
     */
    public function areRatingsPublic(): bool
    {
        if ($this->status !== 'termine') {
            return false;
        }

        // Check if both parties have rated (using count to avoid multiple select queries if possible, 
        // but exists() is fine for now as we don't have many evaluations per intervention)
        $intervenantRated = $this->evaluations()->where('type_auteur', 'intervenant')->exists();
        $clientRated = $this->evaluations()->where('type_auteur', 'client')->exists();
        
        if ($intervenantRated && $clientRated) {
            return true;
        }

        // Check 7-day window
        return $this->updated_at->addDays(7)->isPast();
    }
}
