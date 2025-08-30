<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'supplier',
        'seller',
        'seller_id',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'status',
        'image_url',
        'video_url',
        'video_duration',
        'description',
    ];

    /**
     * Seller (responsible user) relation.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Stocks relation.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Orders relation.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
} 