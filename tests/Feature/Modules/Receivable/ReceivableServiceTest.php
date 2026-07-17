<?php

use App\Enums\ReceivableStatus;
use App\Models\Receivable;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Receivable\Services\ReceivableService;

beforeEach(function () {
    $this->receivables = app(ReceivableService::class);
});

test('syncFromInvoice creates an OPEN receivable for an unpaid invoice with a known customer', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);

    $receivable = $this->receivables->syncFromInvoice($invoice);

    expect($receivable)->not->toBeNull()
        ->and($receivable->status)->toBe(ReceivableStatus::OPEN)
        ->and($receivable->balance_due)->toBe($invoice->total_amount);
});

test('syncFromInvoice returns null when the sale has no identified customer', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $sales = app(\App\Modules\Sale\Services\SaleService::class);
    $sale = $sales->createDraft(['company_id' => $tenant['company']->id, 'outlet_id' => $tenant['outlet']->id, 'user_id' => $tenant['seller']->id, 'customer_type' => 'PASSING']);
    $sales->addLine($sale, $tenant['product'], 3);
    $invoice = $sales->validate($sale->fresh());

    expect($this->receivables->syncFromInvoice($invoice))->toBeNull()
        ->and(Receivable::where('invoice_id', $invoice->id)->exists())->toBeFalse();
});

test('syncFromInvoice updates the same receivable rather than duplicating it', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 5);
    app(PaymentService::class)->record($invoice, intdiv($invoice->total_amount, 2), \App\Enums\PaymentMethod::CASH);

    expect(Receivable::where('invoice_id', $invoice->id)->count())->toBe(1);
    $receivable = Receivable::where('invoice_id', $invoice->id)->first();
    expect($receivable->status)->toBe(ReceivableStatus::PARTIAL);
});

test('markOverdue flips open receivables past their due date and leaves others untouched', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $overdueInvoice = validatedInvoice($tenant, 3);
    $overdueInvoice->update(['due_date' => now()->subDays(10)]);
    $this->receivables->syncFromInvoice($overdueInvoice->fresh());

    $currentInvoice = validatedInvoice($tenant, 2);
    $this->receivables->syncFromInvoice($currentInvoice->fresh());

    $flipped = $this->receivables->markOverdue();

    expect($flipped)->toHaveCount(1)
        ->and(Receivable::where('invoice_id', $overdueInvoice->id)->first()->status)->toBe(ReceivableStatus::OVERDUE)
        ->and(Receivable::where('invoice_id', $currentInvoice->id)->first()->status)->toBe(ReceivableStatus::OPEN);
});

test('dueToday returns only receivables due exactly today', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $todayInvoice = validatedInvoice($tenant, 3);
    $todayInvoice->update(['due_date' => now()]);
    $this->receivables->syncFromInvoice($todayInvoice->fresh());

    $futureInvoice = validatedInvoice($tenant, 2);
    $futureInvoice->update(['due_date' => now()->addDays(15)]);
    $this->receivables->syncFromInvoice($futureInvoice->fresh());

    $due = $this->receivables->dueToday();

    expect($due)->toHaveCount(1)
        ->and($due->first()->invoice_id)->toBe($todayInvoice->id);
});

test('calculateDaysOverdue is 0 for a future due date and positive for a past one', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 3);
    $invoice->update(['due_date' => now()->subDays(7)]);
    $receivable = $this->receivables->syncFromInvoice($invoice->fresh());

    expect($this->receivables->calculateDaysOverdue($receivable))->toBeGreaterThanOrEqual(7);

    $receivable->due_date = now()->addDays(3);
    expect($this->receivables->calculateDaysOverdue($receivable))->toBe(0);
});
