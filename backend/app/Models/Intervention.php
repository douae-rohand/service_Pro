<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Intervention extends Model
{
    use HasFactory;

    protected $table = 'intervention';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'address',
        'ville',
        'status',
        'dateIntervention',
        'clientId',
        'intervenantId',
        'tacheId',
    ];

    protected function casts(): array
    {
        return [
            'dateIntervention' => 'date',
        ];
    }

    /**
     * Get the client that owns this intervention.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId', 'id');
    }

    /**
     * Get the intervenant that performs this intervention.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenantId', 'id');
    }

    /**
     * Get the tache associated with this intervention.
     */
    public function tache()
    {
        return $this->belongsTo(Tache::class, 'tacheId', 'id');
    }

    /**
     * Get the photos for this intervention.
     */
    public function photos()
    {
        return $this->hasMany(PhotoIntervention::class, 'interventionId', 'id');
    }

    /**
     * Get the evaluations for this intervention.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'interventionId', 'id');
    }

    /**
     * Get the commentaires for this intervention.
     */
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'interventionId', 'id');
    }

    /**
     * Get the facture for this intervention.
     */
    public function facture()
    {
        return $this->hasOne(Facture::class, 'interventionId', 'id');
    }

    /**
     * Get the informations for this intervention.
     */
    public function informations()
    {
        return $this->belongsToMany(
            Information::class,
            'interventioninformation',
            'idIntervention',
            'idInformation'
        )->withPivot('information')
            ->withTimestamps();
    }

    /**
     * Get the materiels used in this intervention.
     */
    public function materiels()
    {
        return $this->belongsToMany(
            Materiel::class,
            'interventionmateriel',
            'idIntervention',
            'idMateriel'
        )->withTimestamps();
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
        return $query->where('dateIntervention', '>=', now()->toDateString())
            ->orderBy('dateIntervention', 'asc');
    }

    /**
     * Scope a query to only include completed interventions.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'terminée');
    }

    /**
     * Scope a query to only include pending interventions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'en attente');
    }
}
