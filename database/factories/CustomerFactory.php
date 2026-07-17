<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Database\Factories\Concerns\GeneratesIvorianData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    use GeneratesIvorianData;

    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->name(),
            'phone' => $this->ivorianPhone(),
            'address' => fake()->streetAddress(),
            'neighborhood_city' => $this->abidjanNeighborhood(),
            'tax_id' => fake()->boolean(30) ? fake()->numerify('CI##########') : null,
            'credit_limit' => fake()->boolean(40) ? fake()->numberBetween(50_000, 1_000_000) * 100 : null,
            'notes' => null,
            'is_active' => true,
            'total_purchased' => 0,
            'outstanding_balance' => 0,
        ];
    }
}
