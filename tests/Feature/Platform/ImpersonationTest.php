<?php

use App\Enums\SubscriptionPlan;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\SupportLog;
use App\Models\User;

// ── Plan d'abonnement ─────────────────────────────────────────────────────────

test('new company defaults to DECOUVERTE plan', function () {
    $tenant = seedTenant();

    expect($tenant['company']->fresh()->subscription_plan)
        ->toBe(SubscriptionPlan::DECOUVERTE);
});

test('SUPER_ADMIN can update subscription plan', function () {
    $super   = User::factory()->superAdmin()->create();
    $tenant  = seedTenant();

    \Livewire\Livewire::actingAs($super)
        ->test(\App\Livewire\Platform\CompanyList::class)
        ->call('openEditForm', $tenant['company']->id)
        ->set('subscriptionPlan', SubscriptionPlan::BOUTIQUE->value)
        ->call('updateCompany');

    expect($tenant['company']->fresh()->subscription_plan)->toBe(SubscriptionPlan::BOUTIQUE);
});

// ── Impersonation : démarrage ─────────────────────────────────────────────────

test('SUPER_ADMIN can impersonate a company admin', function () {
    $super  = User::factory()->superAdmin()->create();
    $tenant = seedTenant();

    $this->actingAs($super)
        ->post(route('support.start', $tenant['admin']))
        ->assertRedirect();

    $this->assertAuthenticatedAs($tenant['admin']);
    expect(session('impersonating_original_id'))->toBe($super->id);
});

test('impersonation is logged in support_logs', function () {
    $super  = User::factory()->superAdmin()->create();
    $tenant = seedTenant();

    $this->actingAs($super)
        ->post(route('support.start', $tenant['admin']));

    $this->assertDatabaseHas('support_logs', [
        'super_admin_id'       => $super->id,
        'impersonated_user_id' => $tenant['admin']->id,
        'company_id'           => $tenant['company']->id,
    ]);
});

test('SUPER_ADMIN cannot impersonate another SUPER_ADMIN', function () {
    $super1 = User::factory()->superAdmin()->create();
    $super2 = User::factory()->superAdmin()->create();

    $this->actingAs($super1)
        ->post(route('support.start', $super2))
        ->assertForbidden();
});

test('SUPER_ADMIN cannot self-impersonate', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)
        ->post(route('support.start', $super))
        ->assertForbidden();
});

test('non-super-admin cannot start impersonation', function () {
    $tenant = seedTenant();

    $this->actingAs($tenant['admin'])
        ->post(route('support.start', $tenant['seller']))
        ->assertForbidden();
});

// ── Impersonation : arrêt ─────────────────────────────────────────────────────

test('stop impersonation restores original SUPER_ADMIN session', function () {
    $super  = User::factory()->superAdmin()->create();
    $tenant = seedTenant();

    // Start
    $this->actingAs($super)
        ->post(route('support.start', $tenant['admin']));

    $log = \App\Models\SupportLog::where('super_admin_id', $super->id)->latest()->first();

    // Stop — re-authenticate as impersonated user; session keys were set by start()
    $this->actingAs($tenant['admin'])
        ->post(route('support.stop'))
        ->assertRedirect(route('platform.index'));

    $this->assertAuthenticatedAs($super);
    expect(session('impersonating_original_id'))->toBeNull();
});

test('stop impersonation records ended_at in support_logs', function () {
    $super  = User::factory()->superAdmin()->create();
    $tenant = seedTenant();

    $this->actingAs($super)
        ->post(route('support.start', $tenant['admin']));

    $this->post(route('support.stop'));

    $log = SupportLog::where('super_admin_id', $super->id)->latest()->first();
    expect($log->ended_at)->not->toBeNull();
});

test('stop without active impersonation returns 403', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)
        ->post(route('support.stop'))
        ->assertForbidden();
});

// ── Recherche / filtre liste ──────────────────────────────────────────────────

test('company list filters by search term', function () {
    $super = User::factory()->superAdmin()->create();
    Company::factory()->create(['name' => 'Boutique Koffi']);
    Company::factory()->create(['name' => 'Marché Kouamé']);

    \Livewire\Livewire::actingAs($super)
        ->test(\App\Livewire\Platform\CompanyList::class)
        ->set('search', 'Koffi')
        ->assertSee('Boutique Koffi')
        ->assertDontSee('Marché Kouamé');
});

test('company list filters by suspended status', function () {
    $super = User::factory()->superAdmin()->create();
    Company::factory()->create(['name' => 'Active Co', 'is_active' => true]);
    Company::factory()->create(['name' => 'Suspended Co', 'is_active' => false]);

    \Livewire\Livewire::actingAs($super)
        ->test(\App\Livewire\Platform\CompanyList::class)
        ->set('statusFilter', 'suspended')
        ->assertSee('Suspended Co')
        ->assertDontSee('Active Co');
});
