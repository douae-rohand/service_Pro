<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin';

    protected $primaryKey = 'id';

    public $incrementing = false;

    

    protected $fillable = [];

    /**
     * Get the utilisateur record associated with the admin.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id', 'id');
    }

    /**
     * Get the clients managed by this admin.
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'adminId', 'id');
    }

    /**
     * Get the intervenants managed by this admin.
     */
    public function intervenants()
    {
        return $this->hasMany(Intervenant::class, 'adminId', 'id');
    }
}
