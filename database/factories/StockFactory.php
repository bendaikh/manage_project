<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => \App\Models\User::factory(),
            'shipment_id' => \App\Models\Shipment::factory(),
            'product_id' => \App\Models\Product::factory(),
            'title' => fake()->words(3, true),
            'reference' => fake()->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'barcode' => fake()->unique()->ean13(),
            'quantity' => fake()->numberBetween(0, 100),
            'initial_quantity' => fake()->numberBetween(50, 200),
            'delivered_quantity' => fake()->numberBetween(0, 50),
            'damaged_quantity' => fake()->numberBetween(0, 10),
            'in_progress_quantity' => fake()->numberBetween(0, 20),
            'remaining_quantity' => fake()->numberBetween(0, 100),
            'description' => fake()->paragraph(),
            'purchase_price' => fake()->randomFloat(2, 10, 100),
            'selling_price' => fake()->randomFloat(2, 20, 200),
            'status' => fake()->randomElement(['in_stock', 'low_stock', 'out_of_stock']),
            'warehouse_location' => fake()->city(),
            'warehouse_id' => null,
            'last_updated_by' => fake()->name(),
            'last_updated_at' => fake()->dateTime(),
            'product_link' => fake()->url(),
            'photo' => fake()->imageUrl(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
