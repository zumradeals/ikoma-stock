<?php

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function superAdmin(): User
{
    return User::factory()->create([
        'role'       => UserRole::SUPER_ADMIN,
        'company_id' => null,
        'outlet_id'  => null,
    ]);
}

// ---------------------------------------------------------------------------
// Accès aux routes
// ---------------------------------------------------------------------------

test('catalogue modules est accessible au SUPER_ADMIN', function () {
    $this->actingAs(superAdmin())
        ->get(route('platform.modules'))
        ->assertOk();
});

test('catalogue modules est interdit aux autres rôles', function () {
    $tenant = seedTenant();

    $this->actingAs($tenant['admin'])
        ->get(route('platform.modules'))
        ->assertForbidden();
});

test('company-modules est accessible au SUPER_ADMIN', function () {
    $this->actingAs(superAdmin())
        ->get(route('platform.company-modules'))
        ->assertOk();
});

// ---------------------------------------------------------------------------
// Toggle : active le module → hasModule() change immédiatement
// ---------------------------------------------------------------------------

test('toggle active deliveries pour une société et hasModule() retourne true', function () {
    $tenant = seedTenant();
    $company = $tenant['company'];
    $module = Module::where('code', 'deliveries')->firstOrFail();

    // Assurons-nous qu'il est inactif au départ
    $company->modules()->syncWithoutDetaching([$module->id => ['enabled' => false]]);
    $company->moduleCache = [];

    expect($company->hasModule('deliveries'))->toBeFalse();

    // SUPER_ADMIN active le module
    \Livewire\Livewire::actingAs(superAdmin())
        ->test(\App\Livewire\Platform\CompanyModules::class)
        ->call('toggle', $company->id, $module->id);

    // Reset cache + recharge depuis la base
    $company->moduleCache = [];
    expect($company->hasModule('deliveries'))->toBeTrue();
});

test('toggle désactive deliveries et la route redirige', function () {
    $tenant = seedTenant();
    $company = $tenant['company'];
    $module = Module::where('code', 'deliveries')->firstOrFail();

    // Active d'abord
    $company->modules()->syncWithoutDetaching([$module->id => ['enabled' => true, 'enabled_at' => now()]]);
    $company->moduleCache = [];
    expect($company->hasModule('deliveries'))->toBeTrue();

    // SUPER_ADMIN désactive
    \Livewire\Livewire::actingAs(superAdmin())
        ->test(\App\Livewire\Platform\CompanyModules::class)
        ->call('toggle', $company->id, $module->id);

    $company->moduleCache = [];
    expect($company->hasModule('deliveries'))->toBeFalse();

    // La route doit rediriger pour un user de cette société
    $this->actingAs($tenant['seller'])
        ->get(route('deliveries.index'))
        ->assertRedirect(route('app.dashboard'));
});

test('toggle enregistre enabled_by et enabled_at', function () {
    $sa = superAdmin();
    $tenant = seedTenant();
    $company = $tenant['company'];
    $module = Module::where('code', 'deliveries')->firstOrFail();

    $company->modules()->syncWithoutDetaching([$module->id => ['enabled' => false]]);
    $company->moduleCache = [];

    \Livewire\Livewire::actingAs($sa)
        ->test(\App\Livewire\Platform\CompanyModules::class)
        ->call('toggle', $company->id, $module->id);

    $pivot = \Illuminate\Support\Facades\DB::table('company_modules')
        ->where('company_id', $company->id)
        ->where('module_id', $module->id)
        ->first();

    expect((int) $pivot->enabled)->toBe(1)
        ->and($pivot->enabled_by)->toBe($sa->id)
        ->and($pivot->enabled_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Catalogue : créer un module
// ---------------------------------------------------------------------------

test('le SUPER_ADMIN peut créer un module depuis le catalogue', function () {
    \Livewire\Livewire::actingAs(superAdmin())
        ->test(\App\Livewire\Platform\ModuleCatalogue::class)
        ->call('openCreateForm')
        ->set('code', 'new_feature')
        ->set('name', 'Nouvelle fonctionnalité')
        ->set('status', 'planned')
        ->call('save')
        ->assertHasNoErrors();

    expect(Module::where('code', 'new_feature')->exists())->toBeTrue();
});

test('le SUPER_ADMIN peut modifier un module existant', function () {
    $module = Module::where('code', 'deliveries')->firstOrFail();

    \Livewire\Livewire::actingAs(superAdmin())
        ->test(\App\Livewire\Platform\ModuleCatalogue::class)
        ->call('openEditForm', $module->id)
        ->set('name', 'Livraisons v2')
        ->call('save')
        ->assertHasNoErrors();

    expect($module->fresh()->name)->toBe('Livraisons v2');
});
