<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="transfers" />
    <div class="flex-1 overflow-y-auto">
<div class="p-3 space-y-3">
    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">Transferts</h1>
        @can('create', \App\Models\Transfer::class)
            <button type="button" wire:click="openCreateForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-3 py-1.5">
                + Nouveau transfert
            </button>
        @endcan
    </div>

    @if ($formError)
        <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $formError }}</p>
    @endif

    @if ($showCreateForm)
        <form wire:submit="createTransfer" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dépôt → Point de vente</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Dépôt source" />
                    <select wire:model="sourceWarehouseId" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                        <option value="">—</option>
                        @foreach ($this->warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('sourceWarehouseId')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Point de vente destination" />
                    <select wire:model="destinationOutletId" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                        <option value="">—</option>
                        @foreach ($this->outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('destinationOutletId')" class="mt-1" />
                </div>
            </div>

            <div class="space-y-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Produits</p>
                @foreach ($lines as $index => $line)
                    <div class="flex gap-2 items-start" wire:key="line-{{ $index }}">
                        <select wire:model="lines.{{ $index }}.product_id" class="flex-1 border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm text-sm">
                            <option value="">Produit —</option>
                            @foreach ($this->products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="1" min="0" wire:model="lines.{{ $index }}.quantity" placeholder="Qté" class="w-24 rounded-md border-gray-300 text-sm">
                        <button type="button" wire:click="removeLine({{ $index }})" class="shrink-0 rounded-lg bg-gray-100 text-gray-500 text-xs px-2 py-2">✕</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addLine" class="text-xs text-orange-600 font-medium">+ Ajouter une ligne</button>
            </div>

            <div class="flex gap-3 pt-1">
                <x-secondary-button type="button" wire:click="$set('showCreateForm', false)" class="flex-1 justify-center">
                    Annuler
                </x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">
                    Créer
                </x-primary-button>
            </div>
        </form>
    @endif

    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($this->transfers as $transfer)
            <a href="{{ route('transfers.show', $transfer) }}" wire:navigate class="flex items-center justify-between px-3 py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $transfer->number }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $transfer->sourceWarehouse?->name ?? $transfer->sourceOutlet?->name }}
                        →
                        {{ $transfer->destinationOutlet?->name ?? $transfer->destinationWarehouse?->name }}
                    </p>
                </div>
                <x-status-badge
                    :status="match($transfer->status->value) {
                        'RECEIVED' => 'green',
                        'SHIPPED', 'PARTIALLY_RECEIVED', 'ACCEPTED' => 'orange',
                        'CANCELLED' => 'red',
                        default => 'gray',
                    }"
                    :label="$transfer->status->label()"
                />
            </a>
        @empty
            <p class="px-3 py-10 text-sm text-gray-400 text-center">Aucun transfert pour l'instant.</p>
        @endforelse
    </div>
</div>
    </div>
</div>
{{-- Mobile --}}
<div class="lg:hidden">
<div class="p-3 space-y-3">
    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">Transferts</h1>
        @can('create', \App\Models\Transfer::class)
            <button type="button" wire:click="openCreateForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-3 py-1.5">
                + Nouveau transfert
            </button>
        @endcan
    </div>

    @if ($formError)
        <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $formError }}</p>
    @endif

    @if ($showCreateForm)
        <form wire:submit="createTransfer" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dépôt → Point de vente</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Dépôt source" />
                    <select wire:model="sourceWarehouseId" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                        <option value="">—</option>
                        @foreach ($this->warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('sourceWarehouseId')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Point de vente destination" />
                    <select wire:model="destinationOutletId" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                        <option value="">—</option>
                        @foreach ($this->outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('destinationOutletId')" class="mt-1" />
                </div>
            </div>

            <div class="space-y-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Produits</p>
                @foreach ($lines as $index => $line)
                    <div class="flex gap-2 items-start" wire:key="line-{{ $index }}">
                        <select wire:model="lines.{{ $index }}.product_id" class="flex-1 border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm text-sm">
                            <option value="">Produit —</option>
                            @foreach ($this->products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="1" min="0" wire:model="lines.{{ $index }}.quantity" placeholder="Qté" class="w-24 rounded-md border-gray-300 text-sm">
                        <button type="button" wire:click="removeLine({{ $index }})" class="shrink-0 rounded-lg bg-gray-100 text-gray-500 text-xs px-2 py-2">✕</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addLine" class="text-xs text-orange-600 font-medium">+ Ajouter une ligne</button>
            </div>

            <div class="flex gap-3 pt-1">
                <x-secondary-button type="button" wire:click="$set('showCreateForm', false)" class="flex-1 justify-center">
                    Annuler
                </x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">
                    Créer
                </x-primary-button>
            </div>
        </form>
    @endif

    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($this->transfers as $transfer)
            <a href="{{ route('transfers.show', $transfer) }}" wire:navigate class="flex items-center justify-between px-3 py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $transfer->number }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $transfer->sourceWarehouse?->name ?? $transfer->sourceOutlet?->name }}
                        →
                        {{ $transfer->destinationOutlet?->name ?? $transfer->destinationWarehouse?->name }}
                    </p>
                </div>
                <x-status-badge
                    :status="match($transfer->status->value) {
                        'RECEIVED' => 'green',
                        'SHIPPED', 'PARTIALLY_RECEIVED', 'ACCEPTED' => 'orange',
                        'CANCELLED' => 'red',
                        default => 'gray',
                    }"
                    :label="$transfer->status->label()"
                />
            </a>
        @empty
            <p class="px-3 py-10 text-sm text-gray-400 text-center">Aucun transfert pour l'instant.</p>
        @endforelse
    </div>
</div>
</div>
</div>
