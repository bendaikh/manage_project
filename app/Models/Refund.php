<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'amount',
        'date',
        'expense_id',
        'account_id',
        'description',
        'order_sku',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the expense that owns the refund.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the account that owns the refund.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user that created the refund.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 