<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductRequestUserModel extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'product_request';
    protected $primaryKey = 'product_request_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'user_id',
        'product_id',
        'current_price',
        'requested_price',
        'reason',
        'status',
        'admin_note',
    ];
    protected $casts = [
        'requested_price' => 'integer',
        'current_price' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'product_id');
    }
}
