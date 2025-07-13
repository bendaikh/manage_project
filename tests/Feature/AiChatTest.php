<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AiChatTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_access_ai_chat_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'Hello, how are you?'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_ai_chat_endpoint()
    {
        $response = $this->postJson('/api/ai-chat', [
            'message' => 'Hello, how are you?'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function ai_chat_requires_message_parameter()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    /** @test */
    public function ai_chat_validates_message_length()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => str_repeat('a', 1001) // Exceeds 1000 character limit
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    /** @test */
    public function ai_chat_returns_error_when_openai_key_not_configured()
    {
        // Temporarily remove OpenAI API key
        config(['app.env' => 'testing']);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'How many orders do we have?'
            ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'OpenAI API key not configured']);
    }

    /** @test */
    public function ai_chat_can_handle_order_related_questions()
    {
        // Create some test data
        $product = Product::factory()->create(['name' => 'Test Product']);
        Order::factory()->count(5)->create([
            'product_id' => $product->id,
            'client_name' => 'John Smith'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'How many orders do we have?'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
        
        // The response should contain information about orders
        $this->assertStringContainsString('order', strtolower($response->json('response')));
    }

    /** @test */
    public function ai_chat_can_handle_financial_questions()
    {
        // Create some test financial data
        Income::factory()->count(3)->create(['amount' => 100]);
        Expense::factory()->count(2)->create(['amount' => 50]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'What is our total income?'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
    }

    /** @test */
    public function ai_chat_can_handle_product_questions()
    {
        // Create some test products
        Product::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'How many products do we have?'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
    }

    /** @test */
    public function ai_chat_handles_empty_database_gracefully()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'Show me all orders'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
        
        // Should still return a meaningful response even with no data
        $this->assertNotEmpty($response->json('response'));
    }

    /** @test */
    public function ai_chat_can_handle_complex_questions()
    {
        // Create comprehensive test data
        $product = Product::factory()->create(['name' => 'Premium Product', 'price' => 100]);
        Order::factory()->count(10)->create([
            'product_id' => $product->id,
            'client_name' => 'Jane Doe'
        ]);
        Income::factory()->count(5)->create(['amount' => 200]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'What is the business overview for today?'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
    }

    /** @test */
    public function ai_chat_handles_special_characters_in_messages()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => 'What about orders with special chars: @#$%^&*()?'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
    }

    /** @test */
    public function ai_chat_handles_multilingual_questions()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-chat', [
                'message' => '¿Cuántos pedidos tenemos hoy?' // Spanish
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['response']);
    }
} 