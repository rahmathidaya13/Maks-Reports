<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchesModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'branches';
    protected $primaryKey = 'branches_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';
    protected $fillable = [
        'created_by',
        'name',
        'address',
        'phone',
        'status',
    ];
    protected $dates    = ["deleted_at"];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function profile()
    {
        return $this->hasMany(ProfileModel::class, 'branches_id', 'branches_id');
    }
}
