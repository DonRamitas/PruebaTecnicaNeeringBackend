<?php

// Modelo de usuario

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'birthdate',
        'phone',
        'password',
        'profile_image', //No se utiliza
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Obtiene el JWT del usuario
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
