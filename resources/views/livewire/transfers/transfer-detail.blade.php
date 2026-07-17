<div class="p-3 space-y-4">
    @error('form') <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $message }}</p> @enderror

    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">{{ $transfer->number }}</h1>
        <x-status-badge
            :status="match($transfer->status->value) {
                'RECEIVED' => 'green',
                'SHIPPED', 'PARTIALLY_RECEIVED', 'ACCEPTED' => 'orange',
                'CANCELLED' => 'red',
                default => 'gray',
            }"
            :label="$transfer->status->value"
        />
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-1 text-sm">
        <p><span class="text-gray-500">Dépôt source :</span> {{ $transfer->sourceWarehouse?->name ?? $transfer->sourceOutlet?->name }}</p>
        <p><span class="text-gray-500">Destination :</span> {{ $transfer->destinationOutlet?->name ?? $transfer->destinationWarehouse?->name }}</p>
        <p><span class="text-gray-500">Demandé par :</span> {{ $transfer->user->name }}</p>
        @if ($transfer->note)
            <p class="text-xs text-gray-500 bg-gray-50 rounded-lg p-2 mt-2">{{ $transfer->note }}</p>
        @endif
    </div>

    <div class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-left text-xs text-gray-400">
                    <th class="px-3 py-2">Produit</th>
                    <th class="px-3 py-2">Demandé</th>
                    <th class="px-3 py-2">Expédié</th>
                    <th class="px-3 py-2">Reçu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($transfer->transferLines as $line)
                    <tr>
                        <td class="px-3 py-2 font-medium text-gray-900">{{ $line->product->name }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ number_format($line->requested_quantity / 100, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ number_format($line->shipped_quantity / 100, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ number_format($line->received_quantity / 100, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($this->canManage)
        <div class="space-y-2">
            @if ($transfer->status->value === 'REQUESTED')
                <button type="button" wire:click="accept" class="w-full rounded-lg bg-indigo-600 text-white text-sm font-medium py-2.5">
                    Accepter le transfert
                </button>
            @endif

            @if ($transfer->status->value === 'ACCEPTED')
                <button type="button" wire:click="openShipForm" class="w-full rounded-lg bg-indigo-600 text-white text-sm font-medium py-2.5">
                    Expédier
                </button>
            @endif

            @if (in_array($transfer->status->value, ['SHIPPED', 'PARTIALLY_RECEIVED']))
                <button type="button" wire:click="openReceiveForm" class="w-full rounded-lg bg-green-600 text-white text-sm font-medium py-2.5">
                    Réceptionner
                </button>
            @endif

            @if (in_array($transfer->status->value, ['DRAFT', 'REQUESTED', 'ACCEPTED']))
                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-2">
                    <textarea wire:model="cancelReason" rows="2" placeholder="Motif d'annulation" class="block w-full rounded-lg border-gray-200 text-sm"></textarea>
                    @error('cancelReason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="button" wire:click="requestCancel" class="w-full rounded-lg bg-red-600 text-white text-sm font-medium py-2.5">
                        Annuler le transfert
                    </button>
                </div>
            @endif
        </div>
    @endif

    @if ($showShipForm)
        <form wire:submit="ship" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <p class="text-sm font-semibold text-gray-900">Quantités expédiées</p>
            @foreach ($transfer->transferLines as $line)
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-gray-700 flex-1">{{ $line->product->name }}</span>
                    <input type="number" step="1" min="0" wire:model="shipQuantities.{{ $line->product_id }}" class="w-24 rounded-md border-gray-300 text-sm">
                </div>
            @endforeach
            <div class="flex gap-3 pt-1">
                <x-secondary-button type="button" wire:click="$set('showShipForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">Confirmer l'expédition</x-primary-button>
            </div>
        </form>
    @endif

    @if ($showReceiveForm)
        <form wire:submit="receive" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <p class="text-sm font-semibold text-gray-900">Quantités réceptionnées</p>
            @foreach ($transfer->transferLines as $line)
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-gray-700 flex-1">{{ $line->product->name }}</span>
                    <input type="number" step="1" min="0" wire:model="receiveQuantities.{{ $line->product_id }}" class="w-24 rounded-md border-gray-300 text-sm">
                </div>
            @endforeach
            <div class="flex gap-3 pt-1">
                <x-secondary-button type="button" wire:click="$set('showReceiveForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">Confirmer la réception</x-primary-button>
            </div>
        </form>
    @endif
</div>
