<?php

namespace App\Livewire\Deliveries;

use App\Enums\InvoiceDeliveryStatus;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class PendingDeliveries extends Component
{
    public string $filter = 'all';

    /**
     * Une commande non livrée est "en retard" 2 jours après la vente,
     * indépendamment de l'échéance de paiement (due_date).
     */
    private const OVERDUE_AFTER_DAYS = 2;

    public function getInvoicesProperty()
    {
        return Invoice::query()
            ->with(['sale.customer', 'sale.outlet', 'sale.saleLines'])
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->when($this->filter === 'today', fn ($q) => $q->whereDate('due_date', now()->toDateString()))
            ->when($this->filter === 'overdue', fn ($q) => $q->where('created_at', '<', now()->subDays(self::OVERDUE_AFTER_DAYS)))
            ->when($this->filter === 'week', fn ($q) => $q->whereBetween('due_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()]))
            ->orderBy('due_date')
            ->get();
    }

    public function getAllCountProperty(): int
    {
        return Invoice::query()
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->count();
    }

    public function getOverdueCountProperty(): int
    {
        return Invoice::query()
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->where('created_at', '<', now()->subDays(self::OVERDUE_AFTER_DAYS))
            ->count();
    }

    public function getTodayCountProperty(): int
    {
        return Invoice::query()
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->whereDate('due_date', now()->toDateString())
            ->count();
    }

    public function status(Invoice $invoice): string
    {
        if ($invoice->created_at->lt(now()->subDays(self::OVERDUE_AFTER_DAYS))) {
            return 'retard';
        }

        if ($invoice->due_date?->isToday()) {
            return 'aujourd_hui';
        }

        return 'planifiee';
    }

    public function render()
    {
        return view('livewire.deliveries.pending-deliveries');
    }
}
