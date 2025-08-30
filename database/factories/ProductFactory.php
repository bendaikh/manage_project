<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'category' => fake()->randomElement(['Electronics', 'Clothing', 'Books', 'Home', 'Sports']),
            'supplier' => fake()->company(),
            'purchase_price' => fake()->randomFloat(2, 10, 100),
            'selling_price' => fake()->randomFloat(2, 20, 200),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(['active', 'inactive', 'discontinued']),
            'image_url' => fake()->imageUrl(),
            'video_url' => fake()->url(),
            'video_duration' => fake()->time(),
            'description' => fake()->paragraph(),
        ];
    }
}
