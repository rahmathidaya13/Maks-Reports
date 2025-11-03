<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesModel extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'roles';
    protected $primaryKey = 'roles_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dates    = ["deleted_at"];

    protected $fillable = [
        'created_by',
        'position_code',
        'name',
        'shortname',
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
        static::creating(function ($roles) {
            $roles->position_code    = self::generateUniqueCode();
        });
    }
    public static function generateUniqueCode()
    {
        $prefix = "EMP-";
        do {
            $randomNumber = str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (self::where('position_code', $code)->exists());
        // Kembalikan kode yang sudah dipastikan unik
        return $code;
    }
    public function profile()
    {
        return $this->hasMany(ProfileModel::class, 'roles_id', 'roles_id');
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
