<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrainte extends Model
{
    use HasFactory;

    protected $table = 'contrainte';

    protected $fillable = [
        'tache_id',
        'nom',
        'seuil',
        'unite',
    ];

    public function tache()
    {
        return $this->belongsTo(Tache::class, 'tache_id', 'id');
    }
}