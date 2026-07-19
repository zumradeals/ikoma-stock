<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="quotes" />
    <div class="flex-1 overflow-y-auto">

        {{-- Header sticky --}}
        <div class="sticky top-0 z-10 bg-white border-b border-line px-5 py-3 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-base font-extrabold text-ink">Devis</h1>
                <p class="text-xs text-ink-soft">{{ $quotes->total() }} devis</p>
            </div>
            <a href="{{ route('quotes.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 hover:brightness-90 active:brightness-75 transition">
                <span class="text-base font-bold leading-none">+</span> Nouveau devis
            </a>
        </div>

        <div class="p-4 space-y-3">

            {{-- Filtres --}}
            <div class="flex items-center gap-2">
                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="N° devis ou client…"
                       class="flex-1 rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                <select wire:model.live="status"
                        class="rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                    <option value="">Tous les statuts</option>
                    <option value="DRAFT">Brouillon</option>
                    <option value="SENT">Envoyé</option>
                    <option value="ACCEPTED">Accepté</option>
                    <option value="REFUSED">Refusé</option>
                    <option value="EXPIRED">Expiré</option>
                    <option value="CONVERTED">Converti</option>
                </select>
            </div>

            @if ($quotes->isEmpty())
                <div class="rounded-2xl border border-line bg-white px-4 py-12 text-center">
                    <p class="text-2xl mb-2">📄</p>
                    <p class="text-sm font-bold text-ink">Aucun devis pour le moment.</p>
                </div>
            @else
                {{-- Grille desktop 3 colonnes --}}
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($quotes as $quote)
                        @php $net = $quote->netTotal(); @endphp
                        <a href="{{ route('quotes.show', $quote) }}" wire:navigate
                           class="flex flex-col rounded-2xl border border-line bg-white px-4 py-4 gap-2 hover:border-brand/30 transition">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-bold text-ink truncate flex-1">
                                    {{ $quote->customer?->name ?? 'Client de passage' }}
                                </p>
                                <span class="shrink-0 text-[11px] font-bold rounded-full px-2 py-0.5 {{ $quote->status->badgeClass() }}">
                                    {{ $quote->status->label() }}
                                </span>
                            </div>
                            <p class="text-xs text-ink-soft">{{ $quote->number }}</p>
                            @if ($quote->valid_until)
                                <p class="text-xs text-ink-soft">
                                    Valide jusqu'au {{ $quote->valid_until->format('d/m/Y') }}
                                </p>
                            @endif
                            <div class="mt-auto pt-2 flex items-end justify-between gap-2">
                                <p class="text-lg font-extrabold text-ink"><x-money :amount="$net" /></p>
                                <p class="text-[10px] text-ink-soft/60">{{ \App\Support\HumanDate::format($quote->created_at) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4">{{ $quotes->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Mobile --}}
<div class="lg:hidden">
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-extrabold text-ink">Devis</h1>
            <p class="text-xs text-ink-soft">{{ $quotes->total() }} devis</p>
        </div>
        <a href="{{ route('quotes.create') }}" wire:navigate
           class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 hover:brightness-90 transition">
            <span class="text-base font-bold leading-none">+</span> Nouveau
        </a>
    </div>

    <div class="p-4 space-y-3">
        <div class="flex items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher…"
                   class="flex-1 rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            <select wire:model.live="status"
                    class="rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                <option value="">Tous</option>
                <option value="DRAFT">Brouillon</option>
                <option value="SENT">Envoyé</option>
                <option value="ACCEPTED">Accepté</option>
                <option value="REFUSED">Refusé</option>
                <option value="EXPIRED">Expiré</option>
                <option value="CONVERTED">Converti</option>
            </select>
        </div>

        @if ($quotes->isEmpty())
            <div class="rounded-2xl border border-line bg-white px-4 py-12 text-center">
                <p class="text-2xl mb-2">📄</p>
                <p class="text-sm font-bold text-ink">Aucun devis pour le moment.</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($quotes as $quote)
                    @php $net = $quote->netTotal(); @endphp
                    <a href="{{ route('quotes.show', $quote) }}" wire:navigate
                       class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-3 gap-3 hover:border-brand/30 transition">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-ink truncate">
                                {{ $quote->customer?->name ?? 'Client de passage' }}
                            </p>
                            <p class="text-xs text-ink-soft">{{ $quote->number }}</p>
                            @if ($quote->valid_until)
                                <p class="text-[10px] text-ink-soft/60">jusqu'au {{ $quote->valid_until->format('d/m/Y') }}</p>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-extrabold text-ink"><x-money :amount="$net" /></p>
                            <span class="text-[11px] font-bold rounded-full px-2 py-0.5 {{ $quote->status->badgeClass() }}">
                                {{ $quote->status->label() }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-4">{{ $quotes->links() }}</div>
        @endif
    </div>
</div>
</div>
