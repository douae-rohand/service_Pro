<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Intervenant extends Model
{
    use HasFactory;

    protected $table = 'intervenant';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'address',
        'ville',
        'bio',
        'is_active',
        'admin_id',
    ];

    /**
     * Get the utilisateur record associated with the intervenant.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id', 'id');
    }

    /**
     * Get the interventions for this intervenant.
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'intervenant_id', 'id');
    }

    /**
     * Get the disponibilites for this intervenant.
     */
    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class, 'intervenant_id', 'id');
    }

    /**
     * Get the services that this intervenant provides.
     */
    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'intervenant_service',
            'intervenant_id',
            'service_id'
        )->withPivot('status', 'experience', 'presentation')
         ->withTimestamps();
    }

    /**
     * Get the taches that this intervenant can perform.
     */
    public function taches()
    {
        return $this->belongsToMany(
            Tache::class,
            'intervenant_tache',
            'intervenant_id',
            'tache_id'
        )->withPivot('prix_tache', 'status')
            ->withTimestamps();
    }

    /**
     * Get the materiels owned by this intervenant.
     */
    public function materiels()
    {
        return $this->belongsToMany(
            Materiel::class,
            'intervenant_materiel',
            'intervenant_id',
            'materiel_id'
        )->withTimestamps();
    }

    /**
     * Get the clients who favorited this intervenant.
     */
    public function clientsFavoris()
    {
        return $this->belongsToMany(
            Client::class,
            'favorise',
            'intervenant_id',
            'client_id'
        )->withPivot('created_at', 'updated_at');
    }

    /**
     * Get the admin that manages this intervenant.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    /**
     * Get the justificatifs for this intervenant.
     */
    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class, 'intervenant_id', 'id');
    }

    /**
     * Get all photos from interventions performed by this intervenant.
     */
    public function photosInterventions()
    {
        return $this->hasManyThrough(
            PhotoIntervention::class,
            Intervention::class,
            'intervenant_id', // Foreign key on interventions table
            'intervention_id', // Foreign key on photo_intervention table
            'id', // Local key on intervenants table
            'id' // Local key on interventions table
        );
    }

    /**
     * Scope a query to only include active intervenants.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive intervenants.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the average rating and review count for this intervenant.
     * Calculate from evaluations where type_auteur is 'client'
     */
    public function getRatingInfo()
    {
        // Get all interventions for this intervenant
        $interventionIds = \App\Models\Intervention::where('intervenant_id', $this->id)
            ->pluck('id');
        
        if ($interventionIds->isEmpty()) {
            return [
                'average_rating' => 0,
                'review_count' => 0
            ];
        }
        
        // Get all client evaluations for these interventions
        $evaluations = \App\Models\Evaluation::whereIn('intervention_id', $interventionIds)
            ->where('type_auteur', 'client')
            ->get();
        
        $reviewCount = $evaluations->pluck('intervention_id')->unique()->count();
        $averageRating = $evaluations->count() > 0 ? round($evaluations->avg('note'), 1) : 0;
        
        return [
            'average_rating' => $averageRating,
            'review_count' => $reviewCount
        ];
    }

    /**
     * Get all evaluations for this intervenant through their interventions.
     */
    public function evaluations()
    {
        return $this->hasManyThrough(
            Evaluation::class,
            Intervention::class,
            'intervenant_id', // Foreign key on intervention table
            'intervention_id', // Foreign key on evaluation table
            'id', // Local key on intervenant table
            'id' // Local key on intervention table
        );
    }
}
