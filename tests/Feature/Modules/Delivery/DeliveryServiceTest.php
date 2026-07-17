<?php

use App\Enums\InvoiceDeliveryStatus;
use App\Exceptions\Business\DeliveryExceedsOrderedQuantityException;
use App\Models\StockLevel;
use App\Modules\Delivery\Services\DeliveryService;

beforeEach(function () {
    $this->deliveries = app(DeliveryService::class);
});

test('markReady transitions TO_PREPARE to READY', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    $this->deliveries->markReady($invoice);

    expect($invoice->fresh()->delivery_status)->toBe(InvoiceDeliveryStatus::READY);
});

test('a full delivery marks the invoice DELIVERED and decreases stock', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['warehouseKeeper']);
    $invoice = validatedInvoice($tenant, 10);
    $saleLine = $invoice->sale->saleLines->first();

    $delivery = $this->deliveries->deliver($invoice, [$saleLine->id => 10]);

    expect($invoice->fresh()->delivery_status)->toBe(InvoiceDeliveryStatus::DELIVERED)
        ->and($saleLine->fresh()->delivered_quantity)->toBe(10)
        ->and($delivery->deliveryLines()->count())->toBe(1);

    $level = StockLevel::where('product_id', $tenant['product']->id)->where('location_id', $tenant['outlet']->id)->first();
    expect($level->quantity_physical)->toBe(90);
});

test('a partial delivery marks the invoice PARTIAL_DELIVERED and can be completed later', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['warehouseKeeper']);
    $invoice = validatedInvoice($tenant, 10);
    $saleLine = $invoice->sale->saleLines->first();

    $this->deliveries->deliver($invoice, [$saleLine->id => 4]);
    expect($invoice->fresh()->delivery_status)->toBe(InvoiceDeliveryStatus::PARTIAL_DELIVERED)
        ->and($saleLine->fresh()->delivered_quantity)->toBe(4);

    $this->deliveries->deliver($invoice->fresh(), [$saleLine->fresh()->id => 6]);
    expect($invoice->fresh()->delivery_status)->toBe(InvoiceDeliveryStatus::DELIVERED)
        ->and($saleLine->fresh()->delivered_quantity)->toBe(10);
});

test('delivering a quantity of 0 is a no-op and does not error', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['warehouseKeeper']);
    $invoice = validatedInvoice($tenant, 10);
    $saleLine = $invoice->sale->saleLines->first();

    $delivery = $this->deliveries->deliver($invoice, [$saleLine->id => 0]);

    expect($delivery->deliveryLines()->count())->toBe(0)
        ->and($saleLine->fresh()->delivered_quantity)->toBe(0)
        ->and($invoice->fresh()->delivery_status)->toBe(InvoiceDeliveryStatus::TO_PREPARE);
});

test('delivering more than what remains throws DeliveryExceedsOrderedQuantityException', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['warehouseKeeper']);
    $invoice = validatedInvoice($tenant, 5);
    $saleLine = $invoice->sale->saleLines->first();

    expect(fn () => $this->deliveries->deliver($invoice, [$saleLine->id => 6]))
        ->toThrow(DeliveryExceedsOrderedQuantityException::class);

    expect($saleLine->fresh()->delivered_quantity)->toBe(0);
});
