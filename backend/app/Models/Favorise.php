<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Favorise extends Pivot
{
    use HasFactory;

    protected $table = 'favorise';

    

    protected $fillable = [
        'client_id',
        'intervenant_id',
        'service_id',
    ];

    /**
     * Get the client that favorited.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Get the intervenant that was favorited.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
    }
}
