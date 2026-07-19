<div>
@php
    $hasDeliveries = $this->hasDeliveriesModule;
    $urgenceCount = ($this->outstandingReceivables > 0 ? 1 : 0)
        + ($hasDeliveries && $this->unpaidDeliveriesCount > 0 ? 1 : 0)
        + ($this->lowStockAlertsCount > 0 ? 1 : 0);
    $firstName = Illuminate\Support\Str::of(auth()->user()->name)->before(' ');
@endphp

{{-- ════════════════════════════════════════════════════════════════════════
     GABARIT DESKTOP — visible à partir de lg:
════════════════════════════════════════════════════════════════════════ --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">

    <x-ikoma.desktop-sidebar active="home" />

    <div class="flex-1 overflow-y-auto p-8">
        <div class="max-w-lg mx-auto space-y-5 pt-4">

            {{-- Bonjour + badge urgences --}}
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-extrabold text-ink">Bonjour {{ $firstName }} 👋</h1>
                @if ($urgenceCount > 0)
                    <span class="inline-flex items-center justify-center h-5 min-w-[20px] rounded-full bg-danger text-white text-[10px] font-extrabold px-1.5 leading-none">
                        {{ $urgenceCount }}
                    </span>
                @endif
            </div>

            {{-- Actions --}}
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft mb-2.5">Que veux-tu faire ?</p>
                <div class="space-y-2.5">
                    <a href="{{ route('sales.create') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-white bg-brand shadow-brand-glow border border-transparent transition hover:brightness-90">
                        <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-white/20 text-base">🛒</span>
                        <span class="flex-1 text-left">Vendre</span>
                        <span class="ml-auto text-sm opacity-50">›</span>
                    </a>
                    <a href="{{ route('payments.index') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-ink bg-white border border-line transition hover:border-brand/40">
                        <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-wash text-base">💰</span>
                        <span class="flex-1 text-left">Encaisser un paiement</span>
                        <span class="ml-auto text-sm opacity-30">›</span>
                    </a>
                    @if ($hasDeliveries)
                    <a href="{{ route('deliveries.index') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-ink bg-white border border-line transition hover:border-brand/40">
                        <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-wash text-base">📦</span>
                        <span class="flex-1 text-left">Livrer un client</span>
                        <span class="ml-auto text-sm opacity-30">›</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Urgences du jour --}}
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft mb-2.5">Aujourd'hui</p>
                @if ($urgenceCount === 0)
                    <p class="text-sm text-ink-soft py-2">🎉 Rien d'urgent aujourd'hui</p>
                @else
                    <div class="space-y-1.5">
                        @if ($this->outstandingReceivables > 0)
                            <a href="{{ route('payments.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                                <span class="h-2 w-2 rounded-full flex-none bg-gold"></span>
                                <span class="flex-1 text-ink">Des clients doivent encore payer</span>
                                <span class="font-extrabold text-ink-soft text-xs"><x-money :amount="$this->outstandingReceivables" /></span>
                            </a>
                        @endif
                        @if ($this->lowStockAlertsCount > 0)
                            <a href="{{ route('stock.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                                <span class="h-2 w-2 rounded-full flex-none bg-danger"></span>
                                <span class="flex-1 text-ink">{{ $this->lowStockAlertsCount }} {{ $this->lowStockAlertsCount > 1 ? 'produits en stock faible' : 'produit en stock faible' }}</span>
                                <span class="text-ink-soft opacity-40">›</span>
                            </a>
                        @endif
                        @if ($hasDeliveries && $this->unpaidDeliveriesCount > 0)
                            <a href="{{ route('deliveries.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                                <span class="h-2 w-2 rounded-full flex-none bg-info"></span>
                                <span class="flex-1 text-ink">{{ $this->unpaidDeliveriesCount }} {{ $this->unpaidDeliveriesCount > 1 ? 'commandes à livrer' : 'commande à livrer' }}</span>
                                <span class="text-ink-soft opacity-40">›</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Dernières ventes --}}
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft">Dernières ventes</p>
                    <a href="{{ route('sales.index') }}" wire:navigate class="text-xs font-bold text-brand hover:brightness-90 transition">Tout voir</a>
                </div>
                <div class="rounded-2xl border border-line bg-white divide-y divide-line overflow-hidden">
                    @forelse ($this->recentSales as $sale)
                        <a href="{{ route('sales.show', $sale) }}" wire:navigate class="flex items-center justify-between px-4 py-3 hover:bg-cream/60 transition">
                            <div class="min-w-0 mr-3">
                                <p class="text-sm font-semibold text-ink truncate">{{ $sale->customer?->name ?? 'Client de passage' }}</p>
                                <p class="text-xs text-ink-soft">{{ \App\Support\HumanDate::format($sale->created_at) }} · {{ $sale->number }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-ink"><x-money :amount="$sale->total_amount - $sale->discount_amount" /></p>
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
                    @empty
                        <p class="text-center text-sm text-ink-soft py-8">Aucune vente pour le moment.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     GABARIT MOBILE — masqué à partir de lg:
════════════════════════════════════════════════════════════════════════ --}}
<div class="lg:hidden p-4 space-y-5">

    {{-- Bonjour + badge urgences --}}
    <div class="flex items-center gap-2.5">
        <h1 class="text-xl font-extrabold text-ink">Bonjour {{ $firstName }} 👋</h1>
        @if ($urgenceCount > 0)
            <span class="inline-flex items-center justify-center h-5 min-w-[20px] rounded-full bg-danger text-white text-[10px] font-extrabold px-1.5 leading-none">
                {{ $urgenceCount }}
            </span>
        @endif
    </div>

    {{-- Actions --}}
    <div>
        <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft mb-2.5">Que veux-tu faire ?</p>
        <div class="space-y-2.5">
            <a href="{{ route('sales.create') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-white bg-brand shadow-brand-glow border border-transparent transition hover:brightness-90">
                <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-white/20 text-base">🛒</span>
                <span class="flex-1 text-left">Vendre</span>
                <span class="ml-auto text-sm opacity-50">›</span>
            </a>
            <a href="{{ route('payments.index') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-ink bg-white border border-line transition hover:border-brand/40">
                <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-wash text-base">💰</span>
                <span class="flex-1 text-left">Encaisser un paiement</span>
                <span class="ml-auto text-sm opacity-30">›</span>
            </a>
            @if ($hasDeliveries)
            <a href="{{ route('deliveries.index') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-ink bg-white border border-line transition hover:border-brand/40">
                <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-wash text-base">📦</span>
                <span class="flex-1 text-left">Livrer un client</span>
                <span class="ml-auto text-sm opacity-30">›</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Urgences du jour --}}
    <div>
        <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft mb-2.5">Aujourd'hui</p>
        @if ($urgenceCount === 0)
            <p class="text-sm text-ink-soft py-2">🎉 Rien d'urgent aujourd'hui</p>
        @else
            <div class="space-y-1.5">
                @if ($this->outstandingReceivables > 0)
                    <a href="{{ route('payments.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                        <span class="h-2 w-2 rounded-full flex-none bg-gold"></span>
                        <span class="flex-1 text-ink">Des clients doivent encore payer</span>
                        <span class="font-extrabold text-ink-soft text-xs"><x-money :amount="$this->outstandingReceivables" /></span>
                    </a>
                @endif
                @if ($this->lowStockAlertsCount > 0)
                    <a href="{{ route('stock.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                        <span class="h-2 w-2 rounded-full flex-none bg-danger"></span>
                        <span class="flex-1 text-ink">{{ $this->lowStockAlertsCount }} {{ $this->lowStockAlertsCount > 1 ? 'produits en stock faible' : 'produit en stock faible' }}</span>
                        <span class="text-ink-soft opacity-40">›</span>
                    </a>
                @endif
                @if ($hasDeliveries && $this->unpaidDeliveriesCount > 0)
                    <a href="{{ route('deliveries.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                        <span class="h-2 w-2 rounded-full flex-none bg-info"></span>
                        <span class="flex-1 text-ink">{{ $this->unpaidDeliveriesCount }} {{ $this->unpaidDeliveriesCount > 1 ? 'commandes à livrer' : 'commande à livrer' }}</span>
                        <span class="text-ink-soft opacity-40">›</span>
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Dernières ventes --}}
    <div>
        <div class="flex items-center justify-between mb-2.5">
            <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft">Dernières ventes</p>
            <a href="{{ route('sales.index') }}" wire:navigate class="text-xs font-bold text-brand hover:brightness-90 transition">Tout voir</a>
        </div>
        <div class="rounded-2xl border border-line bg-white divide-y divide-line overflow-hidden">
            @forelse ($this->recentSales as $sale)
                <a href="{{ route('sales.show', $sale) }}" wire:navigate class="flex items-center justify-between px-4 py-3 hover:bg-cream/60 transition">
                    <div class="min-w-0 mr-3">
                        <p class="text-sm font-semibold text-ink truncate">{{ $sale->customer?->name ?? 'Client de passage' }}</p>
                        <p class="text-xs text-ink-soft">{{ \App\Support\HumanDate::format($sale->created_at) }} · {{ $sale->number }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold text-ink"><x-money :amount="$sale->total_amount - $sale->discount_amount" /></p>
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
            @empty
                <p class="text-center text-sm text-ink-soft py-8">Aucune vente pour le moment.</p>
            @endforelse
        </div>
    </div>

</div>

</div>
