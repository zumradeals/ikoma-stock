<?php

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

/**
 * Un utilisateur de l'entreprise A ne doit jamais pouvoir voir ni modifier
 * les données de l'entreprise B, même en forgeant l'ID dans l'URL — voir
 * App\Traits\BelongsToTenant, App\Models\Scopes\CompanyScope et
 * App\Http\Middleware\EnsureTenantAccess.
 */
function makeCompanyWithProduct(): array
{
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->role(UserRole::ADMIN_COMPANY)->create();
    $product = Product::factory()->for($company)->create();

    return [$company, $user, $product];
}

test('a user only sees products belonging to their own company', function () {
    [$companyA, $userA, $productA] = makeCompanyWithProduct();
    [$companyB, , $productB] = makeCompanyWithProduct();

    $this->actingAs($userA);

    expect(Product::count())->toBe(1)
        ->and(Product::find($productA->id))->not->toBeNull()
        ->and(Product::find($productB->id))->toBeNull();
});

test('a super admin (null company_id) sees every company data', function () {
    [$companyA, , $productA] = makeCompanyWithProduct();
    [$companyB, , $productB] = makeCompanyWithProduct();

    $superAdmin = User::factory()->superAdmin()->create();
    $this->actingAs($superAdmin);

    expect(Product::count())->toBe(2)
        ->and(Product::find($productA->id))->not->toBeNull()
        ->and(Product::find($productB->id))->not->toBeNull();
});

test('console/unauthenticated context is unrestricted (current_company_id resolves to null)', function () {
    makeCompanyWithProduct();
    makeCompanyWithProduct();

    expect(current_company_id())->toBeNull()
        ->and(Product::count())->toBe(2);
});

test('EnsureTenantAccess blocks a forged cross-company id even if the global scope was bypassed', function () {
    [$companyA, $userA] = makeCompanyWithProduct();
    [, , $productB] = makeCompanyWithProduct();

    // Simule un code qui aurait récupéré le modèle sans passer par le scope
    // (withoutGlobalScopes) avant de le confier à la route — le middleware
    // doit rattraper l'isolation manquante.
    Route::bind('unsafeProduct', fn ($value) => Product::withoutGlobalScopes()->findOrFail($value));

    Route::middleware(['web', SubstituteBindings::class, 'tenant'])
        ->get('/__test/unsafe-products/{unsafeProduct}', fn (Product $unsafeProduct) => response()->json(['id' => $unsafeProduct->id]));

    $this->actingAs($userA)
        ->get("/__test/unsafe-products/{$productB->id}")
        ->assertForbidden();
});

test('EnsureTenantAccess lets a super admin through regardless of the resource company', function () {
    [, , $productA] = makeCompanyWithProduct();
    $superAdmin = User::factory()->superAdmin()->create();

    Route::bind('unsafeProduct', fn ($value) => Product::withoutGlobalScopes()->findOrFail($value));

    Route::middleware(['web', SubstituteBindings::class, 'tenant'])
        ->get('/__test/unsafe-products/{unsafeProduct}', fn (Product $unsafeProduct) => response()->json(['id' => $unsafeProduct->id]));

    $this->actingAs($superAdmin)
        ->get("/__test/unsafe-products/{$productA->id}")
        ->assertOk();
});

test('SuperAdminOnly rejects a regular company user', function () {
    [$companyA, $userA] = makeCompanyWithProduct();

    Route::middleware(['web', 'super-admin'])
        ->get('/__test/platform-only', fn () => response()->json(['ok' => true]));

    $this->actingAs($userA)
        ->get('/__test/platform-only')
        ->assertForbidden();
});

test('SuperAdminOnly accepts the platform super admin', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    Route::middleware(['web', 'super-admin'])
        ->get('/__test/platform-only', fn () => response()->json(['ok' => true]));

    $this->actingAs($superAdmin)
        ->get('/__test/platform-only')
        ->assertOk();
});
