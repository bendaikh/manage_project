<?php

namespace Database\Factories;

use App\Models\SellerInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SellerInvoice>
 */
class SellerInvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SellerInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller' => $this->faker->name,
            'invoice_date' => $this->faker->date(),
            'order_count' => $this->faker->numberBetween(1, 20),
            'total_amount' => $this->faker->numberBetween(100, 5000),
            'pdf_path' => 'invoices/sellers/' . $this->faker->slug . '.pdf',
        ];
    }
}
