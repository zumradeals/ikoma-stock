{{-- En-tête --}}
<div class="flex items-center justify-between gap-3">
    <h1 class="text-base font-extrabold text-ink">Sociétés clientes</h1>
    <div class="flex items-center gap-2">
        <a href="{{ route('platform.settings') }}" wire:navigate
           class="rounded-xl border border-line bg-white px-3 py-2 text-xs font-bold text-ink-soft hover:text-ink transition">
            Paramètres
        </a>
        <button type="button" wire:click="openCreateForm"
                class="rounded-xl bg-brand text-white px-3 py-2 text-xs font-extrabold hover:brightness-90 transition">
            + Nouvelle société
        </button>
    </div>
</div>

{{-- Créd compte créé --}}
@if ($createdPassword)
    <div class="rounded-2xl border border-success/30 bg-success/5 px-4 py-3 space-y-1.5 text-sm text-success">
        <p class="font-extrabold">Société créée.</p>
        <p class="text-ink-soft text-xs">Identifiants du compte administrateur (à noter maintenant, ils ne seront plus affichés) :</p>
        <p class="text-xs">Email : <span class="font-mono font-bold text-ink">{{ $createdAdminEmail }}</span></p>
        <p class="text-xs">Mot de passe : <span class="font-mono font-bold text-ink">{{ $createdPassword }}</span></p>
    </div>
@endif

{{-- Formulaire création / édition --}}
@if ($showCreateForm)
    <div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-3">
        <p class="text-sm font-extrabold text-ink">{{ $editingId ? 'Modifier la société' : 'Nouvelle société' }}</p>

        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Nom de la société</label>
            <input type="text" wire:model="name"
                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            @error('name') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-ink-soft mb-1">Adresse</label>
                <input type="text" wire:model="address"
                       class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-soft mb-1">Téléphone</label>
                <input type="text" wire:model="phone"
                       class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label class="block text-xs font-bold text-ink-soft mb-1">Email</label>
                <input type="email" wire:model="email"
                       class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                @error('email') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-soft mb-1">Devise</label>
                <input type="text" wire:model="currency" maxlength="3"
                       class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink uppercase focus:outline-none focus:ring-2 focus:ring-brand/40">
                @error('currency') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-ink-soft mb-1">Préfixe factures</label>
                <input type="text" wire:model="invoicePrefix" maxlength="10"
                       class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink uppercase focus:outline-none focus:ring-2 focus:ring-brand/40">
                @error('invoicePrefix') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-soft mb-1">Plan d'abonnement</label>
                <select wire:model="subscriptionPlan"
                        class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                    @foreach (\App\Enums\SubscriptionPlan::cases() as $plan)
                        <option value="{{ $plan->value }}">{{ $plan->label() }}</option>
                    @endforeach
                </select>
                @error('subscriptionPlan') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        @unless ($editingId)
            <div class="border-t border-line pt-3 space-y-3">
                <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Premier compte administrateur</p>
                <div>
                    <label class="block text-xs font-bold text-ink-soft mb-1">Nom</label>
                    <input type="text" wire:model="adminName"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                    @error('adminName') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft mb-1">Email</label>
                    <input type="email" wire:model="adminEmail"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                    @error('adminEmail') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        @endunless

        <div class="flex gap-2 pt-1">
            <button type="button" wire:click="cancelCreate"
                    class="flex-1 rounded-xl border border-line text-ink-soft text-sm font-bold py-2.5 hover:bg-cream transition">
                Annuler
            </button>
            <button type="button" wire:click="{{ $editingId ? 'updateCompany' : 'createCompany' }}"
                    wire:loading.attr="disabled"
                    class="flex-1 rounded-xl bg-brand text-white text-sm font-extrabold py-2.5 hover:brightness-90 transition disabled:opacity-60">
                {{ $editingId ? 'Enregistrer' : 'Créer' }}
            </button>
        </div>
    </div>
@endif

{{-- Recherche + filtre --}}
<div class="flex gap-2">
    <div class="flex-1 relative">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Rechercher une société…"
               class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40 pl-9">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-soft/60 text-sm">🔍</span>
    </div>
    <select wire:model.live="statusFilter"
            class="rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
        <option value="">Tous</option>
        <option value="active">Actives</option>
        <option value="suspended">Suspendues</option>
    </select>
</div>

{{-- Liste des sociétés --}}
<div class="rounded-2xl border border-line bg-white overflow-hidden">
    @forelse ($this->companies as $company)
        @php $adminUser = $this->adminUserFor($company); @endphp
        <div class="flex items-center gap-3 px-4 py-3 border-b border-line last:border-0"
             wire:key="company-{{ $company->id }}">

            {{-- Avatar --}}
            <div class="h-9 w-9 rounded-full bg-brand/10 flex items-center justify-center shrink-0">
                <span class="text-sm font-extrabold text-brand">{{ strtoupper(substr($company->name, 0, 1)) }}</span>
            </div>

            {{-- Infos --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-bold text-ink truncate">{{ $company->name }}</p>
                    {{-- Statut --}}
                    @if ($company->is_active)
                        <span class="text-[11px] font-bold text-success bg-success/10 rounded-full px-2 py-0.5">Active</span>
                    @else
                        <span class="text-[11px] font-bold text-danger bg-red-50 rounded-full px-2 py-0.5">Suspendue</span>
                    @endif
                    {{-- Plan --}}
                    @if ($company->subscription_plan)
                        <span class="text-[11px] font-bold rounded-full px-2 py-0.5 {{ $company->subscription_plan->badgeClass() }}">
                            {{ $company->subscription_plan->label() }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-ink-soft/70 truncate">{{ $company->email }}</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" wire:click="openEditForm({{ $company->id }})"
                        class="rounded-xl border border-line px-2.5 py-1.5 text-xs font-bold text-ink-soft hover:text-ink hover:bg-cream transition">
                    Éditer
                </button>
                <button type="button" wire:click="requestToggle({{ $company->id }})"
                        class="rounded-xl border border-line px-2.5 py-1.5 text-xs font-bold text-ink-soft hover:text-ink hover:bg-cream transition">
                    {{ $company->is_active ? 'Suspendre' : 'Réactiver' }}
                </button>
                @if ($adminUser)
                    <form method="POST" action="{{ route('support.start', $adminUser) }}">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-gold/10 border border-gold/30 px-2.5 py-1.5 text-xs font-extrabold text-gold hover:bg-gold/20 transition"
                                title="Se connecter en support comme {{ $adminUser->name }}">
                            Support
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="px-4 py-8 text-sm text-ink-soft text-center">Aucune société trouvée.</p>
    @endforelse
</div>
