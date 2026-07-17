<div class="p-3 space-y-3">
    @if (session('status'))
        <p class="text-sm text-orange-700 bg-orange-50 rounded-lg p-3">{{ session('status') }}</p>
    @endif
    @error('form') <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $message }}</p> @enderror

    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">{{ $sale->number }}</h1>
        <x-status-badge
            :status="match($sale->status->value) { 'VALIDATED' => 'green', 'CANCELLED' => 'red', default => 'gray' }"
            :label="$sale->status->value"
        />
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-2">
        <p class="text-sm text-gray-500">{{ $sale->customer->name ?? 'Client de passage' }} · {{ $sale->outlet->name }} · {{ $sale->user->name }}</p>

        <div class="divide-y divide-gray-100">
            @foreach ($sale->saleLines as $line)
                <div class="flex justify-between py-1.5 text-sm">
                    <span class="text-gray-600">{{ $line->product->name }} × {{ $line->quantity }}</span>
                    <span class="text-gray-900 font-medium"><x-money :amount="$line->line_total" /></span>
                </div>
            @endforeach
        </div>

        @if ($sale->discount_amount > 0)
            <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                <span class="text-gray-500">Remise{{ $sale->discount_percentage > 0 ? " ({$sale->discount_percentage}%)" : '' }}</span>
                <span class="text-red-600">- <x-money :amount="$sale->discount_amount" /></span>
            </div>
        @endif

        @if ($sale->status->value === 'CANCELLED')
            <div class="rounded-lg bg-red-50 text-red-700 text-xs p-2 mt-2">
                Annulée le {{ $sale->cancelled_at?->format('d/m/Y H:i') }}
                @if ($sale->cancellation_reason)
                    — {{ $sale->cancellation_reason }}
                @endif
            </div>
        @endif
    </div>

    @if ($sale->invoice)
        @if ($sale->invoice->balance_due > 0 && $sale->status->value !== 'CANCELLED')
            <a href="{{ route('sales.payment', $sale) }}" wire:navigate class="block text-center rounded-lg bg-indigo-600 text-white text-sm font-medium py-2.5">
                Enregistrer un paiement
            </a>
        @endif

        <livewire:components.invoice-pdf-viewer :invoice="$sale->invoice" wire:key="viewer-{{ $sale->invoice->id }}" />
    @endif

    @if ($this->canCancel)
        @if ($showCancelForm)
            <form wire:submit="requestCancel" class="rounded-xl border border-gray-200 bg-white p-4 space-y-2">
                <textarea wire:model="cancelReason" rows="2" placeholder="Motif d'annulation" class="block w-full rounded-lg border-gray-200 text-sm"></textarea>
                @error('cancelReason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="flex gap-3">
                    <x-secondary-button type="button" wire:click="$set('showCancelForm', false)" class="flex-1 justify-center">
                        Retour
                    </x-secondary-button>
                    <x-primary-button type="submit" class="flex-1 justify-center bg-red-600 hover:bg-red-700">
                        Confirmer l'annulation
                    </x-primary-button>
                </div>
            </form>
        @else
            <button type="button" wire:click="openCancelForm" class="w-full rounded-lg bg-red-50 text-red-700 text-sm font-medium py-2.5">
                Annuler cette vente
            </button>
        @endif
    @endif
</div>
