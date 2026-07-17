<?php

namespace App\Modules\Customer\Services;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\ReceivableStatus;
use App\Models\Customer;
use App\Models\Invoice;

/**
 * Le schéma n'a pas de colonne "type" sur Customer (REGISTERED/PASSING vit
 * sur Sale, pas ici) : une fiche Customer minimale (juste le téléphone) est
 * donc considérée "passagère" tant qu'elle n'a pas été complétée via
 * transformPassingToRegistered() — voir BUSINESS_RULES.md.
 */
class CustomerService
{
    public function createOrFindPassingCustomer(?string $phone): Customer
    {
        if (! $phone) {
            return Customer::create(['name' => 'Client passager']);
        }

        return Customer::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'Client passager'],
        );
    }

    public function transformPassingToRegistered(Customer $customer, array $data): Customer
    {
        $customer->update(array_intersect_key($data, array_flip([
            'name', 'phone', 'address', 'neighborhood_city', 'tax_id', 'credit_limit', 'notes',
        ])));

        return $customer->fresh();
    }

    public function checkOutstandingDues(Customer $customer): array
    {
        return [
            'receivables' => $customer->receivables()
                ->where('status', '!=', ReceivableStatus::PAID->value)
                ->get(),
            'undelivered_invoices' => Invoice::query()
                ->whereHas('sale', fn ($query) => $query->where('customer_id', $customer->id))
                ->whereNotIn('delivery_status', [
                    InvoiceDeliveryStatus::DELIVERED->value,
                    InvoiceDeliveryStatus::CANCELLED->value,
                ])
                ->get(),
        ];
    }
}
