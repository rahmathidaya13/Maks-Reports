<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageModel extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'pages';
    protected $primaryKey = 'pages_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'slug',
    ];

    public function permissions()
    {
        return $this->hasMany(PermissionModel::class);
    }
}
