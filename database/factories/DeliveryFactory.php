<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'invoice_id' => Invoice::factory(),
            'user_id' => User::factory(),
            'delivered_at' => now(),
            'note' => null,
        ];
    }
}
