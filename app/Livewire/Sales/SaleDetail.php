<?php

namespace App\Livewire\Sales;

use App\Exceptions\Business\BusinessException;
use App\Models\Sale;
use App\Modules\Sale\Services\SaleService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class SaleDetail extends Component
{
    public Sale $sale;

    public bool $showCancelForm = false;

    public string $cancelReason = '';

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load(['customer', 'outlet', 'user', 'saleLines.product', 'invoice.payments']);
    }

    public function getCanCancelProperty(): bool
    {
        return $this->sale->status->value !== 'CANCELLED'
            && auth()->user()->can('cancel', $this->sale);
    }

    public function openCancelForm(): void
    {
        $this->authorize('cancel', $this->sale);
        $this->cancelReason = '';
        $this->showCancelForm = true;
    }

    public function requestCancel(): void
    {
        $this->authorize('cancel', $this->sale);
        $this->validate(['cancelReason' => 'required|string|min:3']);

        $this->dispatch(
            'confirm-action',
            title: 'Annuler cette vente',
            message: 'Confirmer l\'annulation ? Le stock réservé sera libéré et la facture liée annulée.',
            detail: $this->cancelReason,
            danger: true,
            eventName: 'sale.cancel.confirmed',
            eventParams: [],
        );
    }

    #[On('sale.cancel.confirmed')]
    public function cancelConfirmed(): void
    {
        $this->authorize('cancel', $this->sale);

        try {
            app(SaleService::class)->cancel($this->sale, $this->cancelReason);
        } catch (BusinessException $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $this->sale->refresh();
        $this->sale->load('invoice');
        $this->showCancelForm = false;
        $this->cancelReason = '';
    }

    public function render()
    {
        return view('livewire.sales.sale-detail');
    }
}
