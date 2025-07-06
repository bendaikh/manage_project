<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DeliveryInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_invoice_generates_for_todays_delivered_orders()
    {
        // Create order statuses
        $deliveredStatus = OrderStatus::create(['name' => 'Delivered']);
        $confirmedStatus = OrderStatus::create(['name' => 'Confirmed']);

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

        // Create delivered orders from today
        $today = Carbon::today();
        $deliveredOrder1 = Order::create([
            'seller' => 'Test Seller 1',
            'product_id' => $product->id,
            'quantity' => 1,
            'client_name' => 'Test Client 1',
            'price' => 1000,
            'client_address' => 'Test Address 1',
            'order_status_id' => $deliveredStatus->id,
            'updated_at' => $today->copy()->setTime(10, 30), // 10:30 AM today
        ]);

        $deliveredOrder2 = Order::create([
            'seller' => 'Test Seller 2',
            'product_id' => $product->id,
            'quantity' => 2,
            'client_name' => 'Test Client 2',
            'price' => 2000,
            'client_address' => 'Test Address 2',
            'order_status_id' => $deliveredStatus->id,
            'updated_at' => $today->copy()->setTime(14, 15), // 2:15 PM today
        ]);

        // Create a confirmed order (should not be included)
        $confirmedOrder = Order::create([
            'seller' => 'Test Seller 3',
            'product_id' => $product->id,
            'quantity' => 1,
            'client_name' => 'Test Client 3',
            'price' => 1000,
            'client_address' => 'Test Address 3',
            'order_status_id' => $confirmedStatus->id,
            'updated_at' => $today,
        ]);

        // Create a delivered order from yesterday (should not be included)
        $yesterday = Carbon::yesterday();
        $oldDeliveredOrder = Order::create([
            'seller' => 'Test Seller 4',
            'product_id' => $product->id,
            'quantity' => 1,
            'client_name' => 'Test Client 4',
            'price' => 1000,
            'client_address' => 'Test Address 4',
            'order_status_id' => $deliveredStatus->id,
            'updated_at' => $yesterday,
        ]);

        // Generate delivery invoice
        $response = $this->get('/orders/delivery-invoice');

        // Check that the response is successful (PDF download)
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_delivery_invoice_returns_404_when_no_delivered_orders_today()
    {
        // Create order statuses
        $confirmedStatus = OrderStatus::create(['name' => 'Confirmed']);

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

        // Create only confirmed orders (no delivered orders)
        $today = Carbon::today();
        $confirmedOrder = Order::create([
            'seller' => 'Test Seller',
            'product_id' => $product->id,
            'quantity' => 1,
            'client_name' => 'Test Client',
            'price' => 1000,
            'client_address' => 'Test Address',
            'order_status_id' => $confirmedStatus->id,
            'updated_at' => $today,
        ]);

        // Try to generate delivery invoice
        $response = $this->get('/orders/delivery-invoice');

        // Should return 404
        $response->assertStatus(404);
    }
} 