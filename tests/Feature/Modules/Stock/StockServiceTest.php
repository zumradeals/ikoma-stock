<?php

use App\Enums\LocationType;
use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Exceptions\Business\InsufficientStockException;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockLevel;
use App\Modules\Stock\Services\StockService;

beforeEach(function () {
    $this->stock = app(StockService::class);
});

test('getAvailableQuantity returns physical minus reserved', function () {
    ['product' => $product, 'outlet' => $outlet] = seedTenant(500);

    expect($this->stock->getAvailableQuantity($product, LocationType::OUTLET, $outlet->id))->toBe(500);
});

test('getAvailableQuantity returns 0 when no stock level row exists', function () {
    ['product' => $product] = seedTenant(0);
    $otherOutlet = \App\Models\Outlet::factory()->create();

    expect($this->stock->getAvailableQuantity($product, LocationType::OUTLET, $otherOutlet->id))->toBe(0);
});

test('reserveForSale increases quantity_reserved for each line', function () {
    ['company' => $company, 'product' => $product, 'outlet' => $outlet] = seedTenant(100);

    $sale = Sale::create([
        'company_id' => $company->id, 'number' => 'VTE-T-1', 'outlet_id' => $outlet->id,
        'user_id' => \App\Models\User::factory()->for($company)->create()->id,
        'customer_type' => 'PASSING', 'total_amount' => 0, 'status' => SaleStatus::DRAFT,
    ]);
    SaleLine::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 30, 'unit_price' => 100, 'line_total' => 3000]);

    $this->stock->reserveForSale($sale->fresh(['saleLines']));

    $level = StockLevel::where('product_id', $product->id)->where('location_id', $outlet->id)->first();
    expect($level->quantity_reserved)->toBe(30)
        ->and($this->stock->getAvailableQuantity($product, LocationType::OUTLET, $outlet->id))->toBe(70);
});

test('reserveForSale throws InsufficientStockException when demand exceeds available stock', function () {
    ['company' => $company, 'product' => $product, 'outlet' => $outlet] = seedTenant(10);

    $sale = Sale::create([
        'company_id' => $company->id, 'number' => 'VTE-T-2', 'outlet_id' => $outlet->id,
        'user_id' => \App\Models\User::factory()->for($company)->create()->id,
        'customer_type' => 'PASSING', 'total_amount' => 0, 'status' => SaleStatus::DRAFT,
    ]);
    SaleLine::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 999, 'unit_price' => 100, 'line_total' => 99900]);

    expect(fn () => $this->stock->reserveForSale($sale->fresh(['saleLines'])))
        ->toThrow(InsufficientStockException::class);

    $level = StockLevel::where('product_id', $product->id)->where('location_id', $outlet->id)->first();
    expect($level->quantity_reserved)->toBe(0);
});

test('two sequential reservations on stock only sufficient for one: the second is rejected (no oversell)', function () {
    // Proxy de concurrence : lockForUpdate() dans StockService garantit que
    // deux réservations qui se disputent le même stock ne peuvent jamais
    // survendre, même exécutées en synchronisant leurs transactions l'une
    // après l'autre plutôt qu'en vrai parallélisme multi-processus (Pest ne
    // permet pas de tester du vrai multi-threading).
    ['company' => $company, 'product' => $product, 'outlet' => $outlet] = seedTenant(10);
    $userId = \App\Models\User::factory()->for($company)->create()->id;

    $makeSale = function (int $qty, string $number) use ($company, $outlet, $product, $userId) {
        $sale = Sale::create([
            'company_id' => $company->id, 'number' => $number, 'outlet_id' => $outlet->id,
            'user_id' => $userId, 'customer_type' => 'PASSING', 'total_amount' => 0, 'status' => SaleStatus::DRAFT,
        ]);
        SaleLine::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => $qty, 'unit_price' => 100, 'line_total' => $qty * 100]);

        return $sale->fresh(['saleLines']);
    };

    $saleA = $makeSale(10, 'VTE-C-1');
    $saleB = $makeSale(1, 'VTE-C-2');

    $this->stock->reserveForSale($saleA);

    expect(fn () => $this->stock->reserveForSale($saleB))->toThrow(InsufficientStockException::class);
});

