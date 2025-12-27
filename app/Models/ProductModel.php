<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'created_by',
        'source',
        'status',
        'slug',
        'name',
        'price_original',
        'price_discount',
        'link',
        'image_link',
        'image_url',
        'image_path',
        'category',
        'description',
    ];

    protected $dates = ['deleted_at'];
    protected $casts = [
        'image_url' => 'array',
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
}
