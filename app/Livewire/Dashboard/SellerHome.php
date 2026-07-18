<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
use App\Modules\Dashboard\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class SellerHome extends Component
{
    public function getCompanyProperty()
    {
        return auth()->user()->company;
    }

    public function getOutstandingReceivablesProperty(): int
    {
        return app(DashboardService::class)->outstandingReceivables($this->company);
    }

    public function getUnpaidDeliveriesCountProperty(): int
    {
        return app(DashboardService::class)->unpaidDeliveries($this->company)->count();
    }

    public function getLowStockAlertsCountProperty(): int
    {
        return app(DashboardService::class)->lowStockAlerts($this->company)->count();
    }

    public function getRecentSalesProperty()
    {
        return Sale::query()
            ->with(['customer', 'invoice'])
            ->where('outlet_id', auth()->user()->outlet_id)
            ->latest()
            ->limit(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.seller-home');
    }
}
