<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesModel extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'roles';
    protected $primaryKey = 'roles_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dates    = ["deleted_at"];

    protected $fillable = [
        'created_by',
        'name',
        'description',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function profile()
    {
        return $this->hasMany(ProfileModel::class, 'roles_id', 'roles_id');
    }

    public function permissions()
    {
        return $this->hasMany(PagePermissionModel::class, 'roles_id', 'roles_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
