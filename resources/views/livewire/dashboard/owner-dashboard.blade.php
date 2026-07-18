<div>
@php
    $todayTotal    = $this->todaySales['total'];
    $trendPct      = $this->salesTrendPercent;
    $isUp          = str_starts_with($trendPct, '+') && $trendPct !== '+0%';
    $isFlat        = $trendPct === 'flat' || $trendPct === '+0%';
    $isFirst       = $trendPct === 'first';
    $cashMethods   = $this->cashByPaymentMethodToday;
    $alertCount    = $this->lowStockAlerts->count() + $this->overdueDeliveries->count();
@endphp

{{-- ════ DESKTOP ════ --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="home" />

    <div class="flex-1 overflow-y-auto p-6 space-y-5">

        {{-- Bonjour --}}
        <h1 class="text-xl font-extrabold text-ink">
            Tableau de bord 📊
        </h1>

        {{-- Ligne 1 : Ventes aujourd'hui + % évolution --}}
        <div class="grid grid-cols-3 gap-4">

            {{-- Grosse carte ventes du jour --}}
            <div class="col-span-1 rounded-2xl bg-brand px-5 py-5 shadow-brand-glow space-y-1">
                <p class="text-xs font-extrabold uppercase tracking-widest text-white/70">Ventes aujourd'hui</p>
                <p class="text-3xl font-extrabold text-white leading-tight"><x-money :amount="$todayTotal" /></p>
                @if ($isFirst)
                    <p class="text-xs font-bold text-white/80">🌟 Premières ventes</p>
                @elseif ($isFlat)
                    <p class="text-xs font-bold text-white/70">→ Stable vs hier</p>
                @else
                    <p class="text-xs font-bold {{ $isUp ? 'text-white' : 'text-white/70' }}">
                        {{ $isUp ? '▲' : '▼' }} {{ $trendPct }} vs hier
                    </p>
                @endif
            </div>

            {{-- Argent reçu : espèces --}}
            <div class="rounded-2xl border border-line bg-white px-5 py-4 space-y-1">
                <p class="text-xs font-extrabold uppercase tracking-widest text-ink-soft">💵 Espèces</p>
                <p class="text-2xl font-extrabold text-ink"><x-money :amount="$cashMethods['cash']" /></p>
                <p class="text-xs text-ink-soft">Reçu aujourd'hui</p>
            </div>

            {{-- Argent reçu : mobile money --}}
            <div class="rounded-2xl border border-line bg-white px-5 py-4 space-y-1">
                <p class="text-xs font-extrabold uppercase tracking-widest text-ink-soft">📱 Mobile Money</p>
                <p class="text-2xl font-extrabold text-ink"><x-money :amount="$cashMethods['mobile_money']" /></p>
                <p class="text-xs text-ink-soft">Reçu aujourd'hui</p>
            </div>
        </div>

        {{-- Ligne 2 : Argent à récupérer + Stock à surveiller + Top produits --}}
        <div class="grid grid-cols-3 gap-4">

            {{-- Argent à récupérer --}}
            <a href="{{ route('payments.index') }}" wire:navigate
               class="rounded-2xl border border-gold/30 bg-gold-wash px-5 py-4 space-y-1 hover:border-gold/60 transition block">
                <p class="text-xs font-extrabold uppercase tracking-widest text-gold/80">⚠️ À récupérer</p>
                <p class="text-2xl font-extrabold text-gold"><x-money :amount="$this->outstandingReceivables" /></p>
                <p class="text-xs text-gold/70">Voir les créances →</p>
            </a>

            {{-- Stock à surveiller --}}
            <a href="{{ route('stock.index') }}" wire:navigate
               class="rounded-2xl border border-line bg-white px-5 py-4 space-y-1 hover:border-brand/30 transition block
                      {{ $this->lowStockAlerts->count() > 0 ? 'border-danger/30 bg-danger-wash' : '' }}">
                <p class="text-xs font-extrabold uppercase tracking-widest {{ $this->lowStockAlerts->count() > 0 ? 'text-danger/80' : 'text-ink-soft' }}">
                    📦 Stock à surveiller
                </p>
                <p class="text-2xl font-extrabold {{ $this->lowStockAlerts->count() > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $this->lowStockAlerts->count() }}
                </p>
                <p class="text-xs {{ $this->lowStockAlerts->count() > 0 ? 'text-danger/70' : 'text-success/80' }}">
                    {{ $this->lowStockAlerts->count() > 0 ? 'Produit(s) en alerte →' : '✅ Tout est OK' }}
                </p>
            </a>

            {{-- Top produits --}}
            <div class="rounded-2xl border border-line bg-white px-5 py-4 space-y-2">
                <p class="text-xs font-extrabold uppercase tracking-widest text-ink-soft mb-2">🏆 Top produits du jour</p>
                @forelse ($this->topProductsToday as $i => $row)
                    <div class="flex items-center justify-between text-sm gap-2">
                        <span class="text-base leading-none">{{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '·')) }}</span>
                        <span class="flex-1 text-ink font-semibold truncate">{{ $row['product_name'] }}</span>
                        <span class="text-xs text-ink-soft shrink-0">× {{ $row['total_qty'] }}</span>
                        <span class="font-extrabold text-ink shrink-0"><x-money :amount="$row['total_revenue']" /></span>
                    </div>
                @empty
                    <p class="text-xs text-ink-soft py-2">Aucune vente aujourd'hui.</p>
                @endforelse
            </div>
        </div>

        {{-- Ligne 3 : Ventes par outlet + par vendeur --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-2xl border border-line bg-white overflow-hidden">
                <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Ventes par point de vente</p>
                <div class="divide-y divide-line">
                    @forelse ($this->todaySales['by_outlet'] as $outletId => $amount)
                        <div class="flex justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-soft">{{ $this->outletNames[$outletId] ?? '—' }}</span>
                            <span class="font-semibold text-ink"><x-money :amount="$amount" /></span>
                        </div>
                    @empty
                        <p class="text-center text-sm text-ink-soft py-6">Aucune vente aujourd'hui.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-line bg-white overflow-hidden">
                <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Ventes par vendeur</p>
                <div class="divide-y divide-line">
                    @forelse ($this->topSellersToday as $row)
                        <div class="flex justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-soft">{{ $this->userNames[$row['user_id']] ?? '—' }}</span>
                            <span class="font-semibold text-ink"><x-money :amount="$row['total']" /></span>
                        </div>
                    @empty
                        <p class="text-center text-sm text-ink-soft py-6">Aucune vente aujourd'hui.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Ligne 4 : Transferts en attente + Commandes en retard --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-2xl border border-line bg-white overflow-hidden">
                <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Transferts en transit</p>
                <div class="divide-y divide-line">
                    @forelse ($this->transfersInTransit as $transfer)
                        <div class="flex justify-between items-center px-4 py-2.5 text-sm">
                            <span class="text-ink-soft">{{ $transfer->number }}</span>
                            <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-gold-wash text-gold">{{ $transfer->status->label() }}</span>
                        </div>
                    @empty
                        <p class="text-center text-sm text-ink-soft py-6">Aucun transfert en cours.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-line bg-white overflow-hidden">
                <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Livraisons en retard</p>
                <div class="divide-y divide-line">
                    @forelse ($this->overdueDeliveries as $invoice)
                        <a href="{{ route('deliveries.show', $invoice) }}" wire:navigate class="flex justify-between items-center px-4 py-2.5 text-sm hover:bg-cream/40 transition">
                            <span class="text-ink-soft">{{ $invoice->number }}</span>
                            <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-danger-wash text-danger">Retard</span>
                        </a>
                    @empty
                        <p class="text-center text-sm text-ink-soft py-6">Aucun retard.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ════ MOBILE ════ --}}
<div class="lg:hidden p-4 space-y-4">

    <h1 class="text-base font-extrabold text-ink">Tableau de bord 📊</h1>

    {{-- Grosse carte ventes du jour --}}
    <div class="rounded-2xl bg-brand px-5 py-5 shadow-brand-glow space-y-1">
        <p class="text-xs font-extrabold uppercase tracking-widest text-white/70">Ventes aujourd'hui</p>
        <p class="text-3xl font-extrabold text-white leading-tight"><x-money :amount="$todayTotal" /></p>
        @if ($isFirst)
            <p class="text-xs font-bold text-white/80">🌟 Premières ventes du compte</p>
        @elseif ($isFlat)
            <p class="text-xs font-bold text-white/70">→ Stable vs hier</p>
        @else
            <p class="text-xs font-bold text-white/80">
                {{ $isUp ? '▲' : '▼' }} {{ $trendPct }} vs hier
            </p>
        @endif
    </div>

    {{-- Espèces + Mobile Money --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-2xl border border-line bg-white px-4 py-3 space-y-0.5">
            <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">💵 Espèces</p>
            <p class="text-xl font-extrabold text-ink"><x-money :amount="$cashMethods['cash']" /></p>
        </div>
        <div class="rounded-2xl border border-line bg-white px-4 py-3 space-y-0.5">
            <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">📱 Mobile</p>
            <p class="text-xl font-extrabold text-ink"><x-money :amount="$cashMethods['mobile_money']" /></p>
        </div>
    </div>

    {{-- À récupérer --}}
    <a href="{{ route('payments.index') }}" wire:navigate
       class="block rounded-2xl border border-gold/30 bg-gold-wash px-4 py-3 hover:border-gold/60 transition">
        <p class="text-xs font-extrabold uppercase tracking-widest text-gold/80">⚠️ Argent à récupérer</p>
        <p class="text-2xl font-extrabold text-gold mt-0.5"><x-money :amount="$this->outstandingReceivables" /></p>
        <p class="text-xs text-gold/70 mt-0.5">Voir les créances →</p>
    </a>

    {{-- Top produits --}}
    <div class="rounded-2xl border border-line bg-white px-4 py-3">
        <p class="text-xs font-extrabold uppercase tracking-widest text-ink-soft mb-2.5">🏆 Top produits du jour</p>
        <div class="space-y-2">
            @forelse ($this->topProductsToday as $i => $row)
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-base leading-none w-5 text-center">{{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '·')) }}</span>
                    <span class="flex-1 text-ink font-semibold truncate">{{ $row['product_name'] }}</span>
                    <span class="text-xs text-ink-soft shrink-0">× {{ $row['total_qty'] }}</span>
                    <span class="font-extrabold text-ink shrink-0 text-xs"><x-money :amount="$row['total_revenue']" /></span>
                </div>
            @empty
                <p class="text-xs text-ink-soft py-1">Aucune vente aujourd'hui.</p>
            @endforelse
        </div>
    </div>

    {{-- Stock à surveiller --}}
    <a href="{{ route('stock.index') }}" wire:navigate
       class="block rounded-2xl border px-4 py-3 hover:brightness-95 transition
              {{ $this->lowStockAlerts->count() > 0 ? 'border-danger/30 bg-danger-wash' : 'border-line bg-white' }}">
        <p class="text-xs font-extrabold uppercase tracking-widest {{ $this->lowStockAlerts->count() > 0 ? 'text-danger/80' : 'text-ink-soft' }}">📦 Stock à surveiller</p>
        <p class="text-2xl font-extrabold mt-0.5 {{ $this->lowStockAlerts->count() > 0 ? 'text-danger' : 'text-success' }}">
            {{ $this->lowStockAlerts->count() }}
        </p>
        <p class="text-xs mt-0.5 {{ $this->lowStockAlerts->count() > 0 ? 'text-danger/70' : 'text-success/80' }}">
            {{ $this->lowStockAlerts->count() > 0 ? 'Produit(s) en alerte →' : '✅ Tout est OK' }}
        </p>
    </a>

    {{-- Ventes par outlet --}}
    <div class="rounded-2xl border border-line bg-white overflow-hidden">
        <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Ventes par point de vente</p>
        <div class="divide-y divide-line">
            @forelse ($this->todaySales['by_outlet'] as $outletId => $amount)
                <div class="flex justify-between px-4 py-2.5 text-sm">
                    <span class="text-ink-soft">{{ $this->outletNames[$outletId] ?? '—' }}</span>
                    <span class="font-semibold text-ink"><x-money :amount="$amount" /></span>
                </div>
            @empty
                <p class="text-center text-sm text-ink-soft py-6">Aucune vente aujourd'hui.</p>
            @endforelse
        </div>
    </div>

    {{-- Ventes par vendeur --}}
    <div class="rounded-2xl border border-line bg-white overflow-hidden">
        <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Ventes par vendeur</p>
        <div class="divide-y divide-line">
            @forelse ($this->topSellersToday as $row)
                <div class="flex justify-between px-4 py-2.5 text-sm">
                    <span class="text-ink-soft">{{ $this->userNames[$row['user_id']] ?? '—' }}</span>
                    <span class="font-semibold text-ink"><x-money :amount="$row['total']" /></span>
                </div>
            @empty
                <p class="text-center text-sm text-ink-soft py-6">Aucune vente aujourd'hui.</p>
            @endforelse
        </div>
    </div>

    {{-- Transferts + retards --}}
    <div class="rounded-2xl border border-line bg-white overflow-hidden">
        <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Transferts en transit</p>
        <div class="divide-y divide-line">
            @forelse ($this->transfersInTransit as $transfer)
                <div class="flex justify-between items-center px-4 py-2.5 text-sm">
                    <span class="text-ink-soft">{{ $transfer->number }}</span>
                    <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-gold-wash text-gold">{{ $transfer->status->label() }}</span>
                </div>
            @empty
                <p class="text-center text-sm text-ink-soft py-6">Aucun transfert en cours.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl border border-line bg-white overflow-hidden">
        <p class="px-4 py-2.5 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Livraisons en retard</p>
        <div class="divide-y divide-line">
            @forelse ($this->overdueDeliveries as $invoice)
                <a href="{{ route('deliveries.show', $invoice) }}" wire:navigate class="flex justify-between items-center px-4 py-2.5 text-sm hover:bg-cream/40 transition">
                    <span class="text-ink-soft">{{ $invoice->number }}</span>
                    <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-danger-wash text-danger">Retard</span>
                </a>
            @empty
                <p class="text-center text-sm text-ink-soft py-6">Aucun retard.</p>
            @endforelse
        </div>
    </div>

</div>

</div>
