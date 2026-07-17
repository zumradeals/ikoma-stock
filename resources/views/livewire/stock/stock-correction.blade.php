<div class="p-4 space-y-4">
    @if ($status)
        <div class="rounded-lg bg-green-50 text-green-700 text-sm px-3 py-2.5">{{ $status }}</div>
    @endif

    <div>
        <label class="text-sm font-medium text-gray-700">Produit</label>
        <select wire:model="productId" class="mt-1 block w-full rounded-lg border-gray-200">
            <option value="">— Choisir —</option>
            @foreach ($this->products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
        @error('productId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Emplacement</label>
        <div class="grid grid-cols-2 gap-2 mt-1">
            <select wire:model="locationType" class="rounded-lg border-gray-200">
                <option value="OUTLET">Point de vente</option>
                <option value="WAREHOUSE">Dépôt</option>
            </select>
            <select wire:model="locationId" class="rounded-lg border-gray-200">
                <option value="">— Choisir —</option>
                @if ($locationType === 'OUTLET')
                    @foreach ($this->outlets as $outlet)
                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                    @endforeach
                @else
                    @foreach ($this->warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        @error('locationId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    @if ($this->currentPhysical !== null)
        <p class="text-sm text-gray-500">Stock actuel enregistré : <strong>{{ number_format($this->currentPhysical / 100, 0, ',', ' ') }}</strong></p>
    @endif

    <div>
        <label class="text-sm font-medium text-gray-700">Quantité réelle constatée</label>
        <input type="number" min="0" wire:model="countedQuantity" class="mt-1 block w-full rounded-lg border-gray-200">
        @error('countedQuantity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Raison (obligatoire)</label>
        <textarea wire:model="reason" rows="2" class="mt-1 block w-full rounded-lg border-gray-200"></textarea>
        @error('reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <button type="button" wire:click="submit" class="w-full rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5">
        Enregistrer la correction
    </button>
</div>
