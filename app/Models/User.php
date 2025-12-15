<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $incrementing = false;
    protected $dates = ["deleted_at"];
    public $keyType = 'string';
    protected $guard_name = 'web';
    protected $fillable = [
        'name',
        'google_id',
        'email',
        'email_verified_at',
        'password',
        'is_active',
        'status',
        'first_login',
        'last_login',
        'remember_token',
        'created_at',
        'updated_at',
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
    public function dailyReport()
    {
        return $this->hasMany(DailyReportModel::class, 'created_by', 'id');
    }
    public function reportStoryUpdate()
    {
        return $this->hasMany(StoryStatusReportModel::class, 'created_by', 'id');
    }

    public function products()
    {
        return $this->hasMany(ProductModel::class, 'created_by', 'id');
    }

    public function branches()
    {
        return $this->hasMany(BranchesModel::class, 'created_by', 'id');
    }

    public function jobTitle()
    {
        return $this->hasMany(JobTitleModel::class, 'created_by', 'id');
    }
}
