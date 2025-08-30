<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'product_id',
        'shipment_id',
        'title',
        'reference',
        'barcode',
        'quantity',
        'initial_quantity',
        'delivered_quantity',
        'damaged_quantity',
        'in_progress_quantity',
        'remaining_quantity',
        'description',
        'purchase_price',
        'selling_price',
        'status',
        'warehouse_location',
        'warehouse_id',
        'last_updated_by',
        'last_updated_at',
        'product_link',
        'photo',
        'notes',
    ];

    protected $casts = [
        'initial_quantity' => 'integer',
        'delivered_quantity' => 'integer',
        'damaged_quantity' => 'integer',
        'in_progress_quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'last_updated_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_stock')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // Helper method to recalculate remaining quantity
    public function recalculateRemainingQuantity()
    {
        $this->remaining_quantity = $this->initial_quantity - 
            ($this->delivered_quantity + $this->damaged_quantity + $this->in_progress_quantity);
        
        // Update status based on remaining quantity
        if ($this->remaining_quantity <= 0) {
            $this->status = 'out_of_stock';
        } elseif ($this->remaining_quantity <= 5) {
            $this->status = 'low_stock';
        } else {
            $this->status = 'in_stock';
        }
        
        return $this;
    }

    /**
     * Sync stock with product data
     */
    public function syncWithProduct()
    {
        if ($this->product) {
            $this->title = $this->product->name;
            $this->description = $this->product->description;
            $this->purchase_price = $this->product->purchase_price;
            $this->selling_price = $this->product->selling_price;
        }
    }

    /**
     * Update stock quantities based on order status changes
     */
    public function updateFromOrder(Order $order, $oldStatus = null)
    {
        $quantity = $order->quantity;
        
        // If order was previously delivered, remove from delivered count
        if ($oldStatus && $oldStatus->name === 'Delivered') {
            $this->delivered_quantity = max(0, $this->delivered_quantity - $quantity);
        }
        
        // If order was previously shipped, remove from in_progress count
        if ($oldStatus && $oldStatus->name === 'Shipped') {
            $this->in_progress_quantity = max(0, $this->in_progress_quantity - $quantity);
        }
        
        // Update based on new status
        switch ($order->orderStatus->name) {
            case 'Delivered':
                $this->delivered_quantity += $quantity;
                break;
            case 'Shipped':
                $this->in_progress_quantity += $quantity;
                break;
        }
        
        $this->recalculateRemainingQuantity();
        $this->last_updated_by = auth()->user()->name ?? 'System';
        $this->last_updated_at = now();
        $this->save();
    }
} 