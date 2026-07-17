<?php

namespace App\Livewire\Transfers;

use App\Models\Transfer;
use App\Modules\Transfer\Services\TransferService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class TransferDetail extends Component
{
    public Transfer $transfer;

    public bool $showShipForm = false;

    public array $shipQuantities = [];

    public bool $showReceiveForm = false;

    public array $receiveQuantities = [];

    public string $cancelReason = '';

    public function mount(Transfer $transfer): void
    {
        $this->transfer = $transfer->load('transferLines.product', 'sourceWarehouse', 'destinationOutlet', 'user');
    }

    public function getCanManageProperty(): bool
    {
        return auth()->user()->can('manage', $this->transfer);
    }

    public function accept(): void
    {
        $this->authorize('manage', $this->transfer);

        try {
            app(TransferService::class)->accept($this->transfer);
        } catch (Throwable $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $this->transfer->refresh();
    }

    public function openShipForm(): void
    {
        $this->authorize('manage', $this->transfer);

        $this->shipQuantities = $this->transfer->transferLines
            ->mapWithKeys(fn ($line) => [$line->product_id => (string) ($line->requested_quantity / 100)])
            ->all();

        $this->showShipForm = true;
    }

    public function ship(): void
    {
        $this->authorize('manage', $this->transfer);

        $quantities = collect($this->shipQuantities)
            ->map(fn ($qty) => (int) round(((float) ($qty ?: 0)) * 100))
            ->all();

        try {
            app(TransferService::class)->ship($this->transfer, $quantities);
        } catch (Throwable $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $this->transfer->refresh();
        $this->showShipForm = false;
    }

    public function openReceiveForm(): void
    {
        $this->authorize('manage', $this->transfer);

        $this->receiveQuantities = $this->transfer->transferLines
            ->mapWithKeys(fn ($line) => [$line->product_id => (string) (max(0, $line->shipped_quantity - $line->received_quantity) / 100)])
            ->all();

        $this->showReceiveForm = true;
    }

    public function receive(): void
    {
        $this->authorize('manage', $this->transfer);

        $quantities = collect($this->receiveQuantities)
            ->map(fn ($qty) => (int) round(((float) ($qty ?: 0)) * 100))
            ->all();

        try {
            app(TransferService::class)->receive($this->transfer, $quantities);
        } catch (Throwable $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $this->transfer->refresh();
        $this->showReceiveForm = false;
    }

    public function requestCancel(): void
    {
        $this->authorize('manage', $this->transfer);

        $this->validate(['cancelReason' => 'required|string|min:3']);

        $this->dispatch(
            'confirm-action',
            title: 'Annuler ce transfert',
            message: 'Confirmer l\'annulation ?',
            detail: $this->cancelReason,
            danger: true,
            eventName: 'transfer.cancel.confirmed',
            eventParams: [],
        );
    }

    #[On('transfer.cancel.confirmed')]
    public function cancelConfirmed(): void
    {
        $this->authorize('manage', $this->transfer);

        try {
            app(TransferService::class)->cancel($this->transfer, $this->cancelReason);
        } catch (Throwable $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $this->transfer->refresh();
        $this->cancelReason = '';
    }

    public function render()
    {
        return view('livewire.transfers.transfer-detail');
    }
}
