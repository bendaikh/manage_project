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
    ];

    protected $casts = [
        'invoice_date' => 'date',
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
} 