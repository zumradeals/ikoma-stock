<div class="lg:flex lg:h-screen lg:overflow-hidden">

{{-- ════ Sidebar desktop ════ --}}
<div class="hidden lg:flex">
    <x-ikoma.desktop-sidebar active="livraisons" />
</div>

{{-- ════ Contenu principal ════ --}}
<div class="flex-1 lg:overflow-y-auto">

    {{-- ── En-tête ── --}}
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3">
        <h1 class="text-base font-extrabold text-ink">Livraisons</h1>
    </div>

    <div class="p-4 space-y-4">

        {{-- ── 2 cartes résumé ── --}}
        <div class="grid grid-cols-2 gap-3">
            {{-- En retard --}}
            <button
                type="button"
                wire:click="$set('filter', 'overdue')"
                class="rounded-2xl border px-4 py-3 text-left transition
                    {{ $filter === 'overdue'
                        ? 'bg-danger border-danger/30 shadow-sm'
                        : 'bg-danger-wash border-danger/20 hover:border-danger/40' }}"
            >
                <p class="text-2xl font-extrabold {{ $filter === 'overdue' ? 'text-white' : 'text-danger' }}">
                    {{ $this->overdueCount }}
                </p>
                <p class="text-xs font-bold mt-0.5 {{ $filter === 'overdue' ? 'text-white/80' : 'text-danger/80' }}">
                    En retard
                </p>
            </button>

            {{-- Aujourd'hui --}}
            <button
                type="button"
                wire:click="$set('filter', 'today')"
                class="rounded-2xl border px-4 py-3 text-left transition
                    {{ $filter === 'today'
                        ? 'bg-gold border-gold/30 shadow-sm'
                        : 'bg-gold-wash border-gold/20 hover:border-gold/40' }}"
            >
                <p class="text-2xl font-extrabold {{ $filter === 'today' ? 'text-white' : 'text-gold' }}">
                    {{ $this->todayCount }}
                </p>
                <p class="text-xs font-bold mt-0.5 {{ $filter === 'today' ? 'text-white/80' : 'text-gold/80' }}">
                    Aujourd'hui
                </p>
            </button>
        </div>

        {{-- ── Filtres pilules ── --}}
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
            @foreach ([
                'all'     => 'Toutes',
                'today'   => "Aujourd'hui",
                'overdue' => 'En retard',
                'week'    => 'Cette semaine',
            ] as $key => $label)
                <button
                    type="button"
                    wire:click="$set('filter', '{{ $key }}')"
                    class="shrink-0 rounded-pill px-3.5 py-1.5 text-xs font-extrabold transition
                        {{ $filter === $key
                            ? 'bg-brand text-white shadow-brand-glow'
                            : 'bg-white border border-line text-ink-soft hover:border-brand/40' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- ════ Liste mobile (< lg) ════ --}}
        <div class="lg:hidden space-y-2">
            @forelse ($this->invoices as $invoice)
                @php
                    $linesCount = $invoice->sale->saleLines->count();
                    $outletName = $invoice->sale->outlet?->name;
                @endphp
                <a
                    href="{{ route('deliveries.show', $invoice) }}"
                    wire:navigate
                    wire:key="mob-{{ $invoice->id }}"
                    class="block rounded-2xl border border-line bg-white px-4 py-3 space-y-2 hover:border-brand/30 transition"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink truncate">
                                {{ $invoice->sale->customer?->name ?? 'Client de passage' }}
                            </p>
                            <p class="text-xs text-ink-soft">{{ $invoice->number }}</p>
                        </div>
                        <x-ikoma.status-badge :status="$this->status($invoice)" />
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-soft">
                        @if ($invoice->due_date)
                            <span>📅 {{ $invoice->due_date->translatedFormat('d M') }}</span>
                        @endif
                        @if ($linesCount > 0)
                            <span>📦 {{ $linesCount }} {{ $linesCount > 1 ? 'articles' : 'article' }}</span>
                        @endif
                        <span><x-money :amount="$invoice->total_amount" /></span>
                        @if ($outletName)
                            <span>📍 {{ $outletName }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-line bg-white px-4 py-10 text-center">
                    <p class="text-2xl mb-2">✅</p>
                    <p class="text-sm text-ink-soft">Aucune livraison dans ce filtre.</p>
                </div>
            @endforelse
        </div>

        {{-- ════ Tableau desktop (lg+) ════ --}}
        <div class="hidden lg:block overflow-x-auto rounded-2xl border border-line bg-white">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-line text-left">
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Client</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Facture</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Échéance</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Articles</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Montant</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Emplacement</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($this->invoices as $invoice)
                        @php
                            $linesCount = $invoice->sale->saleLines->count();
                            $outletName = $invoice->sale->outlet?->name;
                        @endphp
                        <tr
                            wire:key="desk-{{ $invoice->id }}"
                            class="hover:bg-cream/40 transition cursor-pointer"
                            onclick="window.location='{{ route('deliveries.show', $invoice) }}'"
                        >
                            <td class="px-4 py-3 font-semibold text-ink">
                                {{ $invoice->sale->customer?->name ?? 'Client de passage' }}
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $invoice->number }}</td>
                            <td class="px-4 py-3 text-ink-soft">
                                {{ $invoice->due_date?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-ink-soft">
                                {{ $linesCount > 0 ? $linesCount . ' ' . ($linesCount > 1 ? 'articles' : 'article') : '—' }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-ink">
                                <x-money :amount="$invoice->total_amount" />
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $outletName ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-ikoma.status-badge :status="$this->status($invoice)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center">
                                <p class="text-2xl mb-2">✅</p>
                                <p class="text-sm text-ink-soft">Aucune livraison dans ce filtre.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>{{-- /p-4 --}}
</div>{{-- /flex-1 --}}

</div>{{-- /lg:flex --}}
