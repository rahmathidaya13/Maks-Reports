<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'created_by',
        'name',
        'price_original',
        'price_discount',
        'link',
        'image_link',
        'image_url',
        'category'
    ];
    protected $casts = [
        'image_url' => 'array',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
