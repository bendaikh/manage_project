<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SellerInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller',
        'invoice_date',
        'order_count',
        'total_amount',
        'pdf_path',
        'is_paid',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Set the invoice date attribute
     */
    public function setInvoiceDateAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['invoice_date'] = $value->toDateString();
        } else {
            $this->attributes['invoice_date'] = Carbon::parse($value)->toDateString();
        }
    }

    /**
     * Get the invoice date attribute
     */
    public function getInvoiceDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->toDateString() : null;
    }

    /**
     * Get the user who marked this invoice as paid
     */
    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Check if the invoice is paid
     */
    public function isPaid()
    {
        return $this->is_paid;
    }
} 