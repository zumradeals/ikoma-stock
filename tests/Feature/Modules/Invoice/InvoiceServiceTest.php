<?php

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Modules\Invoice\Services\InvoiceService;
use App\Modules\Sale\Services\SaleService;

beforeEach(function () {
    $this->invoices = app(InvoiceService::class);
    $this->sales = app(SaleService::class);
});

test('generateNumber produces a PREFIX-YYYYMM-NNNN number scoped to the company', function () {
    $tenant = seedTenant();

    $number = $this->invoices->generateNumber($tenant['company']);

    expect($number)->toStartWith($tenant['company']->invoice_prefix.'-'.now()->format('Ym').'-');
});

test('recordPayment updates paid_amount and payment_status', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    $payment = $this->invoices->recordPayment($invoice, $invoice->total_amount, PaymentMethod::CASH);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($invoice->fresh()->payment_status)->toBe(InvoicePaymentStatus::PAID)
        ->and($invoice->fresh()->balance_due)->toBe(0);
});

test('markOverdue flips unpaid invoices past their due date to OVERDUE', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 2);
    $invoice->update(['due_date' => now()->subDays(5)]);

    $this->invoices->markOverdue();

    expect($invoice->fresh()->payment_status)->toBe(InvoicePaymentStatus::OVERDUE);
});

test('markOverdue never touches a PAID or CANCELLED invoice', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 2);
    $invoice->update(['due_date' => now()->subDays(5)]);
    $this->invoices->recordPayment($invoice, $invoice->total_amount, PaymentMethod::CASH);

    $this->invoices->markOverdue();

    expect($invoice->fresh()->payment_status)->toBe(InvoicePaymentStatus::PAID);
});

test('cancel on a paid invoice issues a refund payment (avoir) and cancels delivery status', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    $this->invoices->recordPayment($invoice, $invoice->total_amount, PaymentMethod::CASH);

    $this->invoices->cancel($invoice->fresh(), 'produit défectueux');

    expect($invoice->fresh()->payment_status)->toBe(InvoicePaymentStatus::CANCELLED)
        ->and($invoice->fresh()->delivery_status)->toBe(InvoiceDeliveryStatus::CANCELLED)
        ->and($invoice->fresh()->paid_amount)->toBe(0)
        ->and(Payment::where('invoice_id', $invoice->id)->where('amount', '<', 0)->exists())->toBeTrue();
});

test('cancel on an unpaid, undelivered invoice releases the stock reservation', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    $this->invoices->cancel($invoice->fresh(), 'annulation client');

    $level = \App\Models\StockLevel::where('product_id', $tenant['product']->id)->where('location_id', $tenant['outlet']->id)->first();
    expect($level->quantity_reserved)->toBe(0);
});

test('generatePdf writes a PDF file and stores its path on the invoice', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 2);

    $path = $this->invoices->generatePdf($invoice);

    expect(\Illuminate\Support\Facades\Storage::exists($path))->toBeTrue()
        ->and($invoice->fresh()->pdf_path)->toBe($path);

    \Illuminate\Support\Facades\Storage::delete($path);
});
