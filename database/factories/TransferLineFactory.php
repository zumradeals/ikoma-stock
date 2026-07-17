<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transfer;
use App\Models\TransferLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransferLine>
 */
class TransferLineFactory extends Factory
{
    protected $model = TransferLine::class;

    public function definition(): array
    {
        return [
            'transfer_id' => Transfer::factory(),
            'product_id' => Product::factory(),
            'requested_quantity' => fake()->numberBetween(1, 200),
            'shipped_quantity' => 0,
            'received_quantity' => 0,
        ];
    }
}
