<div>
    <div class="p-3 space-y-3">
        <div class="flex items-center gap-2">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un produit, une référence..."
                class="flex-1 rounded-lg border-gray-200 text-sm"
            >
            <button
                type="button"
                wire:click="toggleFavorites"
                class="h-9 w-9 shrink-0 rounded-lg flex items-center justify-center {{ $favoritesOnly ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500' }}"
                aria-label="Favoris uniquement"
            >
                ★
            </button>
            <button
                type="button"
                wire:click="toggleView"
                class="h-9 w-9 shrink-0 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center"
                aria-label="Changer d'affichage"
            >
                {{ $view === 'grid' ? '☰' : '▦' }}
            </button>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1 -mx-3 px-3">
            <button
                type="button"
                wire:click="selectCategory(null)"
                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium {{ $categoryId === null ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}"
            >
                Toutes
            </button>
            @foreach ($this->categories as $category)
                <button
                    type="button"
                    wire:click="selectCategory({{ $category->id }})"
                    class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium {{ $categoryId === $category->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}"
                >
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="px-3 pb-4 {{ $view === 'grid' ? 'grid grid-cols-2 gap-3' : 'flex flex-col gap-2' }}">
        @forelse ($this->products as $product)
            @if ($view === 'grid')
                <x-product-card :product="$product" :available="$this->availability[$product->id] ?? 0" />
            @else
                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-2.5" wire:key="product-row-{{ $product->id }}">
                    <button type="button" wire:click="showProduct({{ $product->id }})" class="h-12 w-12 shrink-0 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden">
                        @if ($product->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" class="h-full w-full object-cover" alt="{{ $product->name }}">
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </button>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500"><x-money :amount="$product->sale_price" /></p>
                    </div>
                    <x-status-badge
                        :status="($this->availability[$product->id] ?? 0) <= ($product->low_stock_threshold ?? 0) * 100 ? 'red' : 'green'"
                        :label="number_format(($this->availability[$product->id] ?? 0) / 100, 0, ',', ' ')"
                    />
                    <button
                        type="button"
                        wire:click="addToCart({{ $product->id }})"
                        class="h-8 w-8 shrink-0 rounded-full bg-indigo-600 text-white flex items-center justify-center"
                        aria-label="Ajouter {{ $product->name }} au panier"
                    >
                        +
                    </button>
                </div>
            @endif
        @empty
            <p class="col-span-2 text-center text-sm text-gray-400 py-10">Aucun produit trouvé.</p>
        @endforelse
    </div>

    @if ($this->showingProduct)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 px-4" wire:click.self="closeProduct">
            <div class="w-full sm:max-w-sm bg-white rounded-t-2xl sm:rounded-xl shadow-xl overflow-hidden">
                <div class="aspect-video bg-gray-50 flex items-center justify-center">
                    @if ($this->showingProduct->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($this->showingProduct->image_path) }}" class="h-full w-full object-cover" alt="{{ $this->showingProduct->name }}">
                    @else
                        <span class="text-gray-300 text-sm">Pas d'image</span>
                    @endif
                </div>
                <div class="p-4 space-y-2">
                    <h3 class="text-base font-semibold text-gray-900">{{ $this->showingProduct->name }}</h3>
                    @if ($this->showingProduct->description)
                        <p class="text-sm text-gray-500">{{ $this->showingProduct->description }}</p>
                    @endif
                    <p class="text-lg font-semibold text-gray-900"><x-money :amount="$this->showingProduct->sale_price" /></p>
                    <div class="flex gap-2 pt-2">
                        <button type="button" wire:click="closeProduct" class="flex-1 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium py-2.5">Fermer</button>
                        <button type="button" wire:click="addToCart({{ $this->showingProduct->id }})" class="flex-1 rounded-lg bg-indigo-600 text-white text-sm font-medium py-2.5">Ajouter au panier</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
