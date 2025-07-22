<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
} 