<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'invoice',
        'transaction_date',
        'customer_id',
        'product_id',
        'price_original',
        'price_discount',
        'price_final',
        'status',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'customer_id');
    }
    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'product_id');
    }
    public function payments()
    {
        return $this->hasMany(TransactionPayment::class, 'transaction_id', 'transaction_id');
    }
}
