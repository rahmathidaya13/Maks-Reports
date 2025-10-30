<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolesModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'roles';
    protected $primaryKey = 'roles_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';
    protected $fillable = [
        'created_by',
        'name',
        'description',
    ];
    protected $dates    = ["deleted_at"];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function profile()
    {
        return $this->hasMany(ProfileModel::class, 'roles_id', 'roles_id');
    }
    public function permission()
    {
        return $this->hasMany(RolePermissions::class, 'roles_id', 'roles_id');
    }
}
