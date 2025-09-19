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
        'is_company_product',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'status',
        'image_url',
        'video_url',
        'video_duration',
        'description',
        'warehouse_id',
    ];

    /**
     * Seller (responsible user) relation.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Warehouse relation (legacy - for backward compatibility).
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Many-to-many relationship with warehouses.
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
                    ->withPivot('quantity')
                    ->withTimestamps();
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

    /**
     * Many-to-many relationship with sellers for company products.
     */
    public function assignedSellers()
    {
        return $this->belongsToMany(User::class, 'product_seller', 'product_id', 'seller_id')
                    ->withTimestamps();
    }

    /**
     * Calculate and update total stock quantity from all warehouses
     */
    public function updateStockQuantity()
    {
        $totalQuantity = $this->warehouses()->sum('product_warehouse.quantity');
        $this->update(['stock_quantity' => $totalQuantity]);
        return $totalQuantity;
    }

    /**
     * Get total stock quantity from all warehouses (without updating the field)
     */
    public function getTotalStockQuantity()
    {
        return $this->warehouses()->sum('product_warehouse.quantity');
    }
} 