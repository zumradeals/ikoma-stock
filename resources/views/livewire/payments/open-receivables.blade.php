<div class="lg:flex lg:h-screen lg:overflow-hidden">

{{-- ════ Sidebar desktop ════ --}}
<div class="hidden lg:flex">
    <x-ikoma.desktop-sidebar active="payments" />
</div>

{{-- ════ Contenu principal ════ --}}
<div class="flex-1 lg:overflow-y-auto">

    {{-- ── En-tête : titre + recherche ── --}}
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3 space-y-2.5">
        <h1 class="text-base font-extrabold text-ink">Paiements à encaisser</h1>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-soft pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
            </svg>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un client…"
                class="w-full rounded-xl border border-line bg-cream pl-9 pr-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition"
            >
        </div>
    </div>

    <div class="p-4 space-y-4">

        {{-- ── Carte résumé totale ── --}}
        @if ($this->receivables->isNotEmpty())
            <div class="rounded-2xl bg-gold-wash border border-gold/20 px-5 py-4 space-y-1">
                <p class="text-xs font-extrabold uppercase tracking-widest text-gold/80">Reste à encaisser</p>
                <p class="text-3xl font-extrabold text-gold leading-tight">
                    <x-money :amount="$this->totalDue" />
                </p>
                <p class="text-xs text-gold/70 font-bold">
                    {{ $this->receivables->count() }} {{ $this->receivables->count() > 1 ? 'factures' : 'facture' }}
                    · {{ $this->distinctCustomersCount }} {{ $this->distinctCustomersCount > 1 ? 'clients' : 'client' }}
                </p>
            </div>
        @endif

        {{-- ════ État vide ════ --}}
        @if ($this->receivables->isEmpty())
            <div class="rounded-2xl border border-line bg-white px-4 py-12 text-center">
                <p class="text-2xl mb-2">🎉</p>
                <p class="text-sm font-bold text-ink">Aucune créance ouverte</p>
                <p class="text-xs text-ink-soft mt-1">Tous les clients sont à jour.</p>
            </div>

        {{-- ════ Liste mobile (< lg) ════ --}}
        @else
            <div class="lg:hidden space-y-3">
                @foreach ($this->receivables as $receivable)
                    @php
                        $sale = $receivable->invoice?->sale;
                        $days = $receivable->days_overdue ?? 0;
                        if ($days >= 7) {
                            $urgencyClass = 'bg-danger-wash text-danger';
                            $urgencyIcon  = '🔴';
                            $urgencyLabel = $days . 'j de retard';
                        } elseif ($days >= 2) {
                            $urgencyClass = 'bg-gold-wash text-gold';
                            $urgencyIcon  = '🟡';
                            $urgencyLabel = $days . 'j de retard';
                        } else {
                            $urgencyClass = 'bg-info-wash text-info';
                            $urgencyIcon  = '🔵';
                            $urgencyLabel = $receivable->status->label();
                        }
                        $hasPartial = $receivable->total_paid > 0;
                    @endphp
                    @if ($sale)
                        <div wire:key="mob-{{ $receivable->id }}" class="rounded-2xl border border-line bg-white px-4 py-4 space-y-3">
                            {{-- Ligne 1 : client + badge urgence --}}
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-ink truncate">
                                        {{ $receivable->customer?->name ?? 'Client de passage' }}
                                    </p>
                                    <p class="text-xs text-ink-soft">
                                        {{ $receivable->invoice?->number }}
                                        · {{ \App\Support\HumanDate::format($receivable->invoice->created_at) }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-pill px-2.5 py-1 text-[11px] font-extrabold shrink-0 {{ $urgencyClass }}">
                                    {{ $urgencyIcon }} {{ $urgencyLabel }}
                                </span>
                            </div>

                            {{-- Ligne 2 : montant + bouton tel --}}
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-xl font-extrabold text-ink leading-tight">
                                        <x-money :amount="$receivable->balance_due" />
                                    </p>
                                    @if ($hasPartial)
                                        <p class="text-xs text-ink-soft">
                                            Déjà versé <x-money :amount="$receivable->total_paid" /> sur <x-money :amount="$receivable->initial_amount" />
                                        </p>
                                    @endif
                                </div>
                                @if ($receivable->customer?->phone)
                                    <a
                                        href="tel:{{ $receivable->customer->phone }}"
                                        class="h-9 w-9 rounded-full bg-success-wash text-success flex items-center justify-center shrink-0 text-base"
                                        title="Appeler {{ $receivable->customer->name }}"
                                    >
                                        📞
                                    </a>
                                @endif
                            </div>

                            {{-- Bouton Encaisser --}}
                            <a
                                href="{{ route('sales.payment', $sale) }}"
                                wire:navigate
                                class="block w-full text-center rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 shadow-brand-glow hover:brightness-90 active:brightness-75 transition"
                            >
                                Encaisser <x-money :amount="$receivable->balance_due" />
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- ════ Tableau desktop (lg+) ════ --}}
            <div class="hidden lg:block overflow-x-auto rounded-2xl border border-line bg-white">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-line text-left">
                            <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Client</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Facture</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Urgence</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Montant dû</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Versements</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($this->receivables as $receivable)
                            @php
                                $sale = $receivable->invoice?->sale;
                                $days = $receivable->days_overdue ?? 0;
                                if ($days >= 7) {
                                    $urgencyClass = 'bg-danger-wash text-danger';
                                    $urgencyIcon  = '🔴';
                                    $urgencyLabel = $days . 'j de retard';
                                } elseif ($days >= 2) {
                                    $urgencyClass = 'bg-gold-wash text-gold';
                                    $urgencyIcon  = '🟡';
                                    $urgencyLabel = $days . 'j de retard';
                                } else {
                                    $urgencyClass = 'bg-info-wash text-info';
                                    $urgencyIcon  = '🔵';
                                    $urgencyLabel = $receivable->status->label();
                                }
                                $hasPartial = $receivable->total_paid > 0;
                            @endphp
                            @if ($sale)
                                <tr wire:key="desk-{{ $receivable->id }}" class="hover:bg-cream/40 transition">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-ink">{{ $receivable->customer?->name ?? 'Client de passage' }}</p>
                                        @if ($receivable->customer?->phone)
                                            <a href="tel:{{ $receivable->customer->phone }}" class="text-xs text-ink-soft hover:text-brand transition">
                                                {{ $receivable->customer->phone }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-ink-soft">
                                        <p>{{ $receivable->invoice?->number }}</p>
                                        <p class="text-xs">{{ \App\Support\HumanDate::format($receivable->invoice->created_at) }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-pill px-2.5 py-1 text-[11px] font-extrabold {{ $urgencyClass }}">
                                            {{ $urgencyIcon }} {{ $urgencyLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-base font-extrabold text-ink"><x-money :amount="$receivable->balance_due" /></p>
                                    </td>
                                    <td class="px-4 py-3 text-ink-soft text-xs">
                                        @if ($hasPartial)
                                            Versé <x-money :amount="$receivable->total_paid" /><br>
                                            sur <x-money :amount="$receivable->initial_amount" />
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a
                                            href="{{ route('sales.payment', $sale) }}"
                                            wire:navigate
                                            class="inline-flex items-center rounded-xl bg-brand text-white text-xs font-extrabold px-4 py-2 shadow-brand-glow hover:brightness-90 transition whitespace-nowrap"
                                        >
                                            Encaisser <span class="ml-1.5"><x-money :amount="$receivable->balance_due" /></span>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>{{-- /p-4 --}}
</div>{{-- /flex-1 --}}

</div>{{-- /lg:flex --}}
