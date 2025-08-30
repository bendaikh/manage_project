<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'belongs_to',
        'warehouse_id',
    ];

    protected $appends = ['status'];

    protected static function boot()
    {
        parent::boot();

        // When order status is updated, update corresponding stock
        static::updated(function ($order) {
            if ($order->wasChanged('order_status_id')) {
                $oldStatus = OrderStatus::find($order->getOriginal('order_status_id'));
                $newStatus = $order->orderStatus;
                
                // Find stock for this product and seller
                $stock = Stock::where('product_id', $order->product_id)
                    ->where('seller_id', $order->belongs_to)
                    ->first();
                
                if ($stock) {
                    $stock->updateFromOrder($order, $oldStatus);
                }
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the assignment for this order.
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(OrderAssignment::class);
    }

    /**
     * Get the assigned agent for this order.
     */
    public function assignedAgent()
    {
        return $this->hasOneThrough(User::class, OrderAssignment::class, 'order_id', 'id', 'id', 'assigned_to');
    }

    /**
     * Get the status name from the orderStatus relationship
     */
    public function getStatusAttribute()
    {
        return $this->orderStatus?->name ?? 'New Order';
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