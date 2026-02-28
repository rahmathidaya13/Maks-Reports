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
        'status',
        'sub_total',
        'tax_percentage',
        'tax_amount',
        'grand_total',
        'cancel_reason',
        'cancelled_at',
        'cancelled_by'
    ];
    protected $dates    = ["deleted_at"];
    // Relasi ke CREATOR (User) pembuat transaksi
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    // Relasi ke ITEM BELANJA (One to Many)
    // "Satu Transaksi punya BANYAK Barang"
    public function items()
    {
        return $this->hasMany(TransactionItemModel::class, 'transaction_id', 'transaction_id');
    }
    // Relasi ke PEMBAYARAN (One to Many)
    // "Satu Transaksi bisa dibayar berkali-kali (DP, Cicilan)"
    public function payments()
    {
        return $this->hasMany(TransactionPayment::class, 'transaction_id', 'transaction_id');
    }
    // Relasi ke CUSTOMER (Many to One)
    // "Transaksi ini milik SATU Customer"
    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'customer_id');
    }
}
