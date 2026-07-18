<div>
{{-- Desktop (lg+) --}}
<div class="hidden lg:flex h-screen overflow-hidden">
    <x-ikoma.desktop-sidebar active="home" />

    <div class="flex-1 overflow-y-auto p-8 bg-cream">
        <div class="max-w-lg mx-auto space-y-4 pt-4">
            <h1 class="text-xl font-extrabold text-ink">Bonjour 👋</h1>

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
                <a href="{{ route('deliveries.index') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-ink bg-white border border-line transition hover:border-brand/40">
                    <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-wash text-base">📦</span>
                    <span class="flex-1 text-left">Livrer un client</span>
                    <span class="ml-auto text-sm opacity-30">›</span>
                </a>
            </div>

            <div class="pt-2">
                <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft mb-2">Aujourd'hui</p>
                @php $hasUrgent = $this->outstandingReceivables > 0 || $this->unpaidDeliveriesCount > 0 || $this->lowStockAlertsCount > 0; @endphp
                @if (! $hasUrgent)
                    <p class="text-sm text-ink-soft py-2">Rien d'urgent aujourd'hui 🎉</p>
                @else
                    <div class="space-y-1.5">
                        @if ($this->outstandingReceivables > 0)
                            <a href="{{ route('payments.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                                <span class="h-2 w-2 rounded-full flex-none" style="background:var(--gold)"></span>
                                <span class="flex-1 text-ink">{{ $this->unpaidDeliveriesCount > 0 ? 'Des clients' : 'Des clients' }} doivent encore payer</span>
                                <span class="font-extrabold text-ink-soft"><x-money :amount="$this->outstandingReceivables" /></span>
                            </a>
                        @endif
                        @if ($this->unpaidDeliveriesCount > 0)
                            <a href="{{ route('deliveries.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                                <span class="h-2 w-2 rounded-full flex-none" style="background:var(--info)"></span>
                                <span class="flex-1 text-ink">{{ $this->unpaidDeliveriesCount }} {{ $this->unpaidDeliveriesCount > 1 ? 'commandes à livrer' : 'commande à livrer' }}</span>
                                <span class="text-ink-soft opacity-40">›</span>
                            </a>
                        @endif
                        @if ($this->lowStockAlertsCount > 0)
                            <a href="{{ route('stock.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                                <span class="h-2 w-2 rounded-full flex-none" style="background:var(--danger)"></span>
                                <span class="flex-1 text-ink">{{ $this->lowStockAlertsCount }} {{ $this->lowStockAlertsCount > 1 ? 'produits en stock faible' : 'produit en stock faible' }}</span>
                                <span class="text-ink-soft opacity-40">›</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Mobile (< lg) --}}
<div class="lg:hidden p-4 space-y-4">
    <h1 class="text-xl font-extrabold text-ink">Bonjour 👋</h1>

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
        <a href="{{ route('deliveries.index') }}" wire:navigate class="inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-ink bg-white border border-line transition hover:border-brand/40">
            <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-wash text-base">📦</span>
            <span class="flex-1 text-left">Livrer un client</span>
            <span class="ml-auto text-sm opacity-30">›</span>
        </a>
    </div>

    <div class="pt-2">
        <p class="text-[11px] font-extrabold uppercase tracking-widest text-ink-soft mb-2">Aujourd'hui</p>
        @php $hasUrgent = $this->outstandingReceivables > 0 || $this->unpaidDeliveriesCount > 0 || $this->lowStockAlertsCount > 0; @endphp
        @if (! $hasUrgent)
            <p class="text-sm text-ink-soft py-2">Rien d'urgent aujourd'hui 🎉</p>
        @else
            <div class="space-y-1.5">
                @if ($this->outstandingReceivables > 0)
                    <a href="{{ route('payments.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                        <span class="h-2 w-2 rounded-full flex-none" style="background:var(--gold)"></span>
                        <span class="flex-1 text-ink">Des clients doivent encore payer</span>
                        <span class="font-extrabold text-ink-soft"><x-money :amount="$this->outstandingReceivables" /></span>
                    </a>
                @endif
                @if ($this->unpaidDeliveriesCount > 0)
                    <a href="{{ route('deliveries.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                        <span class="h-2 w-2 rounded-full flex-none" style="background:var(--info)"></span>
                        <span class="flex-1 text-ink">{{ $this->unpaidDeliveriesCount }} {{ $this->unpaidDeliveriesCount > 1 ? 'commandes à livrer' : 'commande à livrer' }}</span>
                        <span class="text-ink-soft opacity-40">›</span>
                    </a>
                @endif
                @if ($this->lowStockAlertsCount > 0)
                    <a href="{{ route('stock.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl bg-white border border-line px-4 py-3 text-sm hover:border-brand/30 transition">
                        <span class="h-2 w-2 rounded-full flex-none" style="background:var(--danger)"></span>
                        <span class="flex-1 text-ink">{{ $this->lowStockAlertsCount }} {{ $this->lowStockAlertsCount > 1 ? 'produits en stock faible' : 'produit en stock faible' }}</span>
                        <span class="text-ink-soft opacity-40">›</span>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
</div>
