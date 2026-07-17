<?php

namespace Database\Factories;

use App\Enums\CustomerType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(5_000, 500_000) * 100;

        return [
            'company_id' => Company::factory(),
            'number' => 'VTE-'.now()->format('Ym').'-'.fake()->unique()->numerify('####'),
            'outlet_id' => Outlet::factory(),
            'user_id' => User::factory(),
            'customer_id' => null,
            'customer_type' => CustomerType::PASSING,
            'total_amount' => $total,
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'payment_method_primary' => PaymentMethod::CASH,
            'status' => SaleStatus::VALIDATED,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancellation_reason' => null,
        ];
    }
}
