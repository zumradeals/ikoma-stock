<?php

namespace Database\Factories;

use App\Enums\ReceivableStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receivable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receivable>
 */
class ReceivableFactory extends Factory
{
    protected $model = Receivable::class;

    public function definition(): array
    {
        $initial = fake()->numberBetween(5_000, 300_000) * 100;
        $paid = fake()->numberBetween(0, $initial);

        return [
            'company_id' => Company::factory(),
            'invoice_id' => Invoice::factory(),
            'customer_id' => Customer::factory(),
            'initial_amount' => $initial,
            'total_paid' => $paid,
            'balance_due' => $initial - $paid,
            'due_date' => now()->addDays(30),
            'days_overdue' => 0,
            'last_reminder_at' => null,
            'next_reminder_at' => null,
            'responsible_user_id' => null,
            'status' => $paid >= $initial ? ReceivableStatus::PAID : ReceivableStatus::OPEN,
        ];
    }
}
