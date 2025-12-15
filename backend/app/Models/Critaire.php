<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Critaire extends Model
{
    use HasFactory;

    protected $table = 'critaire';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'nom',
        'description',
    ];

    /**
     * Get the evaluations for this critaire.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'critaire_id', 'id');
    }
}
