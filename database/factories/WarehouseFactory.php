<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Warehouse;
use Database\Factories\Concerns\GeneratesIvorianData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    use GeneratesIvorianData;

    protected $model = Warehouse::class;

    public function definition(): array
    {
        $neighborhood = $this->abidjanNeighborhood();

        return [
            'company_id' => Company::factory(),
            'name' => "Dépôt {$neighborhood}",
            'address' => "Zone industrielle, {$neighborhood}, Abidjan",
            'manager_id' => null,
            'is_active' => true,
        ];
    }
}
