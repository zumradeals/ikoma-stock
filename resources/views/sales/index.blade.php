<x-app-layout>
    <div class="p-3 space-y-3">
        <h1 class="text-base font-semibold text-gray-900">Ventes</h1>

        <a
            href="{{ route('sales.create') }}"
            wire:navigate
            class="flex items-center justify-center gap-2 rounded-xl bg-[var(--brand,#ea580c)] text-white text-base font-semibold py-3.5 shadow-sm"
        >
            <span class="text-xl leading-none">+</span> Nouvelle vente
        </a>

        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide pt-1">Historique</p>

        <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            @forelse ($sales as $sale)
                <a href="{{ route('sales.show', $sale) }}" wire:navigate class="flex items-center justify-between px-3 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ \App\Support\HumanDate::format($sale->created_at) }}</p>
                        <p class="text-xs text-gray-400">{{ $sale->customer->name ?? 'Client de passage' }} · {{ $sale->outlet->name }}</p>
                        <p class="text-[10px] text-gray-300 mt-0.5">{{ $sale->number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900"><x-money :amount="$sale->total_amount - $sale->discount_amount" /></p>
                        @if ($sale->invoice)
                            <x-ikoma.status-badge :status="\App\Support\SaleStatusPresenter::resolve(
                                $sale->invoice->payment_status->value,
                                $sale->invoice->delivery_status->value,
                                $sale->invoice->total_amount,
                                $sale->status->value === 'CANCELLED',
                            )" />
                        @else
                            <x-status-badge
                                :status="match($sale->status->value) { 'VALIDATED' => 'green', 'CANCELLED' => 'red', default => 'gray' }"
                                :label="$sale->status->label()"
                            />
                        @endif
                    </div>
                </a>
            @empty
                <p class="text-center text-sm text-gray-400 py-10">Aucune vente pour le moment.</p>
            @endforelse
        </div>

        {{ $sales->links() }}
    </div>
</x-app-layout>
