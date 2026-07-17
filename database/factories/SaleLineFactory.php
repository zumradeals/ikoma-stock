<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleLine>
 */
class SaleLineFactory extends Factory
{
    protected $model = SaleLine::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 20);
        $unitPrice = fake()->numberBetween(500, 10_000) * 100;

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_discount' => 0,
            'line_total' => $quantity * $unitPrice,
        ];
    }
}
