<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Critaire extends Model
{
    use HasFactory;

    protected $table = 'critaire';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom_critaire',
    ];

    /**
     * Get the evaluations for this critaire.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'critaire_id', 'id');
    }
}
