<?php

namespace Database\Factories;

use App\Enums\LocationType;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockLevel>
 */
class StockLevelFactory extends Factory
{
    protected $model = StockLevel::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'location_type' => LocationType::WAREHOUSE,
            'location_id' => Warehouse::factory(),
            'quantity_physical' => fake()->numberBetween(0, 50_000),
            'quantity_reserved' => 0,
        ];
    }
}
