<?php

use App\Enums\TransferStatus;
use App\Exceptions\Business\InsufficientStockException;
use App\Models\Outlet;
use App\Models\StockLevel;
use App\Modules\Transfer\Services\TransferService;

beforeEach(function () {
    $this->transfers = app(TransferService::class);
});

test('createRequest creates a transfer in REQUESTED status with its lines', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();

    $transfer = $this->transfers->createRequest(
        ['warehouse_id' => $tenant['warehouse']->id],
        ['outlet_id' => $destination->id],
        [$tenant['product']->id => 30],
    );

    expect($transfer->status)->toBe(TransferStatus::REQUESTED)
        ->and($transfer->number)->toStartWith('TRF-')
        ->and($transfer->transferLines()->count())->toBe(1)
        ->and($transfer->total_quantity)->toBe(30);
});

test('accept transitions REQUESTED to ACCEPTED', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();
    $transfer = $this->transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 10]);

    $this->transfers->accept($transfer);

    expect($transfer->fresh()->status)->toBe(TransferStatus::ACCEPTED);
});

test('ship decreases source stock and moves the transfer to SHIPPED', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();
    $transfer = $this->transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 40]);
    $this->transfers->accept($transfer);

    $this->transfers->ship($transfer->fresh(), [$tenant['product']->id => 40]);

    expect($transfer->fresh()->status)->toBe(TransferStatus::SHIPPED);
    $sourceLevel = StockLevel::where('product_id', $tenant['product']->id)->where('location_id', $tenant['warehouse']->id)->first();
    expect($sourceLevel->quantity_physical)->toBe(60);
});

test('ship throws InsufficientStockException when the warehouse lacks stock', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 5);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();
    $transfer = $this->transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 999]);
    $this->transfers->accept($transfer);

    expect(fn () => $this->transfers->ship($transfer->fresh(), [$tenant['product']->id => 999]))
        ->toThrow(InsufficientStockException::class);
});

test('a full receive increases destination stock and moves the transfer to RECEIVED', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();
    $transfer = $this->transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 20]);
    $this->transfers->accept($transfer);
    $this->transfers->ship($transfer->fresh(), [$tenant['product']->id => 20]);

    $this->transfers->receive($transfer->fresh(), [$tenant['product']->id => 20]);

    expect($transfer->fresh()->status)->toBe(TransferStatus::RECEIVED);
    $destLevel = StockLevel::where('product_id', $tenant['product']->id)->where('location_id', $destination->id)->first();
    expect($destLevel->quantity_physical)->toBe(20);
});

test('successive partial receives end up RECEIVED only once everything shipped has arrived', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();
    $transfer = $this->transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 20]);
    $this->transfers->accept($transfer);
    $this->transfers->ship($transfer->fresh(), [$tenant['product']->id => 20]);

    $this->transfers->receive($transfer->fresh(), [$tenant['product']->id => 12]);
    expect($transfer->fresh()->status)->toBe(TransferStatus::PARTIALLY_RECEIVED);

    $this->transfers->receive($transfer->fresh(), [$tenant['product']->id => 8]);
    expect($transfer->fresh()->status)->toBe(TransferStatus::RECEIVED);

    $destLevel = StockLevel::where('product_id', $tenant['product']->id)->where('location_id', $destination->id)->first();
    expect($destLevel->quantity_physical)->toBe(20);
});

test('receive before shipping throws a LogicException', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();
    $transfer = $this->transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 10]);
    $this->transfers->accept($transfer);

    expect(fn () => $this->transfers->receive($transfer->fresh(), [$tenant['product']->id => 10]))
        ->toThrow(LogicException::class);
});

test('cancel is allowed before shipment', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = Outlet::factory()->for($tenant['company'])->create();
    $transfer = $this->transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 10]);

    $this->transfers->cancel($transfer, 'commande annulée par le client');

    expect($transfer->fresh()->status)->toBe(TransferStatus::CANCELLED);
});
