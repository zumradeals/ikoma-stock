<?php

use App\Enums\InvoiceDeliveryStatus;
use App\Models\Customer;
use App\Modules\Customer\Services\CustomerService;

beforeEach(function () {
    $this->customers = app(CustomerService::class);
    $this->actingAs(seedTenant()['seller']);
});

test('createOrFindPassingCustomer creates a minimal customer record from a phone number', function () {
    $customer = $this->customers->createOrFindPassingCustomer('+225 07 00 00 00 01');

    expect($customer->phone)->toBe('+225 07 00 00 00 01')
        ->and($customer->name)->toBe('Client passager');
});

test('createOrFindPassingCustomer returns the same customer for the same phone number', function () {
    $first = $this->customers->createOrFindPassingCustomer('+225 07 00 00 00 02');
    $second = $this->customers->createOrFindPassingCustomer('+225 07 00 00 00 02');

    expect($second->id)->toBe($first->id)
        ->and(Customer::where('phone', '+225 07 00 00 00 02')->count())->toBe(1);
});

test('createOrFindPassingCustomer with no phone always creates a distinct walk-in record', function () {
    $first = $this->customers->createOrFindPassingCustomer(null);
    $second = $this->customers->createOrFindPassingCustomer(null);

    expect($first->id)->not->toBe($second->id);
});

test('transformPassingToRegistered fills in the customer profile', function () {
    $customer = $this->customers->createOrFindPassingCustomer('+225 07 00 00 00 03');

    $updated = $this->customers->transformPassingToRegistered($customer, [
        'name' => 'Kouassi Adjoua',
        'address' => 'Rue 12, Cocody',
        'neighborhood_city' => 'Cocody',
    ]);

    expect($updated->name)->toBe('Kouassi Adjoua')
        ->and($updated->address)->toBe('Rue 12, Cocody');
});

test('checkOutstandingDues returns open receivables and undelivered invoices for the customer', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    $dues = $this->customers->checkOutstandingDues($tenant['customer']->fresh());

    expect($dues['receivables'])->toHaveCount(1)
        ->and($dues['undelivered_invoices'])->toHaveCount(1)
        ->and($dues['undelivered_invoices']->first()->id)->toBe($invoice->id);

    app(\App\Modules\Delivery\Services\DeliveryService::class)->markReady($invoice);
    app(\App\Modules\Delivery\Services\DeliveryService::class)->deliver($invoice->fresh(), [$invoice->sale->saleLines->first()->id => 5]);

    $duesAfterDelivery = $this->customers->checkOutstandingDues($tenant['customer']->fresh());
    expect($duesAfterDelivery['undelivered_invoices'])->toHaveCount(0);
});
