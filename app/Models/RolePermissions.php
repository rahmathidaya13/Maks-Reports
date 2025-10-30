<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'role_permissions';
    protected $primaryKey = 'role_permissions_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';
    protected $fillable = [
        'roles_id',
        'permission_key',
        'value',
    ];
    protected $hidden = [
        'created_at',
        'deleted_at',
    ];
    public function roles()
    {
        return $this->belongsTo(RolesModel::class, 'roles_id', 'roles_id');
    }
}
