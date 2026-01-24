<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $dates    = ["deleted_at"];
    protected $hidden = [
        'deleted_at',
    ];
    protected $fillable = [
        'created_by',
        'national_id_number',
        'customer_name',
        'number_phone_customer',
        'city',
        'province',
        'address',
        'type_bussiness'
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(TransactionModel::class, 'customer_id', 'customer_id');
    }
}
