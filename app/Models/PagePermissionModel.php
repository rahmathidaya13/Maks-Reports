<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagePermissionModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'page_permissions';
    protected $primaryKey = 'page_permissions_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'roles_id',
        'name',
        'slug',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
        'can_export',
        'can_import',
        'can_share',
    ];

    public function role()
    {
        return $this->belongsTo(RolesModel::class, 'roles_id', 'roles_id');
    }
}
