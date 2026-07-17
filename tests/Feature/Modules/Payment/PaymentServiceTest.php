<?php

use App\Enums\InvoicePaymentStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\Business\PaymentExceedsBalanceException;
use App\Modules\Payment\Services\PaymentService;

beforeEach(function () {
    $this->payments = app(PaymentService::class);
});

test('record a partial payment sets payment_status to PARTIAL', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 10);
    $half = intdiv($invoice->total_amount, 2);

    $payment = $this->payments->record($invoice, $half, PaymentMethod::MOBILE_MONEY);

    expect($payment->amount)->toBe($half)
        ->and($invoice->fresh()->payment_status)->toBe(InvoicePaymentStatus::PARTIAL)
        ->and($invoice->fresh()->balance_due)->toBe($invoice->total_amount - $half);
});

test('record a full payment sets payment_status to PAID and syncs the receivable', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 10);

    $this->payments->record($invoice, $invoice->total_amount, PaymentMethod::CASH);

    expect($invoice->fresh()->payment_status)->toBe(InvoicePaymentStatus::PAID);
    $receivable = \App\Models\Receivable::where('invoice_id', $invoice->id)->first();
    expect($receivable->status)->toBe(\App\Enums\ReceivableStatus::PAID)
        ->and($receivable->balance_due)->toBe(0);
});

test('record with a zero or negative amount throws an InvalidArgumentException', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    expect(fn () => $this->payments->record($invoice, 0, PaymentMethod::CASH))
        ->toThrow(InvalidArgumentException::class);
});

test('record beyond balance_due throws PaymentExceedsBalanceException unless overpayment is allowed', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    expect(fn () => $this->payments->record($invoice, $invoice->total_amount + 1000, PaymentMethod::CASH))
        ->toThrow(PaymentExceedsBalanceException::class);

    $payment = $this->payments->record($invoice, $invoice->total_amount + 1000, PaymentMethod::CASH, allowOverpayment: true);
    expect($payment->amount)->toBe($invoice->total_amount + 1000);
});

test('refund creates a negative payment and restores the invoice balance', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    $this->payments->record($invoice, $invoice->total_amount, PaymentMethod::CASH);

    $refund = $this->payments->refund($invoice->fresh(), $invoice->total_amount, 'retour marchandise');

    expect($refund->amount)->toBe(-$invoice->total_amount)
        ->and($invoice->fresh()->paid_amount)->toBe(0)
        ->and($invoice->fresh()->balance_due)->toBe($invoice->total_amount);
});
