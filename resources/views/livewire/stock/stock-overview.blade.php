<div class="p-3 space-y-3">
    <div class="flex items-center gap-2">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Rechercher un produit..."
            class="flex-1 rounded-lg border-gray-200 text-sm"
        >
        <a
            href="{{ route('stock.export', ['search' => $search, 'location' => $locationFilter]) }}"
            target="_blank"
            class="shrink-0 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium px-3 py-2"
        >
            Export PDF
        </a>
    </div>

    @if ($this->canManageCatalog)
        <div class="flex gap-2">
            <button type="button" wire:click="openCategoryForm" class="flex-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-3 py-2">
                + Nouvelle catégorie
            </button>
            <button type="button" wire:click="openProductForm" class="flex-1 rounded-lg bg-indigo-600 text-white text-xs font-medium px-3 py-2">
                + Nouveau produit
            </button>
        </div>
    @endif

    @if ($categoryError)
        <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $categoryError }}</p>
    @endif

    @if ($showCategoryForm)
        <form wire:submit="saveCategory" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <div>
                <x-input-label value="Nom de la catégorie" />
                <x-text-input wire:model="categoryName" type="text" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('categoryName')" class="mt-1" />
            </div>
            <div class="flex gap-3">
                <x-secondary-button type="button" wire:click="$set('showCategoryForm', false)" class="flex-1 justify-center">
                    Annuler
                </x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">
                    {{ $editingCategoryId ? 'Enregistrer' : 'Créer' }}
                </x-primary-button>
            </div>
        </form>
    @endif

    @if ($this->canManageCatalog && $this->categories->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            @foreach ($this->categories as $category)
                <div class="flex items-center gap-1 rounded-full bg-gray-100 pl-3 pr-1 py-1 text-xs text-gray-700">
                    {{ $category->name }}
                    <button type="button" wire:click="openCategoryForm({{ $category->id }})" class="rounded-full px-1.5 py-0.5 hover:bg-gray-200">✎</button>
                    <button type="button" wire:click="deleteCategory({{ $category->id }})" wire:confirm="Supprimer cette catégorie ?" class="rounded-full px-1.5 py-0.5 hover:bg-gray-200">✕</button>
                </div>
            @endforeach
        </div>
    @endif

    @if ($showProductForm)
        <form wire:submit="saveProduct" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <div>
                <x-input-label value="Nom du produit" />
                <x-text-input wire:model="productName" type="text" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('productName')" class="mt-1" />
            </div>

            <div>
                <x-input-label value="Catégorie" />
                <select wire:model="productCategoryId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="">—</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('productCategoryId')" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Référence" />
                    <x-text-input wire:model="productReference" type="text" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Unité" />
                    <select wire:model="productUnit" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                        @foreach ($this->units as $unit)
                            <option value="{{ $unit->value }}">{{ $unit->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Prix de vente" />
                    <x-text-input wire:model="productSalePrice" type="number" step="1" min="0" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('productSalePrice')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Prix d'achat" />
                    <x-text-input wire:model="productCostPrice" type="number" step="1" min="0" class="block mt-1 w-full" />
                </div>
            </div>

            <div>
                <x-input-label value="Seuil de stock bas" />
                <x-text-input wire:model="productLowStockThreshold" type="number" step="1" min="0" class="block mt-1 w-full" />
            </div>

            @unless ($editingProductId)
                <div class="border-t border-gray-100 pt-3 space-y-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Stock initial (facultatif)</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Emplacement" />
                            <select wire:model="initialStockLocation" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">—</option>
                                @foreach ($this->warehouses as $warehouse)
                                    <option value="WAREHOUSE:{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                                @foreach ($this->outlets as $outlet)
                                    <option value="OUTLET:{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('initialStockLocation')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Quantité" />
                            <x-text-input wire:model="initialStockQuantity" type="number" step="1" min="0" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('initialStockQuantity')" class="mt-1" />
                        </div>
                    </div>
                </div>
            @endunless

            <div class="flex gap-3">
                <x-secondary-button type="button" wire:click="$set('showProductForm', false)" class="flex-1 justify-center">
                    Annuler
                </x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">
                    {{ $editingProductId ? 'Enregistrer' : 'Créer' }}
                </x-primary-button>
            </div>
        </form>
    @endif

    <div class="flex gap-2 overflow-x-auto pb-1">
        <button type="button" wire:click="$set('locationFilter', '')" class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium {{ $locationFilter === '' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
            Tous les emplacements
        </button>
        @foreach ($this->warehouses as $warehouse)
            <button type="button" wire:click="$set('locationFilter', 'WAREHOUSE:{{ $warehouse->id }}')" class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium {{ $locationFilter === 'WAREHOUSE:'.$warehouse->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ $warehouse->name }}
            </button>
        @endforeach
        @foreach ($this->outlets as $outlet)
            <button type="button" wire:click="$set('locationFilter', 'OUTLET:{{ $outlet->id }}')" class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium {{ $locationFilter === 'OUTLET:'.$outlet->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ $outlet->name }}
            </button>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-left text-xs text-gray-400">
                    <th class="px-3 py-2 sticky left-0 bg-white">Produit</th>
                    @foreach ($this->warehouses as $warehouse)
                        <th class="px-3 py-2 whitespace-nowrap">{{ $warehouse->name }}</th>
                    @endforeach
                    @foreach ($this->outlets as $outlet)
                        <th class="px-3 py-2 whitespace-nowrap">{{ $outlet->name }}</th>
                    @endforeach
                    <th class="px-3 py-2 whitespace-nowrap">Disponible total</th>
                    @if ($this->canManageCatalog)
                        <th class="px-3 py-2 whitespace-nowrap">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->rows as $row)
                    <tr wire:key="stock-row-{{ $row['product']->id }}">
                        <td class="px-3 py-2 font-medium text-gray-900 sticky left-0 bg-white whitespace-nowrap">
                            {{ $row['product']->name }}
                            @unless ($row['product']->is_active)
                                <x-status-badge status="red" label="Inactif" class="ml-1" />
                            @endunless
                        </td>
                        @foreach ($this->warehouses as $warehouse)
                            @php $qty = $this->availableAt($row['byLocation'], 'WAREHOUSE:'.$warehouse->id); @endphp
                            <td class="px-3 py-2 text-gray-600">{{ $qty !== null ? number_format($qty / 100, 0, ',', ' ') : '—' }}</td>
                        @endforeach
                        @foreach ($this->outlets as $outlet)
                            @php $qty = $this->availableAt($row['byLocation'], 'OUTLET:'.$outlet->id); @endphp
                            <td class="px-3 py-2 text-gray-600">{{ $qty !== null ? number_format($qty / 100, 0, ',', ' ') : '—' }}</td>
                        @endforeach
                        <td class="px-3 py-2">
                            <x-status-badge
                                :status="$row['total'] <= ($row['product']->low_stock_threshold ?? 0) * 100 ? 'red' : 'green'"
                                :label="number_format($row['total'] / 100, 0, ',', ' ')"
                            />
                        </td>
                        @if ($this->canManageCatalog)
                            <td class="px-3 py-2 whitespace-nowrap">
                                <button type="button" wire:click="openProductForm({{ $row['product']->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                                <button type="button" wire:click="requestToggleProduct({{ $row['product']->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">
                                    {{ $row['product']->is_active ? 'Désactiver' : 'Réactiver' }}
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-3 py-10 text-center text-gray-400">Aucun produit trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
