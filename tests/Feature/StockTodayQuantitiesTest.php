<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTodayQuantitiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create order statuses
        OrderStatus::create(['name' => 'New Order']);
        OrderStatus::create(['name' => 'Processing']);
        OrderStatus::create(['name' => 'Shipped']);
        OrderStatus::create(['name' => 'Delivered']);
        
        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_today_quantities_only_include_todays_orders(): void
    {
        // Create a product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001'
        ]);
        
        // Create a stock
        $stock = Stock::factory()->create([
            'title' => 'Test Product',
            'reference' => 'TEST001',
            'product_id' => $product->id,
            'seller_id' => $this->user->id,
            'initial_quantity' => 100
        ]);
        
        // Create yesterday's delivered order
        $yesterdayOrder = Order::factory()->create([
            'seller' => $this->user->name,
            'product_id' => $product->id,
            'quantity' => 5,
            'order_status_id' => OrderStatus::where('name', 'Delivered')->first()->id,
            'created_at' => now()->subDay(), // Yesterday
            'updated_at' => now()->subDay()
        ]);
        
        // Create today's delivered order
        $todayOrder = Order::factory()->create([
            'seller' => $this->user->name,
            'product_id' => $product->id,
            'quantity' => 3,
            'order_status_id' => OrderStatus::where('name', 'Delivered')->first()->id,
            'created_at' => now(), // Today
            'updated_at' => now()
        ]);
        
        // Create today's shipped order
        $todayShippedOrder = Order::factory()->create([
            'seller' => $this->user->name,
            'product_id' => $product->id,
            'quantity' => 2,
            'order_status_id' => OrderStatus::where('name', 'Shipped')->first()->id,
            'created_at' => now(), // Today
            'updated_at' => now()
        ]);

        // Make request to get stock list
        $response = $this->actingAs($this->user)
            ->getJson('/stocks')
            ->assertStatus(200);

        $data = $response->json();
        
        // Find our stock in the response
        $stockData = collect($data['data'])->firstWhere('id', $stock->id);
        
        // Today's delivered should only include today's order (3), not yesterday's (5)
        $this->assertEquals(3, $stockData['today_delivered_quantity']);
        
        // Today's in progress should only include today's shipped order (2)
        $this->assertEquals(2, $stockData['today_in_progress_quantity']);
    }

    public function test_today_quantities_are_zero_when_no_todays_orders(): void
    {
        // Create a product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST002'
        ]);
        
        // Create a stock
        $stock = Stock::factory()->create([
            'title' => 'Test Product',
            'reference' => 'TEST002',
            'product_id' => $product->id,
            'seller_id' => $this->user->id,
            'initial_quantity' => 100
        ]);
        
        // Create only yesterday's orders
        $yesterdayDeliveredOrder = Order::factory()->create([
            'seller' => $this->user->name,
            'product_id' => $product->id,
            'quantity' => 5,
            'order_status_id' => OrderStatus::where('name', 'Delivered')->first()->id,
            'created_at' => now()->subDay(), // Yesterday
            'updated_at' => now()->subDay()
        ]);
        
        $yesterdayShippedOrder = Order::factory()->create([
            'seller' => $this->user->name,
            'product_id' => $product->id,
            'quantity' => 3,
            'order_status_id' => OrderStatus::where('name', 'Shipped')->first()->id,
            'created_at' => now()->subDay(), // Yesterday
            'updated_at' => now()->subDay()
        ]);

        // Make request to get stock list
        $response = $this->actingAs($this->user)
            ->getJson('/stocks')
            ->assertStatus(200);

        $data = $response->json();
        
        // Find our stock in the response
        $stockData = collect($data['data'])->firstWhere('id', $stock->id);
        
        // Today's quantities should be 0 since all orders are from yesterday
        $this->assertEquals(0, $stockData['today_delivered_quantity']);
        $this->assertEquals(0, $stockData['today_in_progress_quantity']);
    }
}
