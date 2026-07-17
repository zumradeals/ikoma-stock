<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->numberBetween(5_000, 500_000) * 100,
            'method' => fake()->randomElement([PaymentMethod::CASH, PaymentMethod::MOBILE_MONEY, PaymentMethod::BANK_TRANSFER]),
            'payment_date' => now(),
            'user_id' => User::factory(),
            'reference' => fake()->boolean(50) ? fake()->bothify('PMT-####??') : null,
            'proof_path' => null,
            'note' => null,
        ];
    }
}
