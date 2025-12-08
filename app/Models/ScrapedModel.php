<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapedModel extends Model
{
    use HasFactory;
    protected $table = 'scraped_products';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'price',
        'link',
        'image_link',
        'category'
    ];
}
