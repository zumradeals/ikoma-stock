<?php

namespace App\Livewire\Stock;

use App\Enums\LocationType;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Modules\Stock\Services\StockService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class StockCorrection extends Component
{
    public ?int $productId = null;

    public string $locationType = 'OUTLET';

    public ?int $locationId = null;

    public ?int $countedQuantity = null;

    public string $reason = '';

    public ?string $status = null;

    public function getProductsProperty()
    {
        return Product::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getWarehousesProperty()
    {
        return Warehouse::query()->orderBy('name')->get();
    }

    public function getOutletsProperty()
    {
        return Outlet::query()->orderBy('name')->get();
    }

    public function getCurrentPhysicalProperty(): ?int
    {
        if (! $this->productId || ! $this->locationId) {
            return null;
        }

        return StockLevel::query()
            ->where('product_id', $this->productId)
            ->where('location_type', $this->locationType)
            ->where('location_id', $this->locationId)
            ->value('quantity_physical');
    }

    public function submit(): void
    {
        $this->status = null;

        $this->validate([
            'productId' => 'required|integer',
            'locationId' => 'required|integer',
            'countedQuantity' => 'required|integer|min:0',
            'reason' => 'required|string|min:3',
        ]);

        $product = Product::findOrFail($this->productId);
        $delta = ($this->countedQuantity * 100) - ($this->currentPhysical ?? 0);

        $this->dispatch(
            'confirm-action',
            title: 'Confirmer la correction de stock',
            message: "Ajuster \"{$product->name}\" de {$delta} (en centièmes) ?",
            detail: $this->reason,
            danger: $delta < 0,
            eventName: 'stock-correction.confirmed',
            eventParams: ['delta' => $delta],
        );
    }

    #[On('stock-correction.confirmed')]
    public function confirmed(int $delta): void
    {
        app(StockService::class)->createInventoryCorrection(
            Product::findOrFail($this->productId),
            LocationType::from($this->locationType),
            $this->locationId,
            $delta,
            $this->reason,
        );

        $this->status = 'Correction enregistrée.';
        $this->reset(['countedQuantity', 'reason']);
    }

    public function render()
    {
        return view('livewire.stock.stock-correction');
    }
}
