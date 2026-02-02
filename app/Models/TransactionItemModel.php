<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItemModel extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'transaction_items';
    protected $primaryKey = 'transaction_item_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'created_by',
        'transaction_id',
        'product_id',
        'quantity',
        'price_unit',
        'discount_amount',
        'subtotal',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function transactions()
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id', 'transaction_id');
    }
    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'product_id');
    }
}
