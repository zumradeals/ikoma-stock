<?php

namespace Database\Factories;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(5_000, 500_000) * 100;

        return [
            'company_id' => Company::factory(),
            'sale_id' => Sale::factory(),
            'number' => 'FAC-'.now()->format('Ym').'-'.fake()->unique()->numerify('####'),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => $total,
            'paid_amount' => $total,
            'balance_due' => 0,
            'payment_status' => InvoicePaymentStatus::PAID,
            'delivery_status' => InvoiceDeliveryStatus::DELIVERED,
            'pdf_path' => null,
        ];
    }
}
