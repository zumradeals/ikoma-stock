<?php

use App\Enums\InvoicePaymentStatus;
use App\Enums\SaleStatus;
use App\Exceptions\Business\InsufficientStockException;
use App\Exceptions\Business\SaleValidationForbiddenException;
use App\Exceptions\Business\UnauthorizedPriceModificationException;
use App\Models\Receivable;
use App\Modules\Sale\Services\SaleService;

beforeEach(function () {
    $this->sales = app(SaleService::class);
});

test('createDraft creates a DRAFT sale with a generated number', function () {
    ['company' => $company, 'seller' => $seller, 'outlet' => $outlet] = seedTenant();

    $sale = $this->sales->createDraft([
        'company_id' => $company->id,
        'outlet_id' => $outlet->id,
        'user_id' => $seller->id,
        'customer_type' => 'PASSING',
    ]);

    expect($sale->status)->toBe(SaleStatus::DRAFT)
        ->and($sale->number)->toStartWith('VTE-');
});

test('addLine adds a line and increments the sale total', function () {
    ['company' => $company, 'seller' => $seller, 'outlet' => $outlet, 'product' => $product] = seedTenant();
    $sale = $this->sales->createDraft(['company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $seller->id, 'customer_type' => 'PASSING']);

    $line = $this->sales->addLine($sale, $product, 3);

    expect($line->line_total)->toBe($product->sale_price * 3)
        ->and($sale->fresh()->total_amount)->toBe($product->sale_price * 3);
});

test('addLine is forbidden once the sale is no longer DRAFT', function () {
    ['company' => $company, 'seller' => $seller, 'outlet' => $outlet, 'product' => $product] = seedTenant();
    $this->actingAs($seller);
    $sale = $this->sales->createDraft(['company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $seller->id, 'customer_type' => 'PASSING']);
    $this->sales->addLine($sale, $product, 1);
    $this->sales->validate($sale->fresh());

    expect(fn () => $this->sales->addLine($sale->fresh(), $product, 1))
        ->toThrow(SaleValidationForbiddenException::class);
});

test('applyDiscount succeeds for an ADMIN_COMPANY user', function () {
    ['company' => $company, 'admin' => $admin, 'outlet' => $outlet, 'product' => $product] = seedTenant();
    $this->actingAs($admin);
    $sale = $this->sales->createDraft(['company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $admin->id, 'customer_type' => 'PASSING']);
    $this->sales->addLine($sale, $product, 10);

    $updated = $this->sales->applyDiscount($sale->fresh(), percentage: 10);

    expect($updated->discount_amount)->toBe((int) round($product->sale_price * 10 * 0.10));
});

test('applyDiscount is forbidden for a SELLER', function () {
    ['company' => $company, 'seller' => $seller, 'outlet' => $outlet, 'product' => $product] = seedTenant();
    $this->actingAs($seller);
    $sale = $this->sales->createDraft(['company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $seller->id, 'customer_type' => 'PASSING']);
    $this->sales->addLine($sale, $product, 10);

    expect(fn () => $this->sales->applyDiscount($sale->fresh(), amount: 500))
        ->toThrow(UnauthorizedPriceModificationException::class);
});

test('validate reserves stock, generates an invoice, and creates a receivable for a credit sale', function () {
    ['company' => $company, 'seller' => $seller, 'outlet' => $outlet, 'product' => $product, 'customer' => $customer] = seedTenant(100);
    $this->actingAs($seller);

    $sale = $this->sales->createDraft([
        'company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $seller->id,
        'customer_id' => $customer->id, 'customer_type' => 'REGISTERED',
    ]);
    $this->sales->addLine($sale, $product, 5);

    $invoice = $this->sales->validate($sale->fresh());

    expect($sale->fresh()->status)->toBe(SaleStatus::VALIDATED)
        ->and($invoice->payment_status)->toBe(InvoicePaymentStatus::UNPAID)
        ->and($invoice->balance_due)->toBe($product->sale_price * 5)
        ->and(Receivable::where('invoice_id', $invoice->id)->exists())->toBeTrue();

    $level = \App\Models\StockLevel::where('product_id', $product->id)->where('location_id', $outlet->id)->first();
    expect($level->quantity_reserved)->toBe(5);
});

test('validate throws InsufficientStockException and leaves the sale as DRAFT (no invoice created)', function () {
    ['company' => $company, 'seller' => $seller, 'outlet' => $outlet, 'product' => $product] = seedTenant(2);
    $this->actingAs($seller);

    $sale = $this->sales->createDraft(['company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $seller->id, 'customer_type' => 'PASSING']);
    $this->sales->addLine($sale, $product, 50);

    expect(fn () => $this->sales->validate($sale->fresh()))->toThrow(InsufficientStockException::class);

    expect($sale->fresh()->status)->toBe(SaleStatus::DRAFT)
        ->and(\App\Models\Invoice::where('sale_id', $sale->id)->exists())->toBeFalse();
});

test('cancel deletes a DRAFT sale outright (no reservation to release)', function () {
    ['company' => $company, 'seller' => $seller, 'outlet' => $outlet, 'product' => $product] = seedTenant();
    $this->actingAs($seller);
    $sale = $this->sales->createDraft(['company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $seller->id, 'customer_type' => 'PASSING']);
    $this->sales->addLine($sale, $product, 2);

    $this->sales->cancel($sale, 'changement d\'avis');

    expect(\App\Models\Sale::find($sale->id))->toBeNull();
});

test('cancel on a VALIDATED sale releases stock and cancels the invoice, but requires permission', function () {
    ['company' => $company, 'admin' => $admin, 'seller' => $seller, 'outlet' => $outlet, 'product' => $product] = seedTenant(100);
    $this->actingAs($seller);
    $sale = $this->sales->createDraft(['company_id' => $company->id, 'outlet_id' => $outlet->id, 'user_id' => $seller->id, 'customer_type' => 'PASSING']);
    $this->sales->addLine($sale, $product, 10);
    $invoice = $this->sales->validate($sale->fresh());

    // SELLER n'a pas la permission d'annuler une vente validée.
    expect(fn () => $this->sales->cancel($sale->fresh(), 'erreur de saisie'))
        ->toThrow(SaleValidationForbiddenException::class);

    $this->actingAs($admin);
    $this->sales->cancel($sale->fresh(), 'erreur de saisie');

    expect($sale->fresh()->status)->toBe(SaleStatus::CANCELLED)
        ->and($invoice->fresh()->payment_status)->toBe(InvoicePaymentStatus::CANCELLED);

    $level = \App\Models\StockLevel::where('product_id', $product->id)->where('location_id', $outlet->id)->first();
    expect($level->quantity_reserved)->toBe(0);
});
