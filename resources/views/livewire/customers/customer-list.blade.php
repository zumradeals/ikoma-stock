<div class="lg:flex lg:h-screen lg:overflow-hidden">

{{-- ════ Sidebar desktop ════ --}}
<div class="hidden lg:flex">
    <x-ikoma.desktop-sidebar active="clients" />
</div>

{{-- ════ Contenu principal ════ --}}
<div class="flex-1 lg:overflow-y-auto">

    {{-- ── En-tête : recherche + bouton Ajouter ── --}}
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3 flex items-center gap-3">
        {{-- Barre de recherche avec icône --}}
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-soft pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
            </svg>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par nom ou téléphone…"
                class="w-full rounded-xl border border-line bg-cream pl-9 pr-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition"
            >
        </div>
        {{-- Bouton Ajouter ── déclenche le formulaire inline --}}
        <button
            type="button"
            wire:click="openCreateForm"
            class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 shadow-brand-glow hover:brightness-90 active:brightness-75 transition"
        >
            <span class="text-base leading-none font-bold">+</span> Ajouter
        </button>
    </div>

    <div class="p-4 space-y-4">

        {{-- Compteur --}}
        @if (! $showCreateForm)
            <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">
                {{ $customers->total() }} {{ $customers->total() > 1 ? 'clients' : 'client' }}
            </p>
        @endif

        {{-- ── Formulaire création / édition (inline) ── --}}
        @if ($showCreateForm)
            <div class="rounded-2xl border border-line bg-white p-4 space-y-3 shadow-sm">
                <p class="text-sm font-extrabold text-ink">{{ $editingId ? 'Modifier le client' : 'Nouveau client' }}</p>

                <form wire:submit="saveCustomer" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1">Nom *</label>
                        <input wire:model="name" type="text" placeholder="Awa Koné"
                            class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink focus:border-brand focus:ring-0 focus:outline-none transition">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1">Téléphone</label>
                            <input wire:model="phone" type="text" placeholder="+225…"
                                class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink focus:border-brand focus:ring-0 focus:outline-none transition">
                            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1">Quartier / Ville</label>
                            <input wire:model="neighborhoodCity" type="text" placeholder="Cocody"
                                class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink focus:border-brand focus:ring-0 focus:outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1">Adresse</label>
                        <input wire:model="address" type="text"
                            class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink focus:border-brand focus:ring-0 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1">Plafond de crédit</label>
                        <input wire:model="creditLimit" type="number" step="1" min="0"
                            class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink focus:border-brand focus:ring-0 focus:outline-none transition">
                        <x-input-error :messages="$errors->get('creditLimit')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2"
                            class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink focus:border-brand focus:ring-0 focus:outline-none transition resize-none"></textarea>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="$set('showCreateForm', false)"
                            class="flex-1 rounded-xl border border-line text-sm font-bold text-ink-soft px-4 py-2.5 hover:bg-cream transition">
                            Annuler
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 shadow-brand-glow hover:brightness-90 transition">
                            {{ $editingId ? 'Enregistrer' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ════ Liste mobile (< lg) ════ --}}
        <div class="lg:hidden space-y-2">
            @forelse ($customers as $customer)
                @php
                    $initials = collect(explode(' ', $customer->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $hasDebt  = ($customer->open_debt ?? 0) > 0;
                    $hasLimit = $customer->credit_limit !== null;
                @endphp
                <div wire:key="mob-{{ $customer->id }}" class="rounded-2xl border border-line bg-white px-4 py-3 space-y-2.5">
                    {{-- Ligne principale --}}
                    <div class="flex items-center gap-3">
                        {{-- Avatar initiales --}}
                        <div class="h-10 w-10 rounded-full bg-brand-wash text-brand text-sm font-extrabold flex items-center justify-center shrink-0">
                            {{ $initials }}
                        </div>
                        <a href="{{ route('customers.show', $customer) }}" wire:navigate class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-ink truncate">
                                {{ $customer->name }}
                                @unless ($customer->is_active)
                                    <span class="ml-1 inline-flex items-center rounded-pill px-2 py-0.5 text-[10px] font-extrabold bg-danger-wash text-danger">Inactif</span>
                                @endunless
                            </p>
                            @if ($customer->phone)
                                <p class="text-xs text-ink-soft">{{ $customer->phone }}</p>
                            @endif
                            @if ($customer->neighborhood_city)
                                <p class="text-xs text-ink-soft">{{ $customer->neighborhood_city }}</p>
                            @endif
                        </a>
                    </div>

                    {{-- Badges dette + plafond --}}
                    @if ($hasDebt || $hasLimit)
                        <div class="flex flex-wrap gap-2">
                            @if ($hasDebt)
                                <span class="inline-flex items-center gap-1 rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-gold-wash text-gold">
                                    💰 Dette · <x-money :amount="$customer->open_debt" />
                                </span>
                            @endif
                            @if ($hasLimit)
                                <span class="inline-flex items-center gap-1 rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-info-wash text-info">
                                    Plafond · <x-money :amount="$customer->credit_limit" />
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-0.5">
                        <button type="button" wire:click="openEditForm({{ $customer->id }})"
                            class="flex-1 rounded-xl border border-line text-xs font-bold text-ink-soft px-3 py-1.5 text-center hover:bg-cream transition">
                            Éditer
                        </button>
                        <button type="button" wire:click="requestToggle({{ $customer->id }})"
                            class="flex-1 rounded-xl border border-line text-xs font-bold px-3 py-1.5 text-center transition
                                {{ $customer->is_active ? 'text-ink-soft hover:bg-cream' : 'text-brand hover:bg-brand-wash' }}">
                            {{ $customer->is_active ? 'Désactiver' : 'Réactiver' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-line bg-white px-4 py-10 text-center">
                    <p class="text-sm text-ink-soft">{{ $search ? 'Aucun client trouvé pour « '.$search.' ».' : 'Aucun client pour le moment.' }}</p>
                </div>
            @endforelse

            {{ $customers->links() }}
        </div>

        {{-- ════ Tableau desktop (lg+) ════ --}}
        <div class="hidden lg:block overflow-x-auto rounded-2xl border border-line bg-white">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-line text-left">
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Client</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Téléphone</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Quartier / Ville</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Plafond crédit</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Dette ouverte</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($customers as $customer)
                        @php
                            $initials = collect(explode(' ', $customer->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                            $hasDebt  = ($customer->open_debt ?? 0) > 0;
                        @endphp
                        <tr wire:key="desk-{{ $customer->id }}" class="hover:bg-cream/40 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('customers.show', $customer) }}" wire:navigate class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-brand-wash text-brand text-xs font-extrabold flex items-center justify-center shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-ink">{{ $customer->name }}</p>
                                        @unless ($customer->is_active)
                                            <span class="inline-flex items-center rounded-pill px-2 py-0.5 text-[10px] font-extrabold bg-danger-wash text-danger">Inactif</span>
                                        @endunless
                                    </div>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $customer->neighborhood_city ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($customer->credit_limit !== null)
                                    <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-info-wash text-info">
                                        <x-money :amount="$customer->credit_limit" />
                                    </span>
                                @else
                                    <span class="text-ink-soft">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($hasDebt)
                                    <span class="inline-flex items-center gap-1 rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-gold-wash text-gold">
                                        💰 <x-money :amount="$customer->open_debt" />
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-[11px] font-extrabold bg-success-wash text-success">OK</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="openEditForm({{ $customer->id }})"
                                        class="rounded-lg border border-line text-xs font-bold text-ink-soft px-3 py-1.5 hover:bg-cream transition">
                                        Éditer
                                    </button>
                                    <button type="button" wire:click="requestToggle({{ $customer->id }})"
                                        class="rounded-lg border border-line text-xs font-bold px-3 py-1.5 transition
                                            {{ $customer->is_active ? 'text-ink-soft hover:bg-cream' : 'text-brand hover:bg-brand-wash' }}">
                                        {{ $customer->is_active ? 'Désactiver' : 'Réactiver' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-ink-soft">
                                {{ $search ? 'Aucun client trouvé pour « '.$search.' ».' : 'Aucun client pour le moment.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($customers->hasPages())
                <div class="px-4 py-3 border-t border-line">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>

    </div>{{-- /p-4 --}}
</div>{{-- /flex-1 --}}

</div>{{-- /lg:flex --}}
