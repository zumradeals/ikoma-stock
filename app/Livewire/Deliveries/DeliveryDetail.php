<?php

namespace App\Livewire\Deliveries;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\UserRole;
use App\Exceptions\Business\BusinessException;
use App\Models\Invoice;
use App\Modules\Delivery\Services\DeliveryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class DeliveryDetail extends Component
{
    public Invoice $invoice;

    public array $quantities = [];

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->loadMissing(['sale.customer', 'sale.saleLines.product', 'deliveries']);

        foreach ($this->invoice->sale->saleLines as $line) {
            $this->quantities[$line->id] = $line->remainingToDeliver();
        }
    }

    public function getCanManageProperty(): bool
    {
        return in_array(auth()->user()->role, [
            UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER, UserRole::WAREHOUSE_KEEPER,
        ], true);
    }

    public function markReady(): void
    {
        app(DeliveryService::class)->markReady($this->invoice);
        $this->invoice->refresh();
    }

    public function submitDelivery(): void
    {
        $this->dispatch(
            'confirm-action',
            title: 'Confirmer la livraison',
            message: 'Enregistrer la livraison des quantités saisies ?',
            detail: null,
            danger: false,
            eventName: 'delivery.confirmed',
            eventParams: [],
        );
    }

    #[On('delivery.confirmed')]
    public function confirmed(): void
    {
        $lines = array_filter(array_map('intval', $this->quantities), fn ($q) => $q > 0);

        try {
            app(DeliveryService::class)->deliver($this->invoice, $lines);
        } catch (BusinessException $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $this->invoice->refresh();
        $this->invoice->load('sale.saleLines.product');

        foreach ($this->invoice->sale->saleLines as $line) {
            $this->quantities[$line->id] = $line->remainingToDeliver();
        }
    }

    public function render()
    {
        return view('livewire.deliveries.delivery-detail', [
            'InvoiceDeliveryStatus' => InvoiceDeliveryStatus::class,
        ]);
    }
}
