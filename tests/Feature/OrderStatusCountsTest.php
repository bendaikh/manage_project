<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class OrderStatusCountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_counts_show_only_todays_orders()
    {
        // Create order statuses
        $newOrderStatus = OrderStatus::create(['name' => 'New Order']);
        $confirmedStatus = OrderStatus::create(['name' => 'Confirmed']);
        $deliveredStatus = OrderStatus::create(['name' => 'Delivered']);

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'purchase_price' => 800,
            'selling_price' => 1000,
            'stock_quantity' => 10,
            'status' => 'active',
            'description' => 'Test product description'
        ]);

        // Create orders from today
        $today = Carbon::today();
        $todayOrder1 = Order::create([
            'seller' => 'Test Seller 1',
            'product_id' => $product->id,
            'quantity' => 1,
            'client_name' => 'Test Client 1',
            'price' => 1000,
            'client_address' => 'Test Address 1',
            'order_status_id' => $newOrderStatus->id,
            'created_at' => $today->copy()->setTime(10, 30),
        ]);

        $todayOrder2 = Order::create([
            'seller' => 'Test Seller 2',
            'product_id' => $product->id,
            'quantity' => 2,
            'client_name' => 'Test Client 2',
            'price' => 2000,
            'client_address' => 'Test Address 2',
            'order_status_id' => $confirmedStatus->id,
            'created_at' => $today->copy()->setTime(14, 15),
        ]);

        // Create orders from yesterday (should not be counted)
        $yesterday = Carbon::yesterday();
        $yesterdayOrder = Order::create([
            'seller' => 'Test Seller 3',
            'product_id' => $product->id,
            'quantity' => 1,
            'client_name' => 'Test Client 3',
            'price' => 1000,
            'client_address' => 'Test Address 3',
            'order_status_id' => $deliveredStatus->id,
            'created_at' => $yesterday->copy()->setTime(10, 30),
        ]);

        // Make request to get orders list
        $response = $this->get('/orders/list');

        $response->assertStatus(200);
        $data = $response->json();

        // Verify that status counts only include today's orders
        $statusCounts = $data['status_counts'];
        
        // Should have 1 "New Order" from today
        $this->assertEquals(1, $statusCounts['New Order'] ?? 0);
        
        // Should have 1 "Confirmed" from today
        $this->assertEquals(1, $statusCounts['Confirmed'] ?? 0);
        
        // Should have 0 "Delivered" (yesterday's order should not be counted)
        $this->assertEquals(0, $statusCounts['Delivered'] ?? 0);
    }
} 