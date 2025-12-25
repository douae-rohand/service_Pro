<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'utilisateur';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'url',
        'profile_photo',
        'google_pw', // Fixed: matches database column name
        'address',
        'google_id',
        'avatar',
        'email_verification_code',
        'email_verification_expires_at',
        'email_verified_at',
        'estActive',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'google_pw', // Fixed: matches database column name
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'estActive' => 'boolean',
        ];
    }

    /**
     * Get the admin record associated with the user.
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'id');
    }

    /**
     * Get the client record associated with the user.
     */
    public function client()
    {
        return $this->hasOne(Client::class, 'id', 'id');
    }

    /**
     * Get the intervenant record associated with the user.
     */
    public function intervenant()
    {
        return $this->hasOne(Intervenant::class, 'id', 'id');
    }

    /**
     * Get all of the reclamations that are reported by this user.
     */
    public function reportedReclamations()
    {
        return $this->morphMany(Reclamation::class, 'signale_par');
    }

    /**
     * Get all of the reclamations that are concerning this user.
     */
    public function concernedReclamations()
    {
        return $this->morphMany(Reclamation::class, 'concernant');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Get the user's profile photo as a full URL.
     */
    public function getProfilePhotoAttribute($value)
    {
        if (!$value) return null;
        if (strpos($value, 'http') === 0) return $value;
        
        // Prevent double storage/ path if already present
        if (strpos($value, 'storage/') === 0) {
            return url($value);
        }
        
        return url('storage/' . $value);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->admin !== null;
    }

    /**
     * Check if user is client
     */
    public function isClient()
    {
        return $this->client !== null;
    }

    /**
     * Check if user is intervenant
     */
    public function isIntervenant()
    {
        return $this->intervenant !== null;
    }
}
