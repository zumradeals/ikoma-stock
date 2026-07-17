<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\Product;
use App\Models\SaleLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryLine>
 */
class DeliveryLineFactory extends Factory
{
    protected $model = DeliveryLine::class;

    public function definition(): array
    {
        return [
            'delivery_id' => Delivery::factory(),
            'sale_line_id' => SaleLine::factory(),
            'product_id' => Product::factory(),
            'quantity_delivered' => fake()->numberBetween(1, 10),
        ];
    }
}
