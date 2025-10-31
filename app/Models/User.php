<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $incrementing = false;
    protected $dates     = ["deleted_at"];
    public $keyType      = 'string';

    protected $fillable = [
        'name',
        'google_id',
        'email',
        'email_verified_at',
        'password',
        'level',
        'is_active',
        'remember_token',
        'created_at',
        'updated_at',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
        'can_share',
        'can_upload',
        'can_import',
        'can_export',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'google_id',
        'email_verified_at',
        'password',
        'remember_token',
        'is_active',
        'role',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function profile()
    {
        return $this->hasOne(ProfileModel::class, 'users_id', 'id');
    }
}
