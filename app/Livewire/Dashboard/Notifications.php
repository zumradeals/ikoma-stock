<?php

namespace App\Livewire\Dashboard;

use App\Enums\TransferStatus;
use App\Models\DailyClosing;
use App\Models\Invoice;
use App\Models\Transfer;
use App\Modules\Dashboard\Services\DashboardService;
use App\Modules\Receivable\Services\ReceivableService;
use Livewire\Component;

class Notifications extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAllRead(): void
    {
        cache()->put($this->cacheKey(), count($this->alerts()), now()->addDay());
        auth()->user()->unreadNotifications->each->markAsRead();
        $this->open = false;
    }

    protected function cacheKey(): string
    {
        return 'notif-dismissed-count:'.auth()->id();
    }

    protected function alerts(): array
    {
        $user = auth()->user();

        if (! $user->company_id) {
            return [];
        }

        $company = $user->company;
        $alerts = [];

        foreach (app(DashboardService::class)->lowStockAlerts($company) as $product) {
            $remaining = $product->stockLevels->sum('quantity_physical') / 100;

            $alerts[] = [
                'message' => "Stock faible : {$product->name} ({$remaining} restant)",
                'url' => route('stock.index'),
            ];
        }

        foreach (app(ReceivableService::class)->dueToday() as $receivable) {
            $alerts[] = [
                'message' => "Créance à échéance aujourd'hui : {$receivable->customer->name}",
                'url' => route('customers.show', $receivable->customer_id),
            ];
        }

        foreach (Invoice::query()->where('company_id', $company->id)->where('payment_status', 'OVERDUE')->get() as $invoice) {
            $alerts[] = [
                'message' => "Facture en retard de paiement : {$invoice->number}",
                'url' => route('sales.show', $invoice->sale_id),
            ];
        }

        foreach (Invoice::query()->where('company_id', $company->id)->whereDate('due_date', now()->toDateString())->whereNotIn('delivery_status', ['DELIVERED', 'CANCELLED'])->get() as $invoice) {
            $alerts[] = [
                'message' => "Livraison prévue aujourd'hui : {$invoice->number}",
                'url' => route('deliveries.show', $invoice),
            ];
        }

        foreach (Transfer::query()->where('company_id', $company->id)->where('status', TransferStatus::SHIPPED->value)->get() as $transfer) {
            $alerts[] = [
                'message' => "Transfert non réceptionné : {$transfer->number}",
                'url' => route('deliveries.index'),
            ];
        }

        if ($user->outlet_id && ! DailyClosing::query()->where('outlet_id', $user->outlet_id)->whereDate('business_date', now()->toDateString())->exists()) {
            $alerts[] = [
                'message' => "Clôture non effectuée pour aujourd'hui",
                'url' => route('closing.index'),
            ];
        }

        return $alerts;
    }

    public function render()
    {
        $alerts = $this->alerts();
        $dismissed = (int) cache()->get($this->cacheKey(), 0);

        $persisted = auth()->user()->unreadNotifications->map(fn ($notification) => [
            'message' => $notification->data['message'] ?? '',
            'url' => route('notifications.read', $notification->id),
        ])->all();

        $unreadCount = max(0, count($alerts) - $dismissed) + count($persisted);

        return view('livewire.dashboard.notifications', [
            'alerts' => array_merge($persisted, $alerts),
            'unreadCount' => $unreadCount,
        ]);
    }
}
