<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\Install\InstallController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Livewire\Components\InvoicePdfViewer;
use App\Livewire\Sales\NewSale;
use App\Livewire\Sales\PaymentForm;
use App\Livewire\Sales\SaleDetail;
use App\Livewire\Customers\CustomerCard;
use App\Livewire\Customers\CustomerList;
use App\Livewire\Admin\CompanySettings;
use App\Livewire\Closing\DailyClosingForm;
use App\Livewire\Dashboard\OwnerDashboard;
use App\Livewire\Dashboard\SellerHome;
use App\Livewire\Deliveries\DeliveryDetail;
use App\Livewire\Deliveries\PendingDeliveries;
use App\Livewire\Platform\CompanyList;
use App\Livewire\Platform\PlatformSettingsForm;
use App\Livewire\Stock\StockCorrection;
use App\Livewire\Stock\StockMovements;
use App\Livewire\Stock\StockOverview;
use App\Livewire\Transfers\TransferDetail;
use App\Livewire\Transfers\TransferList;
use Illuminate\Support\Facades\Route;

Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::post('/', [InstallController::class, 'store'])->name('install.store');
});

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->role->landingRoute());
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {

    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::prefix('ventes')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/nouvelle', NewSale::class)->name('sales.create');
        Route::middleware('tenant')->get('/{sale}', SaleDetail::class)->name('sales.show');
        Route::middleware('tenant')->get('/{sale}/paiement', PaymentForm::class)->name('sales.payment');
    });

    Route::middleware('tenant')->prefix('factures')->group(function () {
        Route::get('/{invoice}', InvoicePdfViewer::class)->name('invoices.show');
        Route::get('/{invoice}/telecharger', [InvoiceController::class, 'download'])->name('invoices.download');
    });

    Route::prefix('stock')->group(function () {
        Route::get('/', StockOverview::class)->name('stock.index');
        Route::get('/mouvements', StockMovements::class)->name('stock.movements');
        Route::get('/export', [StockController::class, 'exportPdf'])->name('stock.export');
        Route::middleware('role:ADMIN_COMPANY|WAREHOUSE_KEEPER')
            ->get('/correction', StockCorrection::class)->name('stock.correction');
        // Raccourcis depuis les boutons rapides de StockOverview
        Route::get('/entree', StockMovements::class)->name('stock.entree');
        Route::middleware('role:ADMIN_COMPANY|WAREHOUSE_KEEPER')
            ->get('/ajuster', StockCorrection::class)->name('stock.ajuster');
        Route::get('/transfert', TransferList::class)->name('stock.transfert');
    });

    Route::prefix('clients')->group(function () {
        Route::get('/', CustomerList::class)->name('customers.index');
        Route::middleware('tenant')->get('/{customer}', CustomerCard::class)->name('customers.show');
    });

    Route::get('/paiements', \App\Livewire\Payments\OpenReceivables::class)->name('payments.index');

    Route::prefix('livraisons')->group(function () {
        Route::get('/', PendingDeliveries::class)->name('deliveries.index');
        Route::middleware('tenant')->get('/{invoice}', DeliveryDetail::class)->name('deliveries.show');
        Route::middleware('tenant')->get('/bon/{delivery}', [DeliveryController::class, 'pdf'])->name('deliveries.pdf');
    });

    Route::prefix('cloture')->group(function () {
        Route::get('/', DailyClosingForm::class)->name('closing.index');
    });

    Route::prefix('transferts')->group(function () {
        Route::get('/', TransferList::class)->name('transfers.index');
        Route::middleware('tenant')->get('/{transfer}', TransferDetail::class)->name('transfers.show');
    });

    Route::middleware('tenant')->prefix('app')->group(function () {
        Route::get('/dashboard', OwnerDashboard::class)->name('app.dashboard');
        Route::get('/accueil', SellerHome::class)->name('app.home');
        Route::get('/stock', StockOverview::class)->name('app.stock');
    });

    Route::middleware('role:ADMIN_COMPANY|OUTLET_MANAGER')->prefix('admin')->group(function () {
        Route::get('/', CompanySettings::class)->name('admin.index');
    });

    Route::middleware('super-admin')->prefix('admin/plateforme')->group(function () {
        Route::get('/', CompanyList::class)->name('platform.index');
        Route::get('/parametres', PlatformSettingsForm::class)->name('platform.settings');
    });
});

// Route de preview design — locale uniquement, sans authentification
if (app()->isLocal() || app()->environment('local', 'development')) {
    Route::get('/design/preview', fn () => view('design.preview'))->name('design.preview');
}

require __DIR__.'/auth.php';

