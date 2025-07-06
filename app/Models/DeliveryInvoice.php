<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_date',
        'order_count',
        'total_amount',
        'pdf_path',
    ];
}
