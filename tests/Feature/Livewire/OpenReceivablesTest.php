<?php

use App\Enums\ReceivableStatus;
use App\Livewire\Payments\OpenReceivables;
use App\Models\Customer;
use App\Models\Receivable;
use Livewire\Livewire;

test('page loads with 200 for authenticated user', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['seller']);

    $this->get(route('payments.index'))->assertOk();
});

test('open, partial and overdue receivables are shown, sorted by days_overdue then balance_due', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['seller']);

    $overdue = Receivable::factory()->for($tenant['company'])->create([
        'customer_id' => $tenant['customer']->id,
        'status'      => ReceivableStatus::OVERDUE,
        'days_overdue' => 10,
        'balance_due' => 50_000,
    ]);

    $partial = Receivable::factory()->for($tenant['company'])->create([
        'customer_id' => $tenant['customer']->id,
        'status'      => ReceivableStatus::PARTIAL,
        'days_overdue' => 0,
        'balance_due' => 120_000,
    ]);

    $open = Receivable::factory()->for($tenant['company'])->create([
        'customer_id' => $tenant['customer']->id,
        'status'      => ReceivableStatus::OPEN,
        'days_overdue' => 0,
        'balance_due' => 30_000,
    ]);

    $component = Livewire::test(OpenReceivables::class);

    $ids = $component->get('receivables')->pluck('id')->all();

    // overdue (days_overdue=10) must come first; then partial > open by balance_due
    expect($ids)->toBe([$overdue->id, $partial->id, $open->id]);
});

test('paid receivable does not appear in the list', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['seller']);

    $paid = Receivable::factory()->for($tenant['company'])->create([
        'customer_id' => $tenant['customer']->id,
        'status'      => ReceivableStatus::PAID,
        'days_overdue' => 0,
        'balance_due' => 0,
    ]);

    $component = Livewire::test(OpenReceivables::class);

    $ids = $component->get('receivables')->pluck('id')->all();

    expect($ids)->not->toContain($paid->id);
});

test('search by customer name filters results', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['seller']);

    $amadou = Customer::factory()->for($tenant['company'])->create(['name' => 'Amadou Koné']);
    $fatou  = Customer::factory()->for($tenant['company'])->create(['name' => 'Fatou Diallo']);

    Receivable::factory()->for($tenant['company'])->create([
        'customer_id' => $amadou->id,
        'status'      => ReceivableStatus::OPEN,
        'days_overdue' => 0,
    ]);
    Receivable::factory()->for($tenant['company'])->create([
        'customer_id' => $fatou->id,
        'status'      => ReceivableStatus::OPEN,
        'days_overdue' => 0,
    ]);

    $component = Livewire::test(OpenReceivables::class)
        ->set('search', 'Amadou');

    $names = $component->get('receivables')
        ->map(fn ($r) => $r->customer?->name)
        ->all();

    expect($names)->toBe(['Amadou Koné'])
        ->and($names)->not->toContain('Fatou Diallo');
});
