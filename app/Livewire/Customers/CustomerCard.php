<?php

namespace App\Livewire\Customers;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\ReceivableStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Modules\Reminder\Services\ReminderService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CustomerCard extends Component
{
    use WithPagination;

    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getSalesProperty()
    {
        return $this->customer->sales()->with('invoice')->latest()->paginate(10);
    }

    public function getOpenReceivablesProperty()
    {
        return $this->customer->receivables()
            ->where('status', '!=', ReceivableStatus::PAID->value)
            ->get();
    }

    public function getUndeliveredInvoicesProperty()
    {
        return Invoice::query()
            ->whereHas('sale', fn ($q) => $q->where('customer_id', $this->customer->id))
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->get();
    }

    public function sendReminder(): void
    {
        $receivable = $this->openReceivables->sortByDesc('balance_due')->first();

        if (! $receivable) {
            return;
        }

        $message = app(ReminderService::class)->generateWhatsappMessage($receivable);
        app(ReminderService::class)->record($receivable, \App\Enums\ReminderChannel::WHATSAPP, $message);

        $digits = preg_replace('/\D/', '', $this->customer->phone ?? '');

        if ($digits !== '') {
            $this->dispatch('open-url', url: 'https://wa.me/'.$digits.'?text='.urlencode($message));
        }
    }

    public function render()
    {
        return view('livewire.customers.customer-card', [
            'sales' => $this->sales,
        ]);
    }
}
