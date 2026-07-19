{{-- Flash messages --}}
@if (session('status'))
    <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm font-bold text-green-700">
        {{ session('status') }}
    </div>
@endif
@error('action')
    <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-bold text-red-600">
        {{ $message }}
    </div>
@enderror

{{-- Infos --}}
<div class="rounded-2xl border border-line bg-white px-4 py-3 space-y-2">
    <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Détails</p>
    <div class="divide-y divide-line">
        <div class="flex items-center justify-between py-2 text-sm">
            <span class="text-ink-soft">Client</span>
            <span class="font-semibold text-ink">{{ $quote->customer?->name ?? 'Client de passage' }}</span>
        </div>
        <div class="flex items-center justify-between py-2 text-sm">
            <span class="text-ink-soft">Point de vente</span>
            <span class="font-semibold text-ink">{{ $quote->outlet->name }}</span>
        </div>
        <div class="flex items-center justify-between py-2 text-sm">
            <span class="text-ink-soft">Créé par</span>
            <span class="font-semibold text-ink">{{ $quote->user->name }}</span>
        </div>
        @if ($quote->valid_until)
            <div class="flex items-center justify-between py-2 text-sm">
                <span class="text-ink-soft">Valide jusqu'au</span>
                <span class="font-semibold {{ $quote->valid_until->isPast() ? 'text-danger' : 'text-ink' }}">
                    {{ $quote->valid_until->format('d/m/Y') }}
                    @if ($quote->valid_until->isPast()) (expiré) @endif
                </span>
            </div>
        @endif
        @if ($quote->convertedSale)
            <div class="flex items-center justify-between py-2 text-sm">
                <span class="text-ink-soft">Vente générée</span>
                <a href="{{ route('sales.show', $quote->convertedSale) }}" wire:navigate
                   class="font-semibold text-brand">{{ $quote->convertedSale->number }}</a>
            </div>
        @endif
    </div>
    @if ($quote->notes)
        <p class="text-xs text-ink-soft italic border-t border-line pt-2">{{ $quote->notes }}</p>
    @endif
</div>

{{-- Lignes --}}
<div class="rounded-2xl border border-line bg-white overflow-hidden">
    <p class="px-4 py-3 text-xs font-extrabold text-ink-soft uppercase tracking-widest border-b border-line">Articles</p>
    <div class="divide-y divide-line">
        @foreach ($quote->quoteLines as $line)
            <div class="flex items-center justify-between px-4 py-3 text-sm">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-ink">{{ $line->product->name }}</p>
                    <p class="text-xs text-ink-soft">× {{ $line->quantity }} · <x-money :amount="$line->unit_price" /> / u</p>
                </div>
                <p class="font-extrabold text-ink shrink-0"><x-money :amount="$line->line_total" /></p>
            </div>
        @endforeach
    </div>

    {{-- Totaux --}}
    <div class="px-4 py-3 bg-cream/50 border-t border-line space-y-1">
        @if ($quote->discount_amount > 0)
            <div class="flex justify-between text-xs text-ink-soft">
                <span>Sous-total</span>
                <span><x-money :amount="$quote->total_amount" /></span>
            </div>
            <div class="flex justify-between text-sm font-bold text-danger">
                <span>
                    Remise
                    @if ($quote->discount_percentage > 0) ({{ $quote->discount_percentage }} %) @endif
                </span>
                <span>− <x-money :amount="$quote->discount_amount" /></span>
            </div>
        @endif
        <div class="flex justify-between text-base font-extrabold text-ink">
            <span>Total devis</span>
            <span><x-money :amount="$quote->netTotal()" /></span>
        </div>
    </div>
</div>

{{-- Actions --}}
@if (! $quote->status->isTerminal())
    <div class="rounded-2xl border border-line bg-white px-4 py-3 space-y-2">
        <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Actions</p>

        <div class="flex flex-wrap gap-2">
            @if ($quote->status === \App\Enums\QuoteStatus::DRAFT || $quote->status === \App\Enums\QuoteStatus::ACCEPTED)
                <button type="button" wire:click="requestConvert"
                        class="rounded-xl bg-brand text-white text-sm font-bold px-4 py-2 hover:brightness-90 transition">
                    Convertir en vente
                </button>
            @endif

            @if ($quote->status === \App\Enums\QuoteStatus::DRAFT)
                <button type="button" wire:click="markSent"
                        class="rounded-xl bg-blue-100 text-blue-700 text-sm font-bold px-4 py-2 hover:bg-blue-200 transition">
                    Marquer comme envoyé
                </button>
            @endif

            @if ($quote->status === \App\Enums\QuoteStatus::DRAFT || $quote->status === \App\Enums\QuoteStatus::SENT)
                <button type="button" wire:click="markAccepted"
                        class="rounded-xl bg-green-100 text-green-700 text-sm font-bold px-4 py-2 hover:bg-green-200 transition">
                    Marquer comme accepté
                </button>
            @endif

            @if ($quote->status !== \App\Enums\QuoteStatus::REFUSED)
                <button type="button" wire:click="markRefused"
                        class="rounded-xl bg-red-50 text-red-600 text-sm font-bold px-4 py-2 hover:bg-red-100 transition">
                    Refusé
                </button>
            @endif
        </div>
    </div>
@endif
