<?php

namespace App\Livewire\Transfers;

use App\Models\Product;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Modules\Transfer\Services\TransferService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TransferList extends Component
{
    public bool $showCreateForm = false;

    public ?int $sourceWarehouseId = null;

    public ?int $destinationOutletId = null;

    public array $lines = [];

    public ?string $formError = null;

    public function getTransfersProperty()
    {
        return Transfer::query()
            ->with(['sourceWarehouse', 'sourceOutlet', 'destinationWarehouse', 'destinationOutlet'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getWarehousesProperty()
    {
        return Warehouse::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getOutletsProperty()
    {
        return auth()->user()->company->outlets()->where('is_active', true)->orderBy('name')->get();
    }

    public function getProductsProperty()
    {
        return Product::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function openCreateForm(): void
    {
        $this->authorize('create', Transfer::class);

        $this->sourceWarehouseId = null;
        $this->destinationOutletId = null;
        $this->lines = [['product_id' => null, 'quantity' => '']];
        $this->formError = null;
        $this->showCreateForm = true;
    }

    public function addLine(): void
    {
        $this->lines[] = ['product_id' => null, 'quantity' => ''];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function createTransfer(): void
    {
        $this->authorize('create', Transfer::class);

        $companyId = auth()->user()->company_id;

        $this->validate([
            'sourceWarehouseId' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'destinationOutletId' => ['required', Rule::exists('outlets', 'id')->where('company_id', $companyId)],
            'lines.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lines = collect($this->lines)
            ->filter(fn (array $line) => $line['product_id'] && (float) ($line['quantity'] ?: 0) > 0)
            ->mapWithKeys(fn (array $line) => [(int) $line['product_id'] => (int) round(((float) $line['quantity']) * 100)])
            ->all();

        if (empty($lines)) {
            $this->formError = 'Ajoutez au moins un produit avec une quantité.';

            return;
        }

        app(TransferService::class)->createRequest(
            ['warehouse_id' => $this->sourceWarehouseId],
            ['outlet_id' => $this->destinationOutletId],
            $lines,
        );

        $this->showCreateForm = false;
    }

    public function render()
    {
        return view('livewire.transfers.transfer-list');
    }
}
