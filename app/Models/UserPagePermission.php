<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPagePermission extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'user_page_permissions';
    protected $primaryKey = 'user_page_permissions_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';
    protected $fillable = [
        'users_id',
        'pages_id',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
        'can_share',
        'can_upload',
        'can_import',
        'can_export',

    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function page()
    {
        return $this->belongsTo(PageModel::class);
    }
}
