<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller' => fake()->name(),
            'product_id' => \App\Models\Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'client_name' => fake()->name(),
            'price' => fake()->randomFloat(2, 1000, 50000),
            'client_address' => fake()->address(),
            'zone' => fake()->city(),
            'client_phone' => fake()->phoneNumber(),
            'comment' => fake()->optional()->sentence(),
            'order_status_id' => \App\Models\OrderStatus::factory(),
            'belongs_to' => fake()->randomElement(['confirmation', 'delivery']),
        ];
    }
}