test('confirmDelivery decreases physical and reserved stock and logs a SALE_DELIVERY movement', function () {
    ['company' => $company, 'product' => $product, 'outlet' => $outlet] = seedTenant(100);
    $user = \App\Models\User::factory()->for($company)->create();

    $sale = Sale::create([
        'company_id' => $company->id, 'number' => 'VTE-T-3', 'outlet_id' => $outlet->id,
        'user_id' => $user->id, 'customer_type' => 'PASSING', 'total_amount' => 0, 'status' => SaleStatus::VALIDATED,
    ]);
    $line = SaleLine::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 20, 'unit_price' => 100, 'line_total' => 2000]);
    $this->stock->reserveForSale($sale->fresh(['saleLines']));

    $invoice = \App\Models\Invoice::factory()->for($company)->for($sale)->create(['total_amount' => 2000, 'paid_amount' => 0, 'balance_due' => 2000]);
    $delivery = \App\Models\Delivery::factory()->for($company)->for($invoice)->for($user)->create();
    \App\Models\DeliveryLine::factory()->for($delivery)->create(['sale_line_id' => $line->id, 'product_id' => $product->id, 'quantity_delivered' => 20]);

    $this->stock->confirmDelivery($delivery->fresh('deliveryLines.product'));

    $level = StockLevel::where('product_id', $product->id)->where('location_id', $outlet->id)->first();
    expect($level->quantity_physical)->toBe(80)
        ->and($level->quantity_reserved)->toBe(0)
        ->and(\App\Models\StockMovement::where('movement_type', StockMovementType::SALE_DELIVERY->value)->count())->toBe(1);
});

test('processTransfer ship decreases source stock and receive increases destination stock', function () {
    ['company' => $company, 'product' => $product, 'warehouse' => $warehouse] = seedTenant(0);
    seedWarehouseStock($company, $product, $warehouse, 200);
    $destinationOutlet = \App\Models\Outlet::factory()->for($company)->create();
    $user = \App\Models\User::factory()->for($company)->create();

    $transfer = \App\Models\Transfer::create([
        'company_id' => $company->id, 'number' => 'TRF-T-1', 'source_warehouse_id' => $warehouse->id,
        'destination_outlet_id' => $destinationOutlet->id, 'user_id' => $user->id,
        'status' => 'ACCEPTED', 'total_quantity' => 50,
    ]);
    \App\Models\TransferLine::create(['transfer_id' => $transfer->id, 'product_id' => $product->id, 'requested_quantity' => 50]);

    $this->stock->processTransfer($transfer->fresh('transferLines'), 'ship', [$product->id => 50]);

    $sourceLevel = StockLevel::where('product_id', $product->id)->where('location_id', $warehouse->id)->first();
    expect($sourceLevel->quantity_physical)->toBe(150);

    $this->stock->processTransfer($transfer->fresh('transferLines'), 'receive', [$product->id => 50]);

    $destLevel = StockLevel::where('product_id', $product->id)->where('location_id', $destinationOutlet->id)->first();
    expect($destLevel->quantity_physical)->toBe(50);
    expect(\App\Models\StockMovement::whereIn('movement_type', [StockMovementType::TRANSFER_OUT->value, StockMovementType::TRANSFER_IN->value])->count())->toBe(2);
});

test('processTransfer ship throws InsufficientStockException when source stock is too low', function () {
    ['company' => $company, 'product' => $product, 'warehouse' => $warehouse] = seedTenant(0);
    seedWarehouseStock($company, $product, $warehouse, 5);
    $user = \App\Models\User::factory()->for($company)->create();

    $transfer = \App\Models\Transfer::create([
        'company_id' => $company->id, 'number' => 'TRF-T-2', 'source_warehouse_id' => $warehouse->id,
        'destination_outlet_id' => \App\Models\Outlet::factory()->for($company)->create()->id,
        'user_id' => $user->id, 'status' => 'ACCEPTED', 'total_quantity' => 999,
    ]);
    \App\Models\TransferLine::create(['transfer_id' => $transfer->id, 'product_id' => $product->id, 'requested_quantity' => 999]);

    expect(fn () => $this->stock->processTransfer($transfer->fresh('transferLines'), 'ship', [$product->id => 999]))
        ->toThrow(InsufficientStockException::class);
});

test('createInventoryCorrection adjusts physical stock up and down and logs a movement', function () {
    ['company' => $company, 'product' => $product, 'outlet' => $outlet, 'admin' => $admin] = seedTenant(50);
    $this->actingAs($admin);

    $this->stock->createInventoryCorrection($product, LocationType::OUTLET, $outlet->id, -10, 'Casse constatée');
    expect(StockLevel::where('product_id', $product->id)->first()->quantity_physical)->toBe(40);

    $this->stock->createInventoryCorrection($product, LocationType::OUTLET, $outlet->id, 5, 'Régularisation inventaire');
    expect(StockLevel::where('product_id', $product->id)->first()->quantity_physical)->toBe(45);

    expect(\App\Models\StockMovement::where('movement_type', StockMovementType::INVENTORY_CORRECTION->value)->count())->toBe(2);
});
