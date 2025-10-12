<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyInvoiceAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_seller_invoice_id',
        'amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the weekly seller invoice this advance belongs to
     */
    public function weeklySellerInvoice()
    {
        return $this->belongsTo(WeeklySellerInvoice::class);
    }

    /**
     * Get the user who created this advance
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
