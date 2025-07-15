<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\OrderStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the "New Order" status
        $newOrderStatus = OrderStatus::where('name', 'New Order')->first();
        
        if ($newOrderStatus) {
            // Update any orders that don't have a valid status
            Order::whereNull('order_status_id')
                ->orWhereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                          ->from('order_statuses')
                          ->whereRaw('order_statuses.id = orders.order_status_id');
                })
                ->update(['order_status_id' => $newOrderStatus->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just fixing data integrity
    }
};
