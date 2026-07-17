<div class="p-3 space-y-4">
    @error('form') <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $message }}</p> @enderror

    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">{{ $invoice->number }}</h1>
        <x-status-badge
            :status="match($invoice->delivery_status->value) { 'DELIVERED' => 'green', 'PARTIAL_DELIVERED' => 'orange', 'CANCELLED' => 'gray', default => 'gray' }"
            :label="$invoice->delivery_status->label()"
        />
    </div>

    <p class="text-sm text-gray-500">{{ $invoice->sale->customer->name ?? 'Client de passage' }}</p>

    <div class="rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
        @foreach ($invoice->sale->saleLines as $line)
            <div class="flex items-center gap-3 px-3 py-2.5" wire:key="delivery-line-{{ $line->id }}">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $line->product->name }}</p>
                    <p class="text-xs text-gray-400">Commandé : {{ $line->quantity }} · Livré : {{ $line->delivered_quantity }} · Restant : {{ $line->remainingToDeliver() }}</p>
                </div>
                @if ($this->canManage && $line->remainingToDeliver() > 0)
                    <input
                        type="number"
                        min="0"
                        max="{{ $line->remainingToDeliver() }}"
                        wire:model="quantities.{{ $line->id }}"
                        class="w-20 rounded-lg border-gray-200 text-sm"
                    >
                @endif
            </div>
        @endforeach
    </div>

    @if ($this->canManage)
        <div class="flex gap-2">
            @if ($invoice->delivery_status === $InvoiceDeliveryStatus::TO_PREPARE)
                <button type="button" wire:click="markReady" class="flex-1 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium py-2.5">
                    Marquer prête
                </button>
            @endif

            @if ($invoice->delivery_status !== $InvoiceDeliveryStatus::DELIVERED)
                <button type="button" wire:click="submitDelivery" class="flex-1 rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5">
                    Livrer
                </button>
            @endif
        </div>
    @endif

    @if ($invoice->deliveries->isNotEmpty())
        <div>
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Bons de livraison</h2>
            <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                @foreach ($invoice->deliveries as $delivery)
                    <a href="{{ route('deliveries.pdf', $delivery) }}" target="_blank" class="flex items-center justify-between px-3 py-2.5">
                        <span class="text-sm text-gray-700">Livraison du {{ $delivery->delivered_at->format('d/m/Y H:i') }}</span>
                        <span class="text-xs text-orange-600 font-medium">PDF</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
