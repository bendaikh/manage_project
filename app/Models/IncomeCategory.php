<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    /**
     * Get the incomes for this category.
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }
} 