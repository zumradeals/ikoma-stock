<?php

namespace App\Livewire\Deliveries;

use App\Enums\InvoiceDeliveryStatus;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PendingDeliveries extends Component
{
    public string $filter = 'all';

    public function getInvoicesProperty()
    {
        return Invoice::query()
            ->with(['sale.customer', 'sale.outlet'])
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->when($this->filter === 'today', fn ($q) => $q->whereDate('due_date', now()->toDateString()))
            ->when($this->filter === 'overdue', fn ($q) => $q->whereDate('due_date', '<', now()->toDateString()))
            ->when($this->filter === 'week', fn ($q) => $q->whereBetween('due_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()]))
            ->orderBy('due_date')
            ->get();
    }

    public function status(Invoice $invoice): string
    {
        if (! $invoice->due_date) {
            return 'gray';
        }

        if ($invoice->due_date->isPast() && ! $invoice->due_date->isToday()) {
            return 'red';
        }

        if ($invoice->due_date->isToday()) {
            return 'orange';
        }

        return 'gray';
    }

    public function render()
    {
        return view('livewire.deliveries.pending-deliveries');
    }
}
