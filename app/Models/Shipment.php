<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'title',
        'reference',
        'quantity',
        'description',
        'link',
        'photo',
        'shipment_date',
        'customs_fees',
        'status',
        'validated',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'customs_fees' => 'decimal:2',
        'validated' => 'boolean',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
} 