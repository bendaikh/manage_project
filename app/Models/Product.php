<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'supplier',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'status',
        'image_url',
        'video_url',
        'video_duration',
        'description',
    ];
} 