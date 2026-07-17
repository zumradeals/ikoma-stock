<div class="p-3 space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-1">
        <h1 class="text-base font-semibold text-gray-900">{{ $customer->name }}</h1>
        <p class="text-sm text-gray-500">{{ $customer->phone }}</p>
        @if ($customer->address)
            <p class="text-sm text-gray-500">{{ $customer->address }}, {{ $customer->neighborhood_city }}</p>
        @endif
    </div>

    <livewire:customers.customer-alert :customer="$customer" wire:key="card-alert-{{ $customer->id }}" />

    <div class="flex gap-2">
        <a href="{{ route('sales.create', ['customer_id' => $customer->id]) }}" wire:navigate class="flex-1 rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5 text-center">
            Nouvelle vente
        </a>
        @if ($this->openReceivables->isNotEmpty())
            <button type="button" wire:click="sendReminder" class="flex-1 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium py-2.5">
                Envoyer rappel
            </button>
        @endif
    </div>

    <div>
        <h2 class="text-sm font-semibold text-gray-900 mb-2">Historique d'achats</h2>
        <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            @forelse ($sales as $sale)
                <a href="{{ route('sales.show', $sale) }}" wire:navigate class="flex items-center justify-between px-3 py-2.5">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $sale->number }}</p>
                        <p class="text-xs text-gray-400">{{ $sale->created_at->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900"><x-money :amount="$sale->total_amount - $sale->discount_amount" /></span>
                </a>
            @empty
                <p class="text-center text-sm text-gray-400 py-6">Aucun achat.</p>
            @endforelse
        </div>
        {{ $sales->links() }}
    </div>
</div>
