<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    use HasFactory;

    protected $table = 'client';

    protected $primaryKey = 'id';

    public $incrementing = false;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'address',
        'ville',
        'isActive',
        'adminId',
    ];

    protected function casts(): array
    {
        return [
            'isActive' => 'boolean',
        ];
    }

    /**
     * Get the utilisateur record associated with the client.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id', 'id');
    }

    /**
     * Get the admin that manages this client.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'adminId', 'id');
    }

    /**
     * Get the interventions for this client.
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'clientId', 'id');
    }

    /**
     * Get the intervenants favorited by this client.
     */
    public function intervenantsFavoris()
    {
        return $this->belongsToMany(
            Intervenant::class,
            'favorise',
            'client_id',
            'intervenant_id'
        )->withPivot('created_at', 'updated_at');
    }

    /**
     * Scope a query to only include active clients.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isActive', true);
    }

    /**
     * Scope a query to only include inactive clients.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('isActive', false);
    }
}
