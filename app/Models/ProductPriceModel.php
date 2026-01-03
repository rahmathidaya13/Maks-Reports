<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPriceModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'product_price';
    protected $primaryKey = 'product_price_id';

    protected $fillable = [
        'product_id',
        'created_by',
        'branch_id',
        'valid_from',
        'valid_until',
        'base_price',
        'discount_price',
        'price_type',
    ];

    protected $dates = ['deleted_at'];
    protected $casts = [
        'valid_from'  => 'date',
        'valid_until' => 'date',
        'base_price' => 'integer',
        'discount_price' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'product_id');
    }
    public function branch()
    {
        return $this->belongsTo(BranchesModel::class, 'branch_id', 'branches_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function getIsActiveAttribute(): bool
    {
        $today = today();

        return $this->valid_from <= $today
            && (is_null($this->valid_until) || $this->valid_until >= $today);
    }
}
