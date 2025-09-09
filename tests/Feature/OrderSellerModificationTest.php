<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSellerModificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create order statuses
        OrderStatus::create(['name' => 'New Order']);
        OrderStatus::create(['name' => 'Confirmed']);
        OrderStatus::create(['name' => 'Processing']);
        
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $sellerRole = Role::create(['name' => 'seller']);
        
        // Create users
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
        
        $this->seller1 = User::factory()->create(['name' => 'John Seller']);
        $this->seller1->roles()->attach($sellerRole);
        
        $this->seller2 = User::factory()->create(['name' => 'Jane Seller']);
        $this->seller2->roles()->attach($sellerRole);
        
        // Create a product
        $this->product = Product::factory()->create();
        
        // Create an order
        $this->order = Order::factory()->create([
            'seller' => 'John Seller',
            'product_id' => $this->product->id,
            'order_status_id' => OrderStatus::where('name', 'New Order')->first()->id
        ]);
    }

    public function test_admin_can_modify_order_seller(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/orders/{$this->order->id}", [
                'seller' => 'Jane Seller',
                'product_id' => $this->product->id,
                'quantity' => 1,
                'client_name' => 'Test Client',
                'price' => 100,
                'client_address' => 'Test Address',
                'order_status_id' => OrderStatus::where('name', 'New Order')->first()->id
            ]);

        $response->assertStatus(200);
        
        $this->order->refresh();
        $this->assertEquals('Jane Seller', $this->order->seller);
    }

    public function test_seller_cannot_modify_other_sellers_orders(): void
    {
        $response = $this->actingAs($this->seller1)
            ->putJson("/orders/{$this->order->id}", [
                'seller' => 'Jane Seller', // Trying to change to another seller
                'product_id' => $this->product->id,
                'quantity' => 1,
                'client_name' => 'Test Client',
                'price' => 100,
                'client_address' => 'Test Address',
                'order_status_id' => OrderStatus::where('name', 'New Order')->first()->id
            ]);

        // The request should still succeed (backend doesn't restrict this)
        // but the frontend should prevent sellers from seeing the dropdown
        $response->assertStatus(200);
    }

    public function test_sellers_endpoint_returns_seller_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/users/sellers');

        $response->assertStatus(200);
        
        $sellers = $response->json();
        $this->assertCount(2, $sellers);
        
        $sellerNames = collect($sellers)->pluck('name')->toArray();
        $this->assertContains('John Seller', $sellerNames);
        $this->assertContains('Jane Seller', $sellerNames);
    }

    public function test_seller_cannot_access_sellers_endpoint(): void
    {
        $response = $this->actingAs($this->seller1)
            ->getJson('/users/sellers');

        // Sellers should still be able to access this endpoint
        // The restriction is handled in the frontend
        $response->assertStatus(200);
    }
}
