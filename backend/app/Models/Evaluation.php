<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Evaluation extends Model
{
    use HasFactory;

    protected $table = 'evaluation';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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

    /**
     * Scope a query to only include evaluations by intervenants.
     */
    public function scopeByIntervenant(Builder $query): Builder
    {
        return $query->where('type_auteur', 'intervenant');
    }

    /**
     * Scope a query to only include evaluations by clients.
     */
    public function scopeByClient(Builder $query): Builder
    {
        return $query->where('type_auteur', 'client');
    }

    /**
     * Check if evaluation can be created by intervenant for this intervention.
     */
    public static function canIntervenantRate(int $interventionId): bool
    {
        $intervention = Intervention::find($interventionId);
        return $intervention && $intervention->status === 'termine';
    }
}
