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

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id',
        'address',
        'ville',
        'bio',
        'is_active',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the utilisateur record associated with the intervenant.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id', 'id');
    }

    /**
     * Get the admin that manages this intervenant.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
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
     * Get the services that this intervenant offers.
     */
    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'intervenant_service',
            'intervenant_id',
            'service_id'
        )->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Get the services that this intervenant offers.
     
        *public function services()
        *{
            *return $this->belongsToMany(
                *Service::class,
                *'intervenant_service',
                *'intervenant_id',
                *'service_id'
            *)->withPivot('prix_tache', 'status', 'created_at', 'updated_at');
        *}
    **/

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
        // Utiliser directement la colonne intervenant_id pour éviter les problèmes de relation
        $interventionIds = \App\Models\Intervention::where('intervenant_id', $this->id)
            ->pluck('id');
        
        if ($interventionIds->isEmpty()) {
            return [
                'average_rating' => 0,
                'review_count' => 0
            ];
        }
        
        // Get all client evaluations for these interventions
        // Utiliser les noms de colonnes réels de la base de données (snake_case)
        $evaluations = \App\Models\Evaluation::whereIn('intervention_id', $interventionIds)
            ->where('type_auteur', 'client')
            ->get();
        
        $reviewCount = $evaluations->count();
        $averageRating = $reviewCount > 0 ? round($evaluations->avg('note'), 1) : 0;
        
        return [
            'average_rating' => $averageRating,
            'review_count' => $reviewCount
        ];
    }
}
