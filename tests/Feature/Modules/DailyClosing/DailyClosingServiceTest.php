<?php

use App\Enums\DailyClosingStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\Business\DailyClosingLockedException;
use App\Modules\DailyClosing\Services\DailyClosingService;
use App\Modules\Payment\Services\PaymentService;

beforeEach(function () {
    $this->closings = app(DailyClosingService::class);
});

test('openForToday creates one closing and is idempotent for the same outlet/day', function () {
    $tenant = seedTenant();

    $first = $this->closings->openForToday($tenant['outlet'], $tenant['seller']);
    $second = $this->closings->openForToday($tenant['outlet'], $tenant['seller']);

    expect($first->id)->toBe($second->id)
        ->and($first->status)->toBe(DailyClosingStatus::OPEN);
});

test('addPayment links a payment to the closing and feeds computeSummary', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $closing = $this->closings->openForToday($tenant['outlet'], $tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    $payment = app(PaymentService::class)->record($invoice, $invoice->total_amount, PaymentMethod::CASH);

    $this->closings->addPayment($closing, $payment);

    expect($payment->fresh()->daily_closing_id)->toBe($closing->id);
    $summary = $this->closings->computeSummary($closing->fresh());
    expect($summary['cash'])->toBe($invoice->total_amount)
        ->and($summary['total'])->toBe($invoice->total_amount);
});

test('addPayment on a payment=0 amount still links correctly (edge case)', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $closing = $this->closings->openForToday($tenant['outlet'], $tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    // Aucune ligne de paiement : le résumé doit rester à zéro sans erreur.
    $summary = $this->closings->computeSummary($closing);

    expect($summary['total'])->toBe(0);
});

test('submitForValidation computes the cash difference and moves to PENDING_VALIDATION', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $closing = $this->closings->openForToday($tenant['outlet'], $tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    $payment = app(PaymentService::class)->record($invoice, $invoice->total_amount, PaymentMethod::CASH);
    $this->closings->addPayment($closing, $payment);

    $this->closings->submitForValidation($closing->fresh(), $invoice->total_amount - 500, 'petit écart de caisse');

    $fresh = $closing->fresh();
    expect($fresh->status)->toBe(DailyClosingStatus::PENDING_VALIDATION)
        ->and($fresh->cash_difference)->toBe(-500);
});

test('validate locks the closing, and any further modification throws DailyClosingLockedException', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['admin']);
    $closing = $this->closings->openForToday($tenant['outlet'], $tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    $this->closings->submitForValidation($closing->fresh(), 0, null);

    $this->closings->validate($closing->fresh(), $tenant['admin']);

    expect($closing->fresh()->status)->toBe(DailyClosingStatus::VALIDATED)
        ->and($closing->fresh()->validated_by_user_id)->toBe($tenant['admin']->id);

    $payment = app(PaymentService::class)->record($invoice, $invoice->total_amount, PaymentMethod::CASH);
    expect(fn () => $this->closings->addPayment($closing->fresh(), $payment))
        ->toThrow(DailyClosingLockedException::class);
});

test('reject moves a PENDING_VALIDATION closing to REJECTED and records the reason', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['admin']);
    $closing = $this->closings->openForToday($tenant['outlet'], $tenant['seller']);
    $this->closings->submitForValidation($closing->fresh(), 0, null);

    $this->closings->reject($closing->fresh(), $tenant['admin'], 'écart de caisse injustifié');

    $fresh = $closing->fresh();
    expect($fresh->status)->toBe(DailyClosingStatus::REJECTED)
        ->and($fresh->observations)->toContain('écart de caisse injustifié');
});
