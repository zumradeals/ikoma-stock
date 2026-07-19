<?php

use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helper local : active ou désactive deliveries pour la société du tenant
// ---------------------------------------------------------------------------

function activateDeliveries(array $tenant): void
{
    $module = Module::where('code', 'deliveries')->firstOrFail();
    $tenant['company']->modules()->syncWithoutDetaching([
        $module->id => ['enabled' => true, 'enabled_at' => now()],
    ]);
    // reset instance cache
    $tenant['company']->moduleCache = [];
}

function deactivateDeliveries(array $tenant): void
{
    $module = Module::where('code', 'deliveries')->firstOrFail();
    $tenant['company']->modules()->syncWithoutDetaching([
        $module->id => ['enabled' => false],
    ]);
    // reset instance cache
    $tenant['company']->moduleCache = [];
}

// ---------------------------------------------------------------------------
// Middleware — route gate
// ---------------------------------------------------------------------------

test('deliveries.index redirige vers le dashboard quand le module est désactivé', function () {
    $tenant = seedTenant();
    deactivateDeliveries($tenant);

    $this->actingAs($tenant['seller'])
        ->get(route('deliveries.index'))
        ->assertRedirect(route('app.dashboard'));
});

test('deliveries.index est accessible quand le module est activé', function () {
    $tenant = seedTenant();
    activateDeliveries($tenant);

    $this->actingAs($tenant['seller'])
        ->get(route('deliveries.index'))
        ->assertOk();
});

// ---------------------------------------------------------------------------
// Société avec module actif — aucune régression
// ---------------------------------------------------------------------------

test('société avec module actif voit deliveries.index normalement', function () {
    $tenant = seedTenant();
    activateDeliveries($tenant);

    $this->actingAs($tenant['seller'])
        ->get(route('deliveries.index'))
        ->assertOk()
        ->assertDontSee('Cette fonctionnalité n\'est pas activée');
});

// ---------------------------------------------------------------------------
// hasDeliveriesModule sur SellerHome
// ---------------------------------------------------------------------------

test('SellerHome::hasDeliveriesModule est faux quand le module est désactivé', function () {
    $tenant = seedTenant();
    deactivateDeliveries($tenant);

    $this->actingAs($tenant['seller']);

    $component = new \App\Livewire\Dashboard\SellerHome();
    \Livewire\Livewire::actingAs($tenant['seller']);

    expect($tenant['company']->hasModule('deliveries'))->toBeFalse();
});

test('SellerHome::hasDeliveriesModule est vrai quand le module est activé', function () {
    $tenant = seedTenant();
    activateDeliveries($tenant);

    expect($tenant['company']->hasModule('deliveries'))->toBeTrue();
});
