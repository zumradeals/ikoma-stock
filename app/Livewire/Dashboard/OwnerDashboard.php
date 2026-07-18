<?php

namespace App\Livewire\Dashboard;

use App\Enums\InvoiceDeliveryStatus;
use App\Models\Invoice;
use App\Models\Outlet;
use App\Models\User;
use App\Modules\Dashboard\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class OwnerDashboard extends Component
{
    public function getCompanyProperty()
    {
        return auth()->user()->company;
    }

    public function getTodaySalesProperty(): array
    {
        return app(DashboardService::class)->todaySales($this->company);
    }

    public function getCashCollectedProperty(): int
    {
        return app(DashboardService::class)->cashCollected($this->company);
    }

    public function getOutstandingReceivablesProperty(): int
    {
        return app(DashboardService::class)->outstandingReceivables($this->company);
    }

    public function getUnpaidDeliveriesProperty()
    {
        return app(DashboardService::class)->unpaidDeliveries($this->company);
    }

    public function getLowStockAlertsProperty()
    {
        return app(DashboardService::class)->lowStockAlerts($this->company);
    }

    public function getStockValueProperty(): int
    {
        return app(DashboardService::class)->stockValue($this->company);
    }

    public function getTransfersInTransitProperty()
    {
        return app(DashboardService::class)->transfersInTransit($this->company);
    }

    public function getTopSellersTodayProperty(): array
    {
        return app(DashboardService::class)->topSellers($this->company, 'day');
    }

    public function getTopProductsTodayProperty(): array
    {
        return app(DashboardService::class)->topProductsToday($this->company);
    }

    public function getCashByPaymentMethodTodayProperty(): array
    {
        return app(DashboardService::class)->cashByPaymentMethodToday($this->company);
    }

    public function getYesterdayTotalSalesProperty(): int
    {
        return app(DashboardService::class)->yesterdayTotalSales($this->company);
    }

    public function getSalesTrendPercentProperty(): string
    {
        $today     = $this->todaySales['total'];
        $yesterday = $this->yesterdayTotalSales;

        if ($yesterday === 0) {
            return $today > 0 ? 'first' : 'flat';
        }

        $pct = round((($today - $yesterday) / $yesterday) * 100);

        return ($pct >= 0 ? '+' : '') . $pct . '%';
    }

    public function getOverdueDeliveriesProperty()
    {
        return Invoice::query()
            ->where('company_id', $this->company->id)
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();
    }

    public function getOutletNamesProperty(): array
    {
        return Outlet::query()->where('company_id', $this->company->id)->pluck('name', 'id')->all();
    }

    public function getUserNamesProperty(): array
    {
        return User::query()->where('company_id', $this->company->id)->pluck('name', 'id')->all();
    }

    public function render()
    {
        return view('livewire.dashboard.owner-dashboard');
    }
}
