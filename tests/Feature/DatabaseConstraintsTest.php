<?php

use App\Enums\CustomerType;
use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Les contraintes métier critiques doivent être imposées par la base de
 * données elle-même, pas seulement par la couche applicative — voir la
 * section "CONTRAINTES MÉTIER EN DB" du brief.
 */
function makeSale(): Sale
{
    $company = Company::factory()->create();
    $outlet = Outlet::factory()->for($company)->create();
    $user = User::factory()->for($company)->create();

    return Sale::create([
        'company_id' => $company->id,
        'number' => 'VTE-TEST-'.uniqid(),
        'outlet_id' => $outlet->id,
        'user_id' => $user->id,
        'customer_id' => null,
        'customer_type' => CustomerType::PASSING,
        'total_amount' => 10_000,
        'status' => SaleStatus::VALIDATED,
    ]);
}

test('invoices.balance_due cannot be negative', function () {
    $sale = makeSale();

    expect(fn () => Invoice::create([
        'company_id' => $sale->company_id,
        'sale_id' => $sale->id,
        'number' => 'FAC-TEST-'.uniqid(),
        'issue_date' => now(),
        'total_amount' => 10_000,
        'paid_amount' => 5_000,
        'balance_due' => -1,
        'payment_status' => InvoicePaymentStatus::PARTIAL,
        'delivery_status' => InvoiceDeliveryStatus::TO_PREPARE,
    ]))->toThrow(QueryException::class);
});

test('invoices.paid_amount cannot exceed total_amount', function () {
    $sale = makeSale();

    expect(fn () => Invoice::create([
        'company_id' => $sale->company_id,
        'sale_id' => $sale->id,
        'number' => 'FAC-TEST-'.uniqid(),
        'issue_date' => now(),
        'total_amount' => 10_000,
        'paid_amount' => 15_000,
        'balance_due' => 0,
        'payment_status' => InvoicePaymentStatus::PAID,
        'delivery_status' => InvoiceDeliveryStatus::DELIVERED,
    ]))->toThrow(QueryException::class);
});

test('receivables.balance_due cannot be negative', function () {
    $sale = makeSale();
    $invoice = Invoice::create([
        'company_id' => $sale->company_id,
        'sale_id' => $sale->id,
        'number' => 'FAC-TEST-'.uniqid(),
        'issue_date' => now(),
        'total_amount' => 10_000,
        'paid_amount' => 0,
        'balance_due' => 10_000,
        'payment_status' => InvoicePaymentStatus::UNPAID,
        'delivery_status' => InvoiceDeliveryStatus::TO_PREPARE,
    ]);
    $customer = \App\Models\Customer::factory()->for($sale->company)->create();

    expect(fn () => DB::table('receivables')->insert([
        'company_id' => $sale->company_id,
        'invoice_id' => $invoice->id,
        'customer_id' => $customer->id,
        'initial_amount' => 10_000,
        'total_paid' => 0,
        'balance_due' => -500,
        'status' => 'OPEN',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('sale_lines.quantity must be strictly positive', function () {
    $sale = makeSale();
    $product = Product::factory()->for($sale->company)->create();

    expect(fn () => DB::table('sale_lines')->insert([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 0,
        'unit_price' => 1_000,
        'line_discount' => 0,
        'line_total' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('transfer_lines.requested_quantity must be strictly positive', function () {
    $company = Company::factory()->create();
    $warehouse = \App\Models\Warehouse::factory()->for($company)->create();
    $user = User::factory()->for($company)->create();
    $product = Product::factory()->for($company)->create();

    $transfer = \App\Models\Transfer::create([
        'company_id' => $company->id,
        'number' => 'TRF-TEST-'.uniqid(),
        'source_warehouse_id' => $warehouse->id,
        'user_id' => $user->id,
        'status' => 'DRAFT',
    ]);

    expect(fn () => DB::table('transfer_lines')->insert([
        'transfer_id' => $transfer->id,
        'product_id' => $product->id,
        'requested_quantity' => -5,
        'shipped_quantity' => 0,
        'received_quantity' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('stock_movements.quantity must be strictly positive', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->for($company)->create();
    $user = User::factory()->for($company)->create();

    expect(fn () => DB::table('stock_movements')->insert([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'movement_type' => 'INITIAL_ENTRY',
        'quantity' => 0,
        'user_id' => $user->id,
        'movement_date' => now(),
        'created_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('sales.number is unique per company but reusable across companies', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $outletA = Outlet::factory()->for($companyA)->create();
    $outletB = Outlet::factory()->for($companyB)->create();
    $userA = User::factory()->for($companyA)->create();
    $userB = User::factory()->for($companyB)->create();

    Sale::create([
        'company_id' => $companyA->id,
        'number' => 'VTE-202607-0001',
        'outlet_id' => $outletA->id,
        'user_id' => $userA->id,
        'customer_type' => CustomerType::PASSING,
        'total_amount' => 1000,
        'status' => SaleStatus::VALIDATED,
    ]);

    // Même numéro, société différente : autorisé.
    Sale::create([
        'company_id' => $companyB->id,
        'number' => 'VTE-202607-0001',
        'outlet_id' => $outletB->id,
        'user_id' => $userB->id,
        'customer_type' => CustomerType::PASSING,
        'total_amount' => 1000,
        'status' => SaleStatus::VALIDATED,
    ]);

    // Même numéro, même société : rejeté par la contrainte unique.
    expect(fn () => Sale::create([
        'company_id' => $companyA->id,
        'number' => 'VTE-202607-0001',
        'outlet_id' => $outletA->id,
        'user_id' => $userA->id,
        'customer_type' => CustomerType::PASSING,
        'total_amount' => 1000,
        'status' => SaleStatus::VALIDATED,
    ]))->toThrow(QueryException::class);

    expect(Sale::count())->toBe(2);
});
