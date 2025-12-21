<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionPayment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'transaction_payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'transaction_id',
        'amount',
        'payment_type',
        'payment_method',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function transactions()
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }
}
