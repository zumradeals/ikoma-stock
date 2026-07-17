<x-app-layout>
    <div class="p-3 space-y-3">
        <div class="flex items-center justify-between">
            <h1 class="text-base font-semibold text-gray-900">Ventes</h1>
            <a href="{{ route('sales.create') }}" wire:navigate class="rounded-lg bg-indigo-600 text-white text-sm font-medium px-3 py-1.5">
                Nouvelle vente
            </a>
        </div>

        <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            @forelse ($sales as $sale)
                <a href="{{ route('sales.show', $sale) }}" wire:navigate class="flex items-center justify-between px-3 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $sale->number }}</p>
                        <p class="text-xs text-gray-400">{{ $sale->customer->name ?? 'Client de passage' }} · {{ $sale->outlet->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900"><x-money :amount="$sale->total_amount - $sale->discount_amount" /></p>
                        <x-status-badge
                            :status="match($sale->status->value) { 'VALIDATED' => 'green', 'CANCELLED' => 'red', default => 'gray' }"
                            :label="$sale->status->value"
                        />
                    </div>
                </a>
            @empty
                <p class="text-center text-sm text-gray-400 py-10">Aucune vente pour le moment.</p>
            @endforelse
        </div>

        {{ $sales->links() }}
    </div>
</x-app-layout>
