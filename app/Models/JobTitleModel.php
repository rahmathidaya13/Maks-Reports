<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class JobTitleModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'job_title';
    protected $primaryKey = 'job_title_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dates    = ["deleted_at"];
    protected $fillable = [
        'created_by',
        'role_id',
        'job_title_code',
        'title',
        'slug',
        'title_alias',
        'description',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($job) {
            $job->job_title_code    = self::generateUniqueCode();
        });
    }
    public static function generateUniqueCode()
    {
        $prefix = "EMP-";
        do {
            $randomNumber = str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (self::where('job_title_code', $code)->exists());
        // Kembalikan kode yang sudah dipastikan unik
        return $code;
    }
    public function profile()
    {
        return $this->hasMany(ProfileModel::class, 'job_title_id', 'job_title_id');
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
