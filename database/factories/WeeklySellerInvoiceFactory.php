<?php

namespace Database\Factories;

use App\Models\WeeklySellerInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeeklySellerInvoice>
 */
class WeeklySellerInvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WeeklySellerInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weekStart = $this->faker->dateTimeBetween('-1 month', 'now');
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');

        return [
            'seller' => $this->faker->name,
            'week_start_date' => $weekStart->format('Y-m-d'),
            'week_end_date' => $weekEnd->format('Y-m-d'),
            'order_count' => $this->faker->numberBetween(5, 50),
            'total_amount' => $this->faker->numberBetween(500, 10000),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'pdf_path' => 'invoices/sellers/weekly/' . $this->faker->slug . '.pdf',
            'approved_by' => null,
            'approved_at' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the invoice is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => 1, // Assuming user ID 1 exists
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the invoice is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the invoice is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => 1, // Assuming user ID 1 exists
            'approved_at' => now(),
            'notes' => 'Rejected for testing purposes',
        ]);
    }
}
