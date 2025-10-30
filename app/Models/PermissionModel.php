<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'permissions';
    protected $primaryKey = 'permissions_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';
    protected $fillable = [
        'pages_id',
        'key',
        'label',
    ];

    public function pages()
    {
        return $this->belongsTo(PageModel::class);
    }
}
