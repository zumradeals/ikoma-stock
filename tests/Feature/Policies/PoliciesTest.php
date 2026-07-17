<?php

use App\Enums\DailyClosingStatus;
use App\Enums\UserRole;
use App\Models\DailyClosing;
use App\Models\Transfer;
use App\Models\User;

test('InvoicePolicy: ADMIN_COMPANY can cancel, SELLER cannot, and nobody can delete (not even SUPER_ADMIN)', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 3);
    $superAdmin = User::factory()->superAdmin()->create();

    expect($tenant['admin']->can('cancel', $invoice))->toBeTrue()
        ->and($tenant['seller']->can('cancel', $invoice))->toBeFalse()
        ->and($tenant['admin']->can('delete', $invoice))->toBeFalse()
        ->and($superAdmin->can('delete', $invoice))->toBeFalse();
});

test('TransferPolicy: WAREHOUSE_KEEPER and ADMIN_COMPANY can manage transfers, SELLER cannot', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 50);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = \App\Models\Outlet::factory()->for($tenant['company'])->create();
    $transfer = app(\App\Modules\Transfer\Services\TransferService::class)->createRequest(
        ['warehouse_id' => $tenant['warehouse']->id],
        ['outlet_id' => $destination->id],
        [$tenant['product']->id => 5],
    );

    expect($tenant['warehouseKeeper']->can('manage', $transfer))->toBeTrue()
        ->and($tenant['admin']->can('manage', $transfer))->toBeTrue()
        ->and($tenant['seller']->can('manage', $transfer))->toBeFalse();
});

test('DailyClosingPolicy: locked once VALIDATED, and the submitter cannot validate their own closing', function () {
    $tenant = seedTenant();
    $closing = DailyClosing::create([
        'company_id' => $tenant['company']->id,
        'outlet_id' => $tenant['outlet']->id,
        'user_id' => $tenant['seller']->id,
        'business_date' => now()->toDateString(),
        'status' => DailyClosingStatus::PENDING_VALIDATION,
    ]);

    expect($tenant['seller']->can('validate', $closing))->toBeFalse()
        ->and($tenant['admin']->can('validate', $closing))->toBeTrue()
        ->and($tenant['admin']->can('update', $closing))->toBeTrue();

    $closing->update(['status' => DailyClosingStatus::VALIDATED]);

    expect($tenant['admin']->can('update', $closing->fresh()))->toBeFalse();
});

test('ProductPolicy: updatePrice allowed for ADMIN_COMPANY, forbidden for SELLER', function () {
    $tenant = seedTenant();

    expect($tenant['admin']->can('updatePrice', $tenant['product']))->toBeTrue()
        ->and($tenant['seller']->can('updatePrice', $tenant['product']))->toBeFalse();
});
