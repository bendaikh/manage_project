<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'stock_id',
        'quantity',
        'transfer_date',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'transfer_date' => 'date',
    ];

    /**
     * Get the warehouse that the transfer is from.
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the warehouse that the transfer is to.
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the stock being transferred.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the user that created the transfer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
