<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeeklySellerInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller',
        'week_start_date',
        'week_end_date',
        'order_count',
        'total_amount',
        'pdf_path',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Set the week start date attribute
     */
    public function setWeekStartDateAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['week_start_date'] = $value->toDateString();
        } else {
            $this->attributes['week_start_date'] = Carbon::parse($value)->toDateString();
        }
    }

    /**
     * Set the week end date attribute
     */
    public function setWeekEndDateAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['week_end_date'] = $value->toDateString();
        } else {
            $this->attributes['week_end_date'] = Carbon::parse($value)->toDateString();
        }
    }

    /**
     * Get the week start date attribute
     */
    public function getWeekStartDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->toDateString() : null;
    }

    /**
     * Get the week end date attribute
     */
    public function getWeekEndDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->toDateString() : null;
    }

    /**
     * Get the user who approved this invoice
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the week period as a string
     */
    public function getWeekPeriodAttribute()
    {
        $start = Carbon::parse($this->week_start_date)->format('M d');
        $end = Carbon::parse($this->week_end_date)->format('M d, Y');
        return "{$start} - {$end}";
    }

    /**
     * Check if the invoice is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the invoice is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the invoice is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}