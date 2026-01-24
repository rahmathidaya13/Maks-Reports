<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'created_by',
        'source',
        'slug',
        'name',
        'item_condition',
        'link',
        'image_link',
        'image_path',
        'category',
    ];

    protected $dates = ['deleted_at'];
    protected $casts = [
        'price_original' => 'integer',
        'price_discount' => 'integer',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(TransactionModel::class, 'product_id', 'product_id');
    }
    public function prices()
    {
        return $this->hasMany(ProductPriceModel::class, 'product_id', 'product_id');
    }

    // Harga AKTIF saat ini (normal / diskon)
    public function activePrice(): HasOne
    {
        return $this->hasOne(ProductModel::class, 'product_id', 'product_id')
            ->whereDate('valid_from', '<=', today())
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', today());
            })
            ->orderByDesc('price_type'); // diskon > normal
    }

    // Harga normal terakhir
    public function normalPrice(): HasOne
    {
        return $this->hasOne(ProductModel::class, 'product_id', 'product_id')
            ->where('price_type', 'normal')
            ->latest('valid_from');
    }
}
