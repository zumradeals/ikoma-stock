<?php

use App\Enums\UserRole;
use App\Models\PlatformSetting;
use App\Models\User;

// ── Politique d'accès ─────────────────────────────────────────────────────────

test('SUPER_ADMIN can access platform settings screen', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)
        ->get(route('platform.settings'))
        ->assertOk();
});

test('ADMIN_COMPANY cannot access platform settings screen', function () {
    $tenant = seedTenant();

    $this->actingAs($tenant['admin'])
        ->get(route('platform.settings'))
        ->assertForbidden();
});

test('SUPER_ADMIN can save branding settings', function () {
    $super = User::factory()->superAdmin()->create();

    \Livewire\Livewire::actingAs($super)
        ->test(\App\Livewire\Platform\PlatformSettingsForm::class)
        ->set('appName', 'Mon App')
        ->set('appTagline', 'Slogan personnalisé')
        ->call('save')
        ->assertSet('saved', true);

    $settings = PlatformSetting::current()->fresh();
    expect($settings->app_name)->toBe('Mon App')
        ->and($settings->app_tagline)->toBe('Slogan personnalisé');
});

// ── Valeurs par défaut ────────────────────────────────────────────────────────

test('resolvedAppName returns default when not configured', function () {
    $settings = PlatformSetting::current();
    $settings->update(['app_name' => null]);

    expect($settings->fresh()->resolvedAppName())->toBe(PlatformSetting::DEFAULT_APP_NAME);
});

test('resolvedAppTagline returns default when not configured', function () {
    $settings = PlatformSetting::current();
    $settings->update(['app_tagline' => null]);

    expect($settings->fresh()->resolvedAppTagline())->toBe(PlatformSetting::DEFAULT_APP_TAGLINE);
});

test('resolvedAppName returns custom value when configured', function () {
    PlatformSetting::current()->update(['app_name' => 'Boutique XYZ']);

    expect(PlatformSetting::current()->resolvedAppName())->toBe('Boutique XYZ');
});

// ── Écran de connexion ────────────────────────────────────────────────────────

test('login page shows app name from platform settings in title', function () {
    PlatformSetting::current()->update(['app_name' => 'MonERP']);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('MonERP');
});

test('login page does not show duplicate branding header', function () {
    $response = $this->get(route('login'));

    $body = $response->getContent();

    // «Connecte-toi avec ton téléphone» vient du slot login
    $this->assertStringContainsString('Connecte-toi avec ton téléphone', $body);

    // «Votre boutique» (en-tête du layout) ne doit PAS apparaître quand bareHeader=true
    $this->assertStringNotContainsString('Votre boutique', $body);
});

test('custom tagline appears on non-login auth pages', function () {
    PlatformSetting::current()->update([
        'app_name'    => 'Mon App',
        'app_tagline' => 'Slogan unique',
    ]);

    // La page register-welcome utilise le layout sans bareHeader
    $user = seedTenant()['admin'];
    session()->put('onboarding_done', true);

    $this->actingAs($user)
        ->get(route('register.welcome'))
        ->assertOk()
        ->assertSee('Slogan unique');
});
