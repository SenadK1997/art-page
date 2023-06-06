<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Tymon\JWTAuth\Contracts\JWTSubject;

class Admins extends Authenticatable
{
    use HasFactory;
    protected $guard = 'admins';
    protected $fillable = [
        'username',
        'password',
    ];
    // public function getJWTIdentifier()
    // {
    //     return $this->getKey();
    // }
    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }
}
