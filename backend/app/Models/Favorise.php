<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Favorise extends Pivot
{
    use HasFactory;

    protected $table = 'favorise';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'idClient',
        'idIntervenant',
        'idService',
    ];

    /**
     * Get the client that favorited.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'idClient', 'id');
    }

    /**
     * Get the intervenant that was favorited.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'idIntervenant', 'id');
    }
}
