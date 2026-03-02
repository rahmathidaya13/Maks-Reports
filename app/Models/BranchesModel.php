<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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
        'branch_code',
        'name',
        'address',
        'status',
        'status_official',
    ];
    protected $dates    = ["deleted_at"];
    protected $hidden = [
        'deleted_at',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($branch) {
            $branch->branch_code    = self::generateUniqueCode();
        });
    }
    public static function generateUniqueCode()
    {
        do {
            $randomNumber = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $code = $randomNumber;
        } while (self::where('branch_code', $code)->exists());
        // Kembalikan kode yang sudah dipastikan unik
        return $code;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function profile()
    {
        return $this->hasMany(ProfileModel::class, 'branches_id', 'branches_id');
    }
    public function branchPhone()
    {
        return $this->hasMany(BranchPhone::class, 'branches_id', 'branches_id');
    }

    public function syncPhones(array $phones)
    {
        DB::transaction(function () use ($phones) {
            $phones = collect($phones)
                ->filter()
                ->map(fn($p) => trim($p))
                ->unique()
                ->values();

            $this->branchPhone()->delete();

            foreach ($phones as $phone) {
                $this->branchPhone()->create([
                    'phone' => $phone,
                ]);
            }
        });
    }
    public function transactions()
    {
        return $this->hasManyThrough(
            TransactionModel::class, // 1. Model Target (Transaksi)
            ProfileModel::class,     // 2. Model Perantara (Profile)
            'branches_id',             // 3. Foreign key di tabel profiles (yg menunjuk ke cabang)
            'created_by',            // 4. Foreign key di tabel transactions (yg menunjuk ke user_id)
            'branches_id',           // 5. Primary key di tabel branches
            'users_id'                // 6. Foreign key di tabel profiles (yg di-referensi oleh transactions)
        );
    }
    public function product()
    {
        return $this->hasMany(ProductModel::class, 'branch_id', 'branches_id');
    }
}
