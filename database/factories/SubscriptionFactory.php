<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'plan_name' => 'STANDARD',
            'started_at' => now(),
            'expires_at' => now()->addYear(),
            'max_users' => 10,
            'max_products' => 500,
            'max_outlets' => 5,
            'max_invoices_per_month' => 1000,
            'is_active' => true,
        ];
    }
}
