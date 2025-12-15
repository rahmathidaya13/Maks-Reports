<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchPhone extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'branch_phone';
    protected $primaryKey = 'branch_phone_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branches_id',
        'phone',
    ];

    public function branch()
    {
        return $this->belongsTo(BranchesModel::class, 'branches_id', 'branches_id');
    }
}
