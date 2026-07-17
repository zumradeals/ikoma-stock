<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Outlet;
use Database\Factories\Concerns\GeneratesIvorianData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    use GeneratesIvorianData;

    protected $model = Outlet::class;

    public function definition(): array
    {
        $neighborhood = $this->abidjanNeighborhood();

        return [
            'company_id' => Company::factory(),
            'name' => "Point de vente {$neighborhood}",
            'address' => "{$neighborhood}, Abidjan",
            'phone' => $this->ivorianPhone(),
            'manager_id' => null,
            'is_active' => true,
        ];
    }
}
