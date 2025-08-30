<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
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
            'title' => fake()->words(3, true),
            'reference' => fake()->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'quantity' => fake()->numberBetween(10, 100),
            'description' => fake()->paragraph(),
            'link' => fake()->url(),
            'photo' => fake()->imageUrl(),
            'shipment_date' => fake()->date(),
            'customs_fees' => fake()->optional()->randomFloat(2, 0, 1000),
            'status' => fake()->randomElement(['Processing', 'Validated', 'Rejected']),
            'validated' => fake()->boolean(),
        ];
    }
}
