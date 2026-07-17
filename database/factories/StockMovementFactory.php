<?php

namespace Database\Factories;

use App\Enums\LocationType;
use App\Enums\StockMovementType;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'movement_type' => StockMovementType::INITIAL_ENTRY,
            'quantity' => fake()->numberBetween(1, 1000),
            'location_source_type' => null,
            'location_source_id' => null,
            'location_destination_type' => LocationType::WAREHOUSE,
            'location_destination_id' => Warehouse::factory(),
            'reason' => 'Stock initial',
            'user_id' => User::factory(),
            'movement_date' => now(),
            'document_type' => null,
            'document_id' => null,
            'note' => null,
        ];
    }
}
