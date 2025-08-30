<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderBelongsToTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create order statuses
        OrderStatus::create(['name' => 'New Order']);
        OrderStatus::create(['name' => 'Confirmed']);
        OrderStatus::create(['name' => 'Processing']);
        OrderStatus::create(['name' => 'Delivered']);
        
        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_confirmation_section_filters_by_belongs_to_field(): void
    {
        // Create orders with different belongs_to values
        $confirmationOrder = Order::factory()->create([
            'belongs_to' => 'confirmation',
            'order_status_id' => OrderStatus::where('name', 'New Order')->first()->id
        ]);
        
        $deliveryOrder = Order::factory()->create([
            'belongs_to' => 'delivery',
            'order_status_id' => OrderStatus::where('name', 'Processing')->first()->id
        ]);

        $this->actingAs($this->user)
            ->getJson('/orders/list?belongs_to=confirmation')
            ->assertStatus(200)
            ->assertJsonCount(1, 'orders.data')
            ->assertJsonPath('orders.data.0.id', $confirmationOrder->id);
    }

    public function test_delivery_section_filters_by_belongs_to_field(): void
    {
        // Create orders with different belongs_to values
        $confirmationOrder = Order::factory()->create([
            'belongs_to' => 'confirmation',
            'order_status_id' => OrderStatus::where('name', 'New Order')->first()->id
        ]);
        
        $deliveryOrder = Order::factory()->create([
            'belongs_to' => 'delivery',
            'order_status_id' => OrderStatus::where('name', 'Processing')->first()->id
        ]);

        $this->actingAs($this->user)
            ->getJson('/orders/list?belongs_to=delivery')
            ->assertStatus(200)
            ->assertJsonCount(1, 'orders.data')
            ->assertJsonPath('orders.data.0.id', $deliveryOrder->id);
    }

    public function test_pagination_works_correctly_with_belongs_to_filter(): void
    {
        // Create 15 confirmation orders (more than default per_page of 10)
        for ($i = 0; $i < 15; $i++) {
            Order::factory()->create([
                'belongs_to' => 'confirmation',
                'order_status_id' => OrderStatus::where('name', 'New Order')->first()->id
            ]);
        }
        
        // Create 5 delivery orders
        for ($i = 0; $i < 5; $i++) {
            Order::factory()->create([
                'belongs_to' => 'delivery',
                'order_status_id' => OrderStatus::where('name', 'Processing')->first()->id
            ]);
        }

        // Test first page of confirmation orders
        $response = $this->actingAs($this->user)
            ->getJson('/orders/list?belongs_to=confirmation&page=1&per_page=10')
            ->assertStatus(200);

        $data = $response->json();
        
        // Should have 10 orders on first page
        $this->assertEquals(10, count($data['orders']['data']));
        
        // Should have 2 total pages (15 orders / 10 per page = 2 pages)
        $this->assertEquals(2, $data['orders']['last_page']);
        
        // Should be on page 1
        $this->assertEquals(1, $data['orders']['current_page']);
        
        // All orders should belong to confirmation
        foreach ($data['orders']['data'] as $order) {
            $this->assertEquals('confirmation', $order['belongs_to']);
        }
    }
}
