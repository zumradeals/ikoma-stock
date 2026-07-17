<?php

namespace Database\Factories;

use App\Enums\DailyClosingStatus;
use App\Models\Company;
use App\Models\DailyClosing;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyClosing>
 */
class DailyClosingFactory extends Factory
{
    protected $model = DailyClosing::class;

    public function definition(): array
    {
        $cash = fake()->numberBetween(10_000, 200_000) * 100;
        $mobileMoney = fake()->numberBetween(5_000, 100_000) * 100;

        return [
            'company_id' => Company::factory(),
            'outlet_id' => Outlet::factory(),
            'user_id' => User::factory(),
            'business_date' => now(),
            'total_sales' => $cash + $mobileMoney,
            'cash_sales' => $cash,
            'mobile_money_sales' => $mobileMoney,
            'transfer_sales' => 0,
            'credit_sales' => 0,
            'collected_old_receivables' => 0,
            'total_discounts' => 0,
            'cancelled_invoices_count' => 0,
            'delivered_products_count' => fake()->numberBetween(0, 50),
            'declared_cash_amount' => $cash,
            'cash_difference' => 0,
            'observations' => null,
            'status' => DailyClosingStatus::OPEN,
            'validated_by_user_id' => null,
            'validated_at' => null,
        ];
    }
}
