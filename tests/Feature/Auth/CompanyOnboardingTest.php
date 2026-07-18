<?php

use App\Enums\UserRole;
use App\Exceptions\Business\PhoneAlreadyRegisteredException;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use App\Modules\Company\Services\CompanyOnboardingService;

test('register creates company, outlet and user all correctly linked', function () {
    $service = app(CompanyOnboardingService::class);

    $user = $service->register(
        fullName: 'Awa Koné',
        phone: '+2250700000001',
        password: '1234',
        companyName: 'Boutique Awa',
        outletName: 'Magasin Central',
    );

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Awa Koné')
        ->and($user->phone)->toBe('+2250700000001')
        ->and($user->role)->toBe(UserRole::ADMIN_COMPANY)
        ->and($user->is_active)->toBeTrue();

    $company = Company::find($user->company_id);
    expect($company)->not->toBeNull()
        ->and($company->name)->toBe('Boutique Awa')
        ->and($company->is_active)->toBeTrue();

    $outlet = Outlet::withoutGlobalScopes()->find($user->outlet_id);
    expect($outlet)->not->toBeNull()
        ->and($outlet->name)->toBe('Magasin Central')
        ->and($outlet->company_id)->toBe($company->id);
});

test('register uses default company and outlet names when omitted', function () {
    $service = app(CompanyOnboardingService::class);

    $user = $service->register(
        fullName: 'Kofi Mensah',
        phone: '+2330200000001',
        password: '5678',
    );

    $company = Company::find($user->company_id);
    $outlet = Outlet::withoutGlobalScopes()->find($user->outlet_id);

    expect($company->name)->toBe('Mon entreprise')
        ->and($outlet->name)->toBe('Boutique principale');
});

test('register throws PhoneAlreadyRegisteredException when phone is taken', function () {
    $existing = User::factory()->create(['phone' => '+2250711111111']);

    $companiesBefore = Company::withoutGlobalScopes()->count();
    $outletsBefore = Outlet::withoutGlobalScopes()->count();
    $usersBefore = User::withoutGlobalScopes()->count();

    expect(fn () => app(CompanyOnboardingService::class)->register(
        fullName: 'Doublon',
        phone: '+2250711111111',
        password: '0000',
    ))->toThrow(PhoneAlreadyRegisteredException::class);

    // Aucun enregistrement partiel ne doit subsister
    expect(Company::withoutGlobalScopes()->count())->toBe($companiesBefore)
        ->and(Outlet::withoutGlobalScopes()->count())->toBe($outletsBefore)
        ->and(User::withoutGlobalScopes()->count())->toBe($usersBefore);
});
