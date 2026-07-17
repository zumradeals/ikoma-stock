<?php

namespace Database\Factories;

use App\Models\Company;
use Database\Factories\Concerns\GeneratesIvorianData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    use GeneratesIvorianData;

    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'address' => $this->abidjanNeighborhood().', Abidjan',
            'phone' => $this->ivorianPhone(),
            'email' => fake()->unique()->companyEmail(),
            'currency' => 'XOF',
            'invoice_prefix' => strtoupper(substr(preg_replace('/[^A-Z]/i', '', $name), 0, 3)) ?: 'FAC',
            'logo_path' => null,
            'footer_text' => 'Merci de votre confiance.',
            'is_active' => true,
            'suspended_at' => null,
            'suspended_reason' => null,
        ];
    }
}
