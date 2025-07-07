<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller',
        'product_id',
        'quantity',
        'client_name',
        'price',
        'client_address',
        'zone',
        'client_phone',
        'comment',
        'agent',
        'order_status_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    /**
     * Calculate total price including delivery cost
     */
    public function getTotalPriceAttribute()
    {
        $deliveryPrice = Setting::getDeliveryPrice();
        return $this->price + $deliveryPrice;
    }

    /**
     * Get delivery price from settings
     */
    public function getDeliveryPriceAttribute()
    {
        return Setting::getDeliveryPrice();
    }
} 