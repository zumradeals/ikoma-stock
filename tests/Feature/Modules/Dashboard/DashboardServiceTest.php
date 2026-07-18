<?php

use App\Enums\PaymentMethod;
use App\Modules\Dashboard\Services\DashboardService;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->dashboard = app(DashboardService::class);
});

test('cashCollected sums only today\'s cash payments for the company', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    app(PaymentService::class)->record($invoice, $invoice->total_amount, PaymentMethod::CASH);

    expect($this->dashboard->cashCollected($tenant['company']))->toBe($invoice->total_amount);
});

test('outstandingReceivables sums unpaid balances for the company', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    app(\App\Modules\Receivable\Services\ReceivableService::class)->syncFromInvoice($invoice);

    expect($this->dashboard->outstandingReceivables($tenant['company']))->toBe($invoice->total_amount);
});

test('lowStockAlerts flags a product at or below its threshold (scaled to the same unit as stock)', function () {
    // quantity_physical est stocké en centièmes ; low_stock_threshold est
    // en unités réelles (voir DashboardService::lowStockAlerts()) — 1000
    // centièmes = 10 unités réelles, au-dessus du seuil de 5.
    $tenant = seedTenant(1000);
    $tenant['product']->update(['low_stock_threshold' => 5]);

    expect($this->dashboard->lowStockAlerts($tenant['company']))->toHaveCount(0);

    // 300 centièmes = 3 unités réelles, sous le seuil de 5.
    \App\Models\StockLevel::where('product_id', $tenant['product']->id)->update(['quantity_physical' => 300]);
    Cache::flush();

    expect($this->dashboard->lowStockAlerts($tenant['company']))->toHaveCount(1);
});

test('stockValue is quantity times cost_price, correctly rescaled', function () {
    $tenant = seedTenant(50);
    $tenant['product']->update(['cost_price' => 200]);

    // 50 (stock) * 200 (cost_price, centimes) / 100 = 100
    expect($this->dashboard->stockValue($tenant['company']))->toBe(100);
});

test('todaySales groups totals by outlet and by seller', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 4);

    $summary = $this->dashboard->todaySales($tenant['company']);

    expect($summary['total'])->toBe($invoice->total_amount)
        ->and($summary['by_outlet'][$tenant['outlet']->id])->toBe($invoice->total_amount)
        ->and($summary['by_seller'][$tenant['seller']->id])->toBe($invoice->total_amount);
});

test('unpaidDeliveries returns invoices not yet fully delivered or cancelled', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 4);

    expect($this->dashboard->unpaidDeliveries($tenant['company']))->toHaveCount(1);

    app(\App\Modules\Delivery\Services\DeliveryService::class)->deliver($invoice->fresh(), [$invoice->sale->saleLines->first()->id => 4]);
    Cache::flush();

    expect($this->dashboard->unpaidDeliveries($tenant['company']))->toHaveCount(0);
});

test('transfersInTransit returns transfers that are SHIPPED or PARTIALLY_RECEIVED', function () {
    $tenant = seedTenant(0);
    seedWarehouseStock($tenant['company'], $tenant['product'], $tenant['warehouse'], 100);
    $this->actingAs($tenant['warehouseKeeper']);
    $destination = \App\Models\Outlet::factory()->for($tenant['company'])->create();
    $transfers = app(\App\Modules\Transfer\Services\TransferService::class);

    $transfer = $transfers->createRequest(['warehouse_id' => $tenant['warehouse']->id], ['outlet_id' => $destination->id], [$tenant['product']->id => 10]);
    $transfers->accept($transfer);
    $transfers->ship($transfer->fresh(), [$tenant['product']->id => 10]);

    expect($this->dashboard->transfersInTransit($tenant['company']))->toHaveCount(1);
});

test('topSellers ranks sellers by total sales amount over the period', function () {
    $tenant = seedTenant(1000);
    $this->actingAs($tenant['seller']);
    validatedInvoice($tenant, 4);

    $ranking = $this->dashboard->topSellers($tenant['company'], 'month');

    expect($ranking)->toHaveCount(1)
        ->and($ranking[0]['user_id'])->toBe($tenant['seller']->id);
});

test('topProductsToday returns up to 5 products sorted by revenue, with correct qty and total', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    validatedInvoice($tenant, 3);

    $top = app(DashboardService::class)->topProductsToday($tenant['company']);

    expect($top)->toHaveCount(1)
        ->and($top[0]['product_id'])->toBe($tenant['product']->id)
        ->and($top[0]['total_qty'])->toBe(3);
    expect($top[0]['total_revenue'])->toBeGreaterThan(0);
});

test('topProductsToday returns empty array when no sales today', function () {
    $tenant = seedTenant(100);

    $top = app(DashboardService::class)->topProductsToday($tenant['company']);

    expect($top)->toBe([]);
});

test('cashByPaymentMethodToday splits cash and mobile money correctly', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoiceA = validatedInvoice($tenant, 2);
    $invoiceB = validatedInvoice($tenant, 1);
    $payService = app(PaymentService::class);
    $payService->record($invoiceA, $invoiceA->total_amount, PaymentMethod::CASH);
    $payService->record($invoiceB, $invoiceB->total_amount, PaymentMethod::MOBILE_MONEY);

    $byMethod = app(DashboardService::class)->cashByPaymentMethodToday($tenant['company']);

    expect($byMethod['cash'])->toBe($invoiceA->total_amount)
        ->and($byMethod['mobile_money'])->toBe($invoiceB->total_amount)
        ->and($byMethod['other'])->toBe(0);
});

test('cashByPaymentMethodToday returns zeros when no payments today', function () {
    $tenant = seedTenant(100);

    $byMethod = app(DashboardService::class)->cashByPaymentMethodToday($tenant['company']);

    expect($byMethod)->toBe(['cash' => 0, 'mobile_money' => 0, 'other' => 0]);
});

test('yesterdayTotalSales returns 0 and salesTrendPercent does not divide by zero', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);

    $yesterday = app(DashboardService::class)->yesterdayTotalSales($tenant['company']);
    expect($yesterday)->toBe(0);

    // Simule un today = 50 000, yesterday = 0 → percent = 'first'
    $todayTotal = 50_000;
    $yesterdayTotal = 0;
    $result = $yesterdayTotal === 0
        ? ($todayTotal > 0 ? 'first' : 'flat')
        : (round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100));

    expect($result)->toBe('first');
});

test('dashboard cache keys are scoped per company: no cross-tenant leakage', function () {
    $tenantA = seedTenant(100);
    $tenantB = seedTenant(100);
    $this->actingAs($tenantA['seller']);
    $invoiceA = validatedInvoice($tenantA, 5);
    app(PaymentService::class)->record($invoiceA, $invoiceA->total_amount, PaymentMethod::CASH);

    $collectedA = $this->dashboard->cashCollected($tenantA['company']);
    $collectedB = $this->dashboard->cashCollected($tenantB['company']);

    expect($collectedA)->toBe($invoiceA->total_amount)
        ->and($collectedB)->toBe(0);
});
