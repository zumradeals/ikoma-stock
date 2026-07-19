<?php

namespace App\Livewire\Stock;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['bareDesktop' => true])]
class StockMovements extends Component
{
    use WithPagination;

    public string $typeFilter = '';

    public string $productFilter = '';

    public string $userFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $expanded = null;

    public function updating(): void
    {
        $this->resetPage();
    }

    public function toggle(int $movementId): void
    {
        $this->expanded = $this->expanded === $movementId ? null : $movementId;
    }

    public function getTypesProperty(): array
    {
        return StockMovementType::cases();
    }

    public function getProductsProperty()
    {
        return Product::query()->orderBy('name')->get();
    }

    public function getUsersProperty()
    {
        return User::query()->orderBy('name')->get();
    }

    public function getMovementsProperty()
    {
        return StockMovement::query()
            ->with(['product', 'user'])
            ->when($this->typeFilter !== '', fn ($q) => $q->where('movement_type', $this->typeFilter))
            ->when($this->productFilter !== '', fn ($q) => $q->where('product_id', $this->productFilter))
            ->when($this->userFilter !== '', fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('movement_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('movement_date', '<=', $this->dateTo))
            ->latest('movement_date')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.stock.stock-movements', [
            'movements' => $this->movements,
        ]);
    }
}
