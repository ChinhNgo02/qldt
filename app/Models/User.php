<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'birthday',
        'email',
        'password',
        'currentPassword',
        'rePassword',
        'isChangePassword',
        'level',
        'added_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'currentPassword',
        'rePassword',
        'isChangePassword',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->hasMany('App\Models\Attendance', 'user_id');
    }

    public function notification()
    {
        return $this->hasMany('App\Models\Notification', 'user_id');
    }

    public function scores()
    {
        return $this->hasMany('App\Models\Scores', 'user_id');
    }
}