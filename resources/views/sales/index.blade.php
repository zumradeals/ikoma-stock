<x-app-layout :bareDesktop="true">
<div class="lg:flex lg:h-screen lg:overflow-hidden">

    {{-- ════ Sidebar desktop ════ --}}
    <div class="hidden lg:flex">
        <x-ikoma.desktop-sidebar active="sell" />
    </div>

    {{-- ════ Contenu principal ════ --}}
    <div class="flex-1 lg:overflow-y-auto">

        {{-- ── En-tête sticky ── --}}
        <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-base font-extrabold text-ink">Historique</h1>
                <p class="text-xs text-ink-soft">{{ $sales->total() }} vente{{ $sales->total() > 1 ? 's' : '' }} récente{{ $sales->total() > 1 ? 's' : '' }}</p>
            </div>
            <a
                href="{{ route('sales.create') }}"
                wire:navigate
                class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 shadow-brand-glow hover:brightness-90 active:brightness-75 transition"
            >
                <span class="text-base leading-none font-bold">+</span> Nouvelle vente
            </a>
        </div>

        <div class="p-4">

            @if ($sales->isEmpty())
                <div class="rounded-2xl border border-line bg-white px-4 py-12 text-center">
                    <p class="text-2xl mb-2">🛒</p>
                    <p class="text-sm font-bold text-ink">Aucune vente pour le moment.</p>
                </div>
            @else

                {{-- ════ Liste mobile (< lg) ════ --}}
                <div class="lg:hidden space-y-2">
                    @foreach ($sales as $sale)
                        @php
                            $net = $sale->total_amount - $sale->discount_amount;
                            $balanceDue = $sale->invoice?->balance_due ?? 0;
                        @endphp
                        <a
                            href="{{ route('sales.show', $sale) }}"
                            wire:navigate
                            class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-3 gap-3 hover:border-brand/30 transition"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-ink truncate">
                                    {{ $sale->customer?->name ?? 'Client de passage' }}
                                </p>
                                <p class="text-xs text-ink-soft">{{ \App\Support\HumanDate::format($sale->created_at) }}</p>
                                <p class="text-[10px] text-ink-soft/50 mt-0.5">{{ $sale->number }}</p>
                            </div>
                            <div class="text-right shrink-0 space-y-1">
                                <p class="text-sm font-extrabold text-ink"><x-money :amount="$net" /></p>
                                @if ($balanceDue > 0)
                                    <p class="text-xs font-bold text-gold">Reste <x-money :amount="$balanceDue" /></p>
                                @endif
                                @if ($sale->invoice)
                                    <x-ikoma.status-badge :status="\App\Support\SaleStatusPresenter::resolve(
                                        $sale->invoice->payment_status->value,
                                        $sale->invoice->delivery_status->value,
                                        $sale->invoice->total_amount,
                                        $sale->status->value === 'CANCELLED',
                                    )" />
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- ════ Grille desktop (lg+) — 3 colonnes ════ --}}
                <div class="hidden lg:grid lg:grid-cols-3 gap-3">
                    @foreach ($sales as $sale)
                        @php
                            $net = $sale->total_amount - $sale->discount_amount;
                            $balanceDue = $sale->invoice?->balance_due ?? 0;
                        @endphp
                        <a
                            href="{{ route('sales.show', $sale) }}"
                            wire:navigate
                            class="flex flex-col rounded-2xl border border-line bg-white px-4 py-4 gap-2 hover:border-brand/30 transition"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-bold text-ink truncate flex-1">
                                    {{ $sale->customer?->name ?? 'Client de passage' }}
                                </p>
                                @if ($sale->invoice)
                                    <x-ikoma.status-badge :status="\App\Support\SaleStatusPresenter::resolve(
                                        $sale->invoice->payment_status->value,
                                        $sale->invoice->delivery_status->value,
                                        $sale->invoice->total_amount,
                                        $sale->status->value === 'CANCELLED',
                                    )" />
                                @endif
                            </div>
                            <p class="text-xs text-ink-soft">{{ \App\Support\HumanDate::format($sale->created_at) }}</p>
                            <p class="text-[10px] text-ink-soft/50">{{ $sale->number }}</p>
                            <div class="mt-auto pt-2 flex items-end justify-between gap-2">
                                <p class="text-lg font-extrabold text-ink"><x-money :amount="$net" /></p>
                                @if ($balanceDue > 0)
                                    <p class="text-xs font-bold text-gold">Reste <x-money :amount="$balanceDue" /></p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $sales->links() }}
                </div>

            @endif

        </div>{{-- /p-4 --}}
    </div>{{-- /flex-1 --}}

</div>{{-- /lg:flex --}}
</x-app-layout>
