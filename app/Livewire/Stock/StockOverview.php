<?php

namespace App\Livewire\Stock;

use App\Enums\LocationType;
use App\Enums\ProductUnit;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Modules\Stock\Services\StockService;
use App\Services\ImageOptimizer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class StockOverview extends Component
{
    use WithFileUploads;

    public string $search = '';

    public string $locationFilter = '';

    public bool $showCategoryForm = false;

    public ?int $editingCategoryId = null;

    public string $categoryName = '';

    public ?string $categoryError = null;

    public bool $showProductForm = false;

    public ?int $editingProductId = null;

    public string $productName = '';

    public ?int $productCategoryId = null;

    public string $productReference = '';

    public string $productUnit = 'UNIT';

    public string $productSalePrice = '';

    public string $productCostPrice = '';

    public string $productLowStockThreshold = '0';

    public string $initialStockLocation = '';

    public string $initialStockQuantity = '';

    public $productImage = null;

    public ?string $currentProductImagePath = null;

    public function getWarehousesProperty()
    {
        return Warehouse::query()->orderBy('name')->get();
    }

    public function getOutletsProperty()
    {
        return Outlet::query()->orderBy('name')->get();
    }

    public function getCategoriesProperty()
    {
        return Category::query()->orderBy('name')->get();
    }

    public function getUnitsProperty(): array
    {
        return ProductUnit::cases();
    }

    public function getCanManageCatalogProperty(): bool
    {
        return in_array(auth()->user()->role, [UserRole::ADMIN_COMPANY, UserRole::WAREHOUSE_KEEPER], true);
    }

    public function getRowsProperty()
    {
        $products = Product::query()
            ->when(! $this->canManageCatalog, fn ($q) => $q->where('is_active', true))
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        $levels = StockLevel::query()->get()->groupBy('product_id');

        return $products->map(function (Product $product) use ($levels) {
            $productLevels = $levels->get($product->id, collect());

            $byLocation = $productLevels->keyBy(fn (StockLevel $l) => $l->location_type->value.':'.$l->location_id);

            return [
                'product' => $product,
                'byLocation' => $byLocation,
                'total' => $productLevels->sum(fn (StockLevel $l) => $l->quantity_physical - $l->quantity_reserved),
            ];
        })->filter(function (array $row) {
            if ($this->locationFilter === '') {
                return true;
            }

            return $row['byLocation']->has($this->locationFilter);
        });
    }

    public function availableAt($byLocation, string $key): ?int
    {
        $level = $byLocation->get($key);

        return $level ? $level->quantity_physical - $level->quantity_reserved : null;
    }

    // ------------------------------------------------------------------
    // Catégories
    // ------------------------------------------------------------------

    public function openCategoryForm(?int $categoryId = null): void
    {
        $this->categoryName = '';
        $this->categoryError = null;
        $this->editingCategoryId = null;

        if ($categoryId) {
            $category = Category::findOrFail($categoryId);
            $this->authorize('update', $category);

            $this->editingCategoryId = $category->id;
            $this->categoryName = $category->name;
        } else {
            $this->authorize('create', Category::class);
        }

        $this->showCategoryForm = true;
    }

    public function saveCategory(): void
    {
        $this->validate(['categoryName' => 'required|string|max:255']);

        if ($this->editingCategoryId) {
            $category = Category::findOrFail($this->editingCategoryId);
            $this->authorize('update', $category);
            $category->update(['name' => $this->categoryName]);
        } else {
            $this->authorize('create', Category::class);

            $category = Category::create([
                'company_id' => auth()->user()->company_id,
                'name' => $this->categoryName,
            ]);

            $this->productCategoryId = $category->id;
        }

        $this->showCategoryForm = false;
        $this->editingCategoryId = null;
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            $this->categoryError = "Impossible de supprimer « {$category->name} » : des produits y sont rattachés.";

            return;
        }

        $category->delete();
        $this->categoryError = null;
    }

    // ------------------------------------------------------------------
    // Produits
    // ------------------------------------------------------------------

    public function openProductForm(?int $productId = null): void
    {
        $this->reset(['productName', 'productCategoryId', 'productReference', 'productSalePrice', 'productCostPrice', 'initialStockLocation', 'initialStockQuantity', 'productImage']);
        $this->productUnit = 'UNIT';
        $this->productLowStockThreshold = '0';
        $this->editingProductId = null;
        $this->currentProductImagePath = null;

        if ($productId) {
            $product = Product::findOrFail($productId);
            $this->authorize('update', $product);

            $this->editingProductId = $product->id;
            $this->productName = $product->name;
            $this->productCategoryId = $product->category_id;
            $this->productReference = $product->reference ?? '';
            $this->productUnit = $product->unit->value;
            $this->productSalePrice = (string) ($product->sale_price / 100);
            $this->productCostPrice = $product->cost_price !== null ? (string) ($product->cost_price / 100) : '';
            $this->productLowStockThreshold = (string) $product->low_stock_threshold;
            $this->currentProductImagePath = $product->image_path;
        } else {
            $this->authorize('create', Product::class);
        }

        $this->showProductForm = true;
    }

    public function saveProduct(): void
    {
        $companyId = auth()->user()->company_id;
        $isNew = ! $this->editingProductId;

        $this->validate([
            'productName' => 'required|string|max:255',
            'productCategoryId' => ['required', Rule::exists('categories', 'id')->where('company_id', $companyId)],
            'productReference' => 'nullable|string|max:255',
            'productUnit' => Rule::in(ProductUnit::values()),
            'productSalePrice' => 'required|numeric|min:0',
            'productCostPrice' => 'nullable|numeric|min:0',
            'productLowStockThreshold' => 'nullable|integer|min:0',
            'initialStockLocation' => [
                $isNew && $this->initialStockQuantity !== '' ? 'required' : 'nullable',
                Rule::in(array_merge(
                    $this->warehouses->map(fn ($w) => 'WAREHOUSE:'.$w->id)->all(),
                    $this->outlets->map(fn ($o) => 'OUTLET:'.$o->id)->all(),
                )),
            ],
            'initialStockQuantity' => 'nullable|numeric|min:0',
            'productImage' => 'nullable|image|max:10240',
        ]);

        $attributes = [
            'category_id' => $this->productCategoryId,
            'name' => $this->productName,
            'reference' => $this->productReference ?: null,
            'unit' => $this->productUnit,
            'sale_price' => (int) round(((float) $this->productSalePrice) * 100),
            'cost_price' => $this->productCostPrice !== '' ? (int) round(((float) $this->productCostPrice) * 100) : null,
            'low_stock_threshold' => (int) ($this->productLowStockThreshold ?: 0),
        ];

        if ($this->productImage) {
            $attributes['image_path'] = ImageOptimizer::storeCompressed($this->productImage, 'products');
        }

        if ($this->editingProductId) {
            $product = Product::findOrFail($this->editingProductId);
            $this->authorize('update', $product);
            $product->update($attributes);
        } else {
            $this->authorize('create', Product::class);
            $product = Product::create($attributes + ['company_id' => $companyId, 'is_active' => true]);

            $quantity = (float) ($this->initialStockQuantity ?: 0);

            if ($quantity > 0 && $this->initialStockLocation !== '') {
                [$type, $locationId] = explode(':', $this->initialStockLocation);

                app(StockService::class)->recordInitialStock(
                    $product,
                    LocationType::from($type),
                    (int) $locationId,
                    (int) round($quantity * 100),
                );
            }
        }

        $this->showProductForm = false;
        $this->editingProductId = null;
    }

    public function requestToggleProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->authorize('update', $product);

        $this->dispatch(
            'confirm-action',
            title: $product->is_active ? 'Désactiver ce produit' : 'Réactiver ce produit',
            message: $product->is_active
                ? "Désactiver « {$product->name} » ? Il n'apparaîtra plus dans le catalogue de vente."
                : "Réactiver « {$product->name} » ?",
            detail: null,
            danger: $product->is_active,
            eventName: 'stock.product-toggle.confirmed',
            eventParams: ['productId' => $productId],
        );
    }

    #[On('stock.product-toggle.confirmed')]
    public function toggleProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->authorize('update', $product);
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function render()
    {
        return view('livewire.stock.stock-overview');
    }
}
