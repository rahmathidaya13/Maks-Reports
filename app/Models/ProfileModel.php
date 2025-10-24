<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfileModel extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'profile';
    protected $primaryKey = 'profile_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'users_id',
        'roles_id',
        'branches_id',
        'date_of_entry',
        'birthdate',
        'education',
        'gender',
        'number_phone',
        'address',
        'images',
    ];

    // 🔹 Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
    public function branch()
    {
        return $this->belongsTo(BranchesModel::class, 'branches_id', 'branches_id');
    }
    public function role()
    {
        return $this->belongsTo(RolesModel::class, 'roles_id', 'roles_id');
    }
}
