<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    use HasFactory;

    protected $table = 'disponibilite';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $primaryKey = 'id';
    public $timestamps = true;


    protected $fillable = [
        'intervenant_id',
        'type',
        'jours_semaine',
        'date_specific',
        'heure_debut',
        'heure_fin',
    ];

    protected function casts(): array
    {
        return [
            'heure_debut' => 'datetime:H:i:s',
            'heure_fin' => 'datetime:H:i:s',
            'date_specific' => 'date',
        ];
    }

    /**
     * Get the intervenant that owns this disponibilite.
     */
    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class, 'intervenant_id', 'id');
    }
}