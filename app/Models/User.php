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
    public function pagePermissions()
    {
        return $this->hasMany(UserPagePermission::class, 'users_id', 'id');
    }

    public function hasPagePermission(string $pageSlug, string $action = 'can_view'): bool
    {
        $perm = $this->pagePermissions()
            ->whereHas('page', function ($q) use ($pageSlug) {
                $q->where('slug', $pageSlug);
            })->first();

        if (!$perm) {
            // fallback: if user has global boolean columns (can_view, can_add...) use that
            if (isset($this->{$action})) {
                return (bool) $this->{$action};
            }
            return false;
        }

        return (bool) ($perm->{$action} ?? false);
    }
}
