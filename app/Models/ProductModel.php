<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'created_by',
        'name',
        'price_original',
        'price_discount',
        'link',
        'image_link',
        'image_url',
        'category',
        'description',
    ];
    protected $casts = [
        'image_url' => 'array',
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
