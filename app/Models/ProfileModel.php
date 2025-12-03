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
        'employee_id_number',
        'national_id_number',
        'branches_id',
        'job_title_id',
        'date_of_entry',
        'employment_status',
        'birthdate',
        'birthplace',
        'education',
        'major',
        'gender',
        'number_phone',
        'address',
        'postal_code',
        'images',
        'is_completed',
    ];

    // public static function booted()
    // {
    //     static::saved(function ($profile) {
    //         if ($profile->jobTitle && $profile->user) {
    //             $profile->user->syncRoles([$profile->jobTitle->title]);
    //         }
    //     });
    // }

    // 🔹 Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitleModel::class, 'job_title_id', 'job_title_id');
    }

    public function branch()
    {
        return $this->belongsTo(BranchesModel::class, 'branches_id', 'branches_id');
    }
}
