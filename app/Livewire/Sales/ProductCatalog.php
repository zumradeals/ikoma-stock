<?php

namespace App\Livewire\Sales;

use App\Enums\LocationType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockLevel;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductCatalog extends Component
{
    public int $outletId;

    public string $search = '';

    public ?int $categoryId = null;

    public string $view = 'grid';

    public bool $favoritesOnly = false;

    public ?int $showingProductId = null;

    public function mount(int $outletId): void
    {
        $this->outletId = $outletId;
    }

    public function toggleView(): void
    {
        $this->view = $this->view === 'grid' ? 'list' : 'grid';
    }

    public function toggleFavorites(): void
    {
        $this->favoritesOnly = ! $this->favoritesOnly;
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    #[On('product.show')]
    public function showProduct(int $productId): void
    {
        $this->showingProductId = $productId;
    }

    public function closeProduct(): void
    {
        $this->showingProductId = null;
    }

    public function addToCart(int $productId): void
    {
        $this->dispatch('cart.add', productId: $productId);
        $this->showingProductId = null;
    }

    public function getCategoriesProperty()
    {
        return Category::query()->orderBy('name')->get();
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('reference', 'like', "%{$this->search}%");
                });
            })
            ->when($this->categoryId, fn ($query) => $query->where('category_id', $this->categoryId))
            ->when($this->favoritesOnly, fn ($query) => $query->where('is_favorite', true))
            ->orderByDesc('is_favorite')
            ->orderBy('display_order')
            ->get();
    }

    public function getAvailabilityProperty(): array
    {
        return StockLevel::query()
            ->where('location_type', LocationType::OUTLET)
            ->where('location_id', $this->outletId)
            ->get()
            ->mapWithKeys(fn (StockLevel $level) => [
                $level->product_id => $level->quantity_physical - $level->quantity_reserved,
            ])
            ->all();
    }

    public function getShowingProductProperty(): ?Product
    {
        return $this->showingProductId ? Product::find($this->showingProductId) : null;
    }

    public function render()
    {
        return view('livewire.sales.product-catalog');
    }
}
