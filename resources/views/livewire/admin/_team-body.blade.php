{{-- PIN généré — affiché une seule fois --}}
@if ($generatedPin)
    <div class="rounded-2xl border-2 border-brand/30 bg-brand/5 px-4 py-4 space-y-2">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-sm font-extrabold text-ink">Compte créé — {{ $createdMemberName }}</p>
                <p class="text-xs text-ink-soft mt-0.5">Transmettez ce PIN une seule fois. Il ne sera plus affiché.</p>
            </div>
            <button type="button" wire:click="dismissPin"
                    class="text-ink-soft hover:text-ink transition text-lg leading-none shrink-0">✕</button>
        </div>
        <div class="flex items-center gap-3">
            <p class="text-3xl font-extrabold tracking-[.25em] text-brand">{{ $generatedPin }}</p>
            <span class="text-xs text-ink-soft">PIN temporaire</span>
        </div>
    </div>
@endif

{{-- Formulaire ajout --}}
@if ($showForm)
    <div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-3">
        <p class="text-sm font-extrabold text-ink">Nouveau membre</p>

        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Nom complet</label>
            <input type="text" wire:model="name" placeholder="Prénom Nom"
                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            @error('name') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Téléphone</label>
            <div class="flex gap-2">
                <span class="inline-flex items-center rounded-xl border border-line bg-cream px-3 text-sm text-ink-soft font-bold">+225</span>
                <input type="tel" wire:model="phone" placeholder="07 00 00 00 00"
                       class="flex-1 rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            </div>
            @error('phone') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Rôle</label>
            <select wire:model="role"
                    class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                <option value="">— Choisir —</option>
                <option value="OUTLET_MANAGER">Gérant de point de vente</option>
                <option value="SELLER">Vendeur</option>
                <option value="WAREHOUSE_KEEPER">Magasinier</option>
            </select>
            @error('role') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-2 pt-1">
            <button type="button" wire:click="cancelForm"
                    class="flex-1 rounded-xl border border-line text-ink-soft text-sm font-bold py-2.5 hover:bg-cream transition">
                Annuler
            </button>
            <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="flex-1 rounded-xl bg-brand text-white text-sm font-bold py-2.5 hover:brightness-90 active:brightness-75 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Créer le compte</span>
                <span wire:loading wire:target="save">Création…</span>
            </button>
        </div>
    </div>
@endif

{{-- Liste des membres --}}
<div class="rounded-2xl border border-line bg-white overflow-hidden">
    @forelse ($this->team as $member)
        @php $isAdmin = $member->role === \App\Enums\UserRole::ADMIN_COMPANY; @endphp
        <div class="flex items-center gap-3 px-4 py-3 border-b border-line last:border-0"
             wire:key="member-{{ $member->id }}">

            {{-- Avatar --}}
            <div class="h-9 w-9 rounded-full bg-brand/10 flex items-center justify-center shrink-0">
                <span class="text-sm font-extrabold text-brand">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
            </div>

            {{-- Infos --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-bold text-ink">{{ $member->name }}</p>
                    @if (! $member->is_active)
                        <span class="text-[11px] font-bold text-danger bg-red-50 rounded-full px-2 py-0.5">inactif</span>
                    @endif
                </div>
                <p class="text-xs text-ink-soft">{{ $member->phone }}</p>
                <p class="text-[11px] text-ink-soft/60 mt-0.5">{{ $member->role->label() }}</p>
            </div>

            {{-- Toggle actif/inactif — ADMIN affiché en lecture seule --}}
            @if ($isAdmin)
                <span class="text-xs text-ink-soft/40 shrink-0">Admin</span>
            @else
                <button
                    type="button"
                    wire:click="toggleActive({{ $member->id }})"
                    wire:loading.attr="disabled"
                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none
                           {{ $member->is_active ? 'bg-brand' : 'bg-gray-200' }}"
                    role="switch"
                    aria-checked="{{ $member->is_active ? 'true' : 'false' }}"
                    title="{{ $member->is_active ? 'Désactiver' : 'Réactiver' }}"
                >
                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200
                                 {{ $member->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                </button>
            @endif
        </div>
    @empty
        <p class="px-4 py-8 text-sm text-ink-soft text-center">Aucun membre dans l'équipe.</p>
    @endforelse
</div>
