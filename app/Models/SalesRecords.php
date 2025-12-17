<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesRecords extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'sales_records';
    protected $primaryKey = 'sales_record_id';
    public $incrementing = false; // Karena pakai UUID
    protected $keyType = 'string';
    protected $fillable = [
        'product_id',
        'created_by',
        'sales_record_code',
        'sale_date',
        'customer_source',
        'purchase_channel',
        'notes',
    ];
    protected $dates    = ["deleted_at"];
    protected $hidden = [
        'deleted_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'product_id');
    }
}
