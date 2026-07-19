<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ── accès route ───────────────────────────────────────────────────────────────

test('ADMIN_COMPANY can access team screen', function () {
    $tenant = seedTenant();

    $this->actingAs($tenant['admin'])
        ->get(route('admin.team'))
        ->assertOk();
});

test('OUTLET_MANAGER can access team screen', function () {
    $tenant = seedTenant();

    $manager = User::factory()->for($tenant['company'])
        ->role(UserRole::OUTLET_MANAGER)
        ->create(['is_active' => true]);

    $this->actingAs($manager)
        ->get(route('admin.team'))
        ->assertOk();
});

test('SELLER cannot access team screen', function () {
    $tenant = seedTenant();

    $this->actingAs($tenant['seller'])
        ->get(route('admin.team'))
        ->assertForbidden();
});

// ── création membre ───────────────────────────────────────────────────────────

test('admin can add a new team member and a PIN is generated', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['admin']);

    \Livewire\Livewire::test(\App\Livewire\Admin\TeamMembers::class)
        ->call('openForm')
        ->set('name', 'Marie Kouassi')
        ->set('phone', '0700112233')
        ->set('role', 'SELLER')
        ->call('save')
        ->assertSet('generatedPin', fn ($pin) => strlen((string) $pin) === 4)
        ->assertSet('showForm', false);

    $this->assertDatabaseHas('users', [
        'name'       => 'Marie Kouassi',
        'company_id' => $tenant['company']->id,
        'role'       => 'SELLER',
        'is_active'  => true,
    ]);
});

test('duplicate phone is rejected', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['admin']);

    // Create a user with a known phone
    User::factory()->for($tenant['company'])->create(['phone' => '+2250700112233']);

    \Livewire\Livewire::test(\App\Livewire\Admin\TeamMembers::class)
        ->call('openForm')
        ->set('name', 'Autre Personne')
        ->set('phone', '0700112233')
        ->set('role', 'SELLER')
        ->call('save')
        ->assertHasErrors(['phone']);
});

// ── toggle actif/inactif ──────────────────────────────────────────────────────

test('admin can deactivate a team member', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['admin']);

    \Livewire\Livewire::test(\App\Livewire\Admin\TeamMembers::class)
        ->call('toggleActive', $tenant['seller']->id);

    expect($tenant['seller']->fresh()->is_active)->toBeFalse();
});

test('admin can reactivate a deactivated member', function () {
    $tenant = seedTenant();
    $tenant['seller']->update(['is_active' => false]);
    $this->actingAs($tenant['admin']);

    \Livewire\Livewire::test(\App\Livewire\Admin\TeamMembers::class)
        ->call('toggleActive', $tenant['seller']->id);

    expect($tenant['seller']->fresh()->is_active)->toBeTrue();
});

test('ADMIN_COMPANY account cannot be toggled', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['admin']);

    \Livewire\Livewire::test(\App\Livewire\Admin\TeamMembers::class)
        ->call('toggleActive', $tenant['admin']->id);

    // Admin remains active — no change
    expect($tenant['admin']->fresh()->is_active)->toBeTrue();
});

// ── membre désactivé ne peut plus se connecter ───────────────────────────────

test('a deactivated member is rejected by Auth::attempt', function () {
    $tenant = seedTenant();

    $seller = $tenant['seller'];
    $seller->update(['is_active' => false, 'password' => Hash::make('1234')]);

    $result = \Illuminate\Support\Facades\Auth::attempt([
        'phone'     => $seller->phone,
        'password'  => '1234',
        'is_active' => true,
    ]);

    expect($result)->toBeFalse();
    $this->assertGuest();
});

test('an active member is accepted by Auth::attempt', function () {
    $tenant = seedTenant();

    $seller = $tenant['seller'];
    $seller->update(['is_active' => true, 'password' => Hash::make('1234')]);

    $result = \Illuminate\Support\Facades\Auth::attempt([
        'phone'     => $seller->phone,
        'password'  => '1234',
        'is_active' => true,
    ]);

    expect($result)->toBeTrue();
    $this->assertAuthenticated();
});
