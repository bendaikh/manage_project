<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
} 