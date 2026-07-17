<?php

use App\Enums\ReminderChannel;
use App\Modules\Receivable\Services\ReceivableService;
use App\Modules\Reminder\Services\ReminderService;

beforeEach(function () {
    $this->reminders = app(ReminderService::class);
});

test('record creates a reminder and updates last_reminder_at on the receivable', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 3);
    $receivable = app(ReceivableService::class)->syncFromInvoice($invoice);

    $reminder = $this->reminders->record($receivable, ReminderChannel::WHATSAPP, 'Merci de régulariser votre solde.');

    expect($reminder->channel)->toBe(ReminderChannel::WHATSAPP)
        ->and($receivable->fresh()->last_reminder_at)->not->toBeNull();
});

test('generateWhatsappMessage includes the customer name and the amount due', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 3);
    $receivable = app(ReceivableService::class)->syncFromInvoice($invoice);

    $message = $this->reminders->generateWhatsappMessage($receivable);

    expect($message)->toContain($tenant['customer']->name)
        ->and($message)->toContain(number_format(intdiv($receivable->balance_due, 100), 0, ',', ' '));
});

test('scheduleNext updates next_reminder_at', function () {
    $tenant = seedTenant(100);
    $this->actingAs($tenant['seller']);
    $invoice = validatedInvoice($tenant, 3);
    $receivable = app(ReceivableService::class)->syncFromInvoice($invoice);
    $next = now()->addDays(5);

    $this->reminders->scheduleNext($receivable, $next);

    expect($receivable->fresh()->next_reminder_at->isSameDay($next))->toBeTrue();
});
