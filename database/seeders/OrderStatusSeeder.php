<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            'New Order',
            'Unreachable',
            'Postponed',
            'Cancelled',
            'Wrong Number',
            'Out of Stock',
            'Confirmed',
            'Confirmed on Date',
            'Blacklisted',
            'Pending Payment',
            'Processing',
            'Shipped',
            'Delivered',
        ];
        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate(['name' => $status]);
        }
    }
} 