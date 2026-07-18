<div class="lg:flex lg:h-screen lg:overflow-hidden">

{{-- ════ Sidebar desktop ════ --}}
<div class="hidden lg:flex">
    <x-ikoma.desktop-sidebar active="sell" />
</div>

{{-- ════ Contenu principal ════ --}}
<div class="flex-1 lg:overflow-y-auto">

    {{-- ── En-tête sticky ── --}}
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-base font-extrabold text-ink">{{ \App\Support\HumanDate::format($sale->created_at) }}</h1>
                <p class="text-xs text-ink-soft mt-0.5">{{ $sale->number }}</p>
            </div>
            @if ($sale->invoice)
                <x-ikoma.status-badge :status="\App\Support\SaleStatusPresenter::resolve(
                    $sale->invoice->payment_status->value,
                    $sale->invoice->delivery_status->value,
                    $sale->invoice->total_amount,
                    $sale->status->value === 'CANCELLED',
                )" />
            @endif
        </div>
    </div>

    <div class="p-4 space-y-4 max-w-2xl lg:mx-auto">

        {{-- Alertes --}}
        @if (session('status'))
            <div class="rounded-xl bg-success-wash border border-success/20 px-4 py-3 text-sm font-bold text-success">
                {{ session('status') }}
            </div>
        @endif
        @error('form')
            <div class="rounded-xl bg-danger-wash border border-danger/20 px-4 py-3 text-sm font-bold text-danger">
                {{ $message }}
            </div>
        @enderror

        {{-- ── Infos client + vendeur ── --}}
        <div class="rounded-2xl border border-line bg-white px-4 py-3 space-y-2">
            <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Détails</p>
            <div class="divide-y divide-line">
                <div class="flex items-center justify-between py-2 text-sm">
                    <span class="text-ink-soft">Client</span>
                    <span class="font-semibold text-ink">{{ $sale->customer?->name ?? 'Client de passage' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 text-sm">
                    <span class="text-ink-soft">Point de vente</span>
                    <span class="font-semibold text-ink">{{ $sale->outlet->name }}</span>
                </div>
                <div class="flex items-center justify-between py-2 text-sm">
                    <span class="text-ink-soft">Vendeur</span>
                    <span class="font-semibold text-ink">{{ $sale->user->name }}</span>
                </div>
            </div>
            @if ($sale->status->value === 'CANCELLED')
                <div class="rounded-xl bg-danger-wash border border-danger/20 px-3 py-2 text-xs font-bold text-danger">
                    Annulée le {{ $sale->cancelled_at?->format('d/m/Y à H\hi') }}
                    @if ($sale->cancellation_reason)
                        — {{ $sale->cancellation_reason }}
                    @endif
                </div>
            @endif
        </div>

        {{-- ── Lignes de vente ── --}}
        <div class="rounded-2xl border border-line bg-white overflow-hidden">
            <p class="px-4 py-3 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Articles</p>
            <div class="divide-y divide-line">
                @foreach ($sale->saleLines as $line)
                    <div class="flex items-center justify-between px-4 py-3 text-sm">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-ink">{{ $line->product->name }}</p>
                            <p class="text-xs text-ink-soft">× {{ $line->quantity }}</p>
                        </div>
                        <p class="font-extrabold text-ink shrink-0"><x-money :amount="$line->line_total" /></p>
                    </div>
                @endforeach
            </div>

            {{-- Totaux --}}
            <div class="border-t border-line px-4 py-3 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-ink-soft">Sous-total</span>
                    <span class="font-semibold text-ink"><x-money :amount="$sale->total_amount" /></span>
                </div>
                @if ($sale->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-ink-soft">Remise{{ $sale->discount_percentage > 0 ? " ({$sale->discount_percentage} %)" : '' }}</span>
                        <span class="font-bold text-danger">− <x-money :amount="$sale->discount_amount" /></span>
                    </div>
                @endif
                <div class="flex justify-between text-sm pt-1 border-t border-line">
                    <span class="font-extrabold text-ink">Total</span>
                    <span class="font-extrabold text-ink text-base"><x-money :amount="$sale->total_amount - $sale->discount_amount" /></span>
                </div>
                @if ($sale->invoice && $sale->invoice->balance_due > 0 && $sale->status->value !== 'CANCELLED')
                    <div class="flex justify-between text-sm">
                        <span class="font-bold text-gold">Reste à payer</span>
                        <span class="font-extrabold text-gold"><x-money :amount="$sale->invoice->balance_due" /></span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Bouton Encaisser ── --}}
        @if ($sale->invoice && $sale->invoice->balance_due > 0 && $sale->status->value !== 'CANCELLED')
            <a
                href="{{ route('sales.payment', $sale) }}"
                wire:navigate
                class="block w-full text-center rounded-2xl bg-brand text-white text-sm font-extrabold px-4 py-3.5 shadow-brand-glow hover:brightness-90 active:brightness-75 transition"
            >
                Encaisser <x-money :amount="$sale->invoice->balance_due" />
            </a>
        @endif

        {{-- ── Visionneuse PDF ── --}}
        @if ($sale->invoice)
            <livewire:components.invoice-pdf-viewer :invoice="$sale->invoice" wire:key="viewer-{{ $sale->invoice->id }}" />
        @endif

        {{-- ── Annulation ── --}}
        @if ($this->canCancel)
            @if ($showCancelForm)
                <div class="rounded-2xl border border-danger/30 bg-danger-wash p-4 space-y-3">
                    <p class="text-sm font-extrabold text-danger">Annuler cette vente</p>
                    <form wire:submit="requestCancel" class="space-y-3">
                        <textarea
                            wire:model="cancelReason"
                            rows="2"
                            placeholder="Motif d'annulation (obligatoire)"
                            class="w-full rounded-xl border border-danger/30 bg-white px-3 py-2.5 text-sm text-ink focus:border-danger focus:ring-0 focus:outline-none transition resize-none"
                        ></textarea>
                        @error('cancelReason')
                            <p class="text-xs text-danger">{{ $message }}</p>
                        @enderror
                        <div class="flex gap-3">
                            <button
                                type="button"
                                wire:click="$set('showCancelForm', false)"
                                class="flex-1 rounded-xl border border-line text-sm font-bold text-ink-soft px-4 py-2.5 hover:bg-cream transition"
                            >
                                Retour
                            </button>
                            <button
                                type="submit"
                                class="flex-1 rounded-xl bg-danger text-white text-sm font-extrabold px-4 py-2.5 hover:brightness-90 transition"
                            >
                                Confirmer l'annulation
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <button
                    type="button"
                    wire:click="openCancelForm"
                    class="w-full rounded-2xl border border-danger/30 bg-danger-wash text-danger text-sm font-bold px-4 py-3 hover:bg-danger/10 transition"
                >
                    Annuler cette vente
                </button>
            @endif
        @endif

    </div>{{-- /p-4 --}}
</div>{{-- /flex-1 --}}

</div>{{-- /lg:flex --}}
