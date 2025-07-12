<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'amount',
        'date',
        'description',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the account that the transfer is from.
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Get the account that the transfer is to.
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    /**
     * Get the user that created the transfer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 