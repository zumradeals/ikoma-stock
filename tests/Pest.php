<?php

use App\Enums\CustomerType;
use App\Enums\LocationType;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Sale\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Crée une société de démo complète (admin, vendeur, magasinier, point de
 * vente, dépôt, produit, client) prête à l'emploi pour les tests de
 * services. $outletStockQty est la quantité physique initiale du produit
 * au point de vente (le dépôt n'a pas de stock par défaut, voir
 * seedWarehouseStock()).
 */
function seedTenant(int $outletStockQty = 100): array
{
    $company = Company::factory()->create();

    $admin = User::factory()->for($company)->role(UserRole::ADMIN_COMPANY)->create();
    $outlet = Outlet::factory()->for($company)->create(['manager_id' => $admin->id]);
    $seller = User::factory()->for($company)->role(UserRole::SELLER)->create(['outlet_id' => $outlet->id]);
    $warehouseKeeper = User::factory()->for($company)->role(UserRole::WAREHOUSE_KEEPER)->create();
    $warehouse = Warehouse::factory()->for($company)->create(['manager_id' => $admin->id]);
    $product = Product::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();

    StockLevel::create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'location_type' => LocationType::OUTLET,
        'location_id' => $outlet->id,
        'quantity_physical' => $outletStockQty,
        'quantity_reserved' => 0,
    ]);

    return compact('company', 'admin', 'seller', 'warehouseKeeper', 'outlet', 'warehouse', 'product', 'customer');
}

/**
 * Crée (ou met à jour) la ligne de stock d'un produit à un dépôt donné.
 */
function seedWarehouseStock(Company $company, Product $product, Warehouse $warehouse, int $quantity): StockLevel
{
    return StockLevel::updateOrCreate(
        [
            'company_id' => $company->id,
            'product_id' => $product->id,
            'location_type' => LocationType::WAREHOUSE,
            'location_id' => $warehouse->id,
        ],
        [
            'quantity_physical' => $quantity,
            'quantity_reserved' => 0,
        ],
    );
}

/**
 * Construit et valide une vente pour le client du tenant, et retourne la
 * facture générée. Le vendeur du tenant est utilisé comme auteur de la
 * vente quel que soit l'utilisateur courant de authAs().
 */
function validatedInvoice(array $tenant, int $quantity): Invoice
{
    $sales = app(SaleService::class);

    $sale = $sales->createDraft([
        'company_id' => $tenant['company']->id,
        'outlet_id' => $tenant['outlet']->id,
        'user_id' => $tenant['seller']->id,
        'customer_id' => $tenant['customer']->id,
        'customer_type' => CustomerType::REGISTERED,
    ]);

    $sales->addLine($sale, $tenant['product'], $quantity);

    return $sales->validate($sale->fresh());
}
