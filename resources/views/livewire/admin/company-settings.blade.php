<div>

{{-- ════ DESKTOP ════ --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="manage" />

    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-2xl space-y-6">

            {{-- Header --}}
            <div class="flex items-center gap-3">
                @if ($this->company->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($this->company->logo_path) }}"
                         alt="" class="h-9 w-9 rounded-lg object-cover shrink-0">
                @endif
                <div>
                    <h1 class="text-xl font-extrabold text-ink">{{ $this->company->name }}</h1>
                    <p class="text-xs text-ink-soft">Paramètres de la société</p>
                </div>
            </div>

            @if ($this->canManage)
                {{-- Message de succès --}}
                @if ($profileSaved)
                    <div class="rounded-xl border border-success/20 bg-success-wash px-4 py-3 text-sm font-bold text-success flex items-center gap-2">
                        <span>✓</span> Enregistré avec succès.
                    </div>
                @endif

                {{-- Formulaire société --}}
                <div class="rounded-2xl border border-line bg-white">
                    <div class="px-5 py-4 border-b border-line">
                        <p class="text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Informations société</p>
                    </div>

                    <form wire:submit="saveCompanyProfile" class="px-5 py-5 space-y-4">
                        <div>
                            <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Nom de la société</label>
                            <input wire:model="companyName" type="text"
                                   class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                            @error('companyName') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Adresse</label>
                                <input wire:model="companyAddress" type="text"
                                       class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Téléphone</label>
                                <input wire:model="companyPhone" type="text"
                                       class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Email</label>
                            <input wire:model="companyEmail" type="email"
                                   class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                            @error('companyEmail') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Devise</label>
                                <input wire:model="companyCurrency" type="text" maxlength="3"
                                       class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink uppercase placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                                @error('companyCurrency') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Préfixe des factures</label>
                                <input wire:model="companyInvoicePrefix" type="text" maxlength="10"
                                       class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink uppercase placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                                @error('companyInvoicePrefix') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Pied de page (factures)</label>
                            <textarea wire:model="companyFooterText" rows="2"
                                      class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4 items-end">
                            <div>
                                <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Logo</label>
                                @if ($this->company->logo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($this->company->logo_path) }}"
                                         alt="" class="h-10 w-10 rounded-lg object-cover mb-2">
                                @endif
                                <input type="file" wire:model="companyLogo" accept="image/*"
                                       class="w-full text-sm text-ink-soft file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white">
                                @error('companyLogo') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Couleur d'accent</label>
                                <input type="color" wire:model="companyPrimaryColor"
                                       class="h-11 w-full rounded-xl border border-line cursor-pointer p-1">
                                @error('companyPrimaryColor') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand py-3 text-sm font-extrabold text-white shadow-brand-glow active:scale-95 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Enregistrer les paramètres
                        </button>
                    </form>
                </div>
            @endif

            {{-- Utilisateur créé --}}
            @if ($createdUserPassword)
                <div class="rounded-xl border border-success/20 bg-success-wash px-4 py-3 text-sm text-success space-y-1">
                    <p class="font-extrabold">Utilisateur créé — donne ces accès au vendeur :</p>
                    <p>Téléphone : <span class="font-mono font-bold">{{ $createdUserPhone }}</span></p>
                    <p>Code : <span class="font-mono font-bold">{{ $createdUserPassword }}</span></p>
                </div>
            @endif

            {{-- Utilisateurs --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold text-gray-900">Utilisateurs</h2>
                    @if ($this->canManage)
                        <button type="button" wire:click="openUserForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-2.5 py-1.5">+ Ajouter</button>
                    @endif
                </div>

                @if ($showUserForm)
                    <form wire:submit="saveUser" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 mb-2">
                        <div>
                            <x-input-label value="Nom" />
                            <x-text-input wire:model="userName" type="text" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('userName')" class="mt-1" />
                        </div>

                        <div x-data="{
                            dial: '+225',
                            countries: [
                                {code:'CI', dial:'+225', name:'Côte d\'Ivoire'},
                                {code:'SN', dial:'+221', name:'Sénégal'},
                                {code:'ML', dial:'+223', name:'Mali'},
                                {code:'BF', dial:'+226', name:'Burkina Faso'},
                                {code:'GN', dial:'+224', name:'Guinée'},
                                {code:'TG', dial:'+228', name:'Togo'},
                                {code:'BJ', dial:'+229', name:'Bénin'},
                                {code:'NE', dial:'+227', name:'Niger'},
                                {code:'GH', dial:'+233', name:'Ghana'},
                                {code:'NG', dial:'+234', name:'Nigeria'},
                                {code:'CM', dial:'+237', name:'Cameroun'},
                                {code:'FR', dial:'+33',  name:'France'},
                                {code:'BE', dial:'+32',  name:'Belgique'},
                            ],
                            local: '',
                            get full() { return this.dial + this.local.replace(/\s+/g,''); },
                            sync() { $wire.set('userPhone', this.full); },
                        }" x-init="
                            local = $wire.userPhone.replace(/^\+\d+/, '');
                            countries.forEach(c => { if ($wire.userPhone.startsWith(c.dial)) dial = c.dial; });
                        ">
                            <x-input-label value="Téléphone" />
                            <div class="flex mt-1 gap-2">
                                <select x-model="dial" @change="sync()" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm text-sm w-36 flex-none">
                                    <template x-for="c in countries" :key="c.code">
                                        <option :value="c.dial" x-text="c.dial + ' ' + c.name" :selected="c.dial === dial"></option>
                                    </template>
                                </select>
                                <input x-model="local" @input="sync()" type="tel" inputmode="numeric" placeholder="0718713781"
                                       class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block w-full text-sm" />
                            </div>
                            <x-input-error :messages="$errors->get('userPhone')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label value="Rôle" />
                            <select wire:model="userRole" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                                @foreach ($this->assignableRoles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('userRole')" class="mt-1" />
                        </div>

                        @if (in_array($userRole, ['SELLER', 'OUTLET_MANAGER']))
                            <div>
                                <x-input-label value="Point de vente" />
                                <select wire:model="userOutletId" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="">—</option>
                                    @foreach ($this->outlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('userOutletId')" class="mt-1" />
                            </div>
                        @endif

                        <div class="flex gap-3 pt-1">
                            <x-secondary-button type="button" wire:click="$set('showUserForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                            <x-primary-button type="submit" class="flex-1 justify-center">{{ $editingUserId ? 'Enregistrer' : 'Créer' }}</x-primary-button>
                        </div>
                    </form>
                @endif

                <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                    @forelse ($this->users as $user)
                        <div class="flex items-center justify-between px-3 py-2.5" wire:key="user-{{ $user->id }}">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->phone ?? '—' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-status-badge status="gray" :label="$user->role->label()" />
                                @if ($this->canManage && ! in_array($user->role->value, ['ADMIN_COMPANY', 'SUPER_ADMIN']))
                                    <button type="button" wire:click="openUserForm({{ $user->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                                    <button type="button" wire:click="requestToggleUser({{ $user->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">{{ $user->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucun utilisateur.</p>
                    @endforelse
                </div>
            </div>

            {{-- Points de vente --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold text-gray-900">Points de vente</h2>
                    @if ($this->canManage)
                        <button type="button" wire:click="openOutletForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-2.5 py-1.5">+ Ajouter</button>
                    @endif
                </div>

                @if ($showOutletForm)
                    <form wire:submit="saveOutlet" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 mb-2">
                        <div>
                            <x-input-label value="Nom" />
                            <x-text-input wire:model="outletName" type="text" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('outletName')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Adresse" />
                            <x-text-input wire:model="outletAddress" type="text" class="block mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label value="Téléphone" />
                            <x-text-input wire:model="outletPhone" type="text" class="block mt-1 w-full" />
                        </div>
                        <div class="flex gap-3 pt-1">
                            <x-secondary-button type="button" wire:click="$set('showOutletForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                            <x-primary-button type="submit" class="flex-1 justify-center">{{ $editingOutletId ? 'Enregistrer' : 'Créer' }}</x-primary-button>
                        </div>
                    </form>
                @endif

                <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                    @forelse ($this->outlets as $outlet)
                        <div class="flex items-center justify-between px-3 py-2.5" wire:key="outlet-{{ $outlet->id }}">
                            <div>
                                <span class="text-sm text-gray-700">{{ $outlet->name }} — {{ $outlet->address }}</span>
                                @unless ($outlet->is_active) <x-status-badge status="red" label="Inactif" class="ml-2" /> @endunless
                            </div>
                            @if ($this->canManage)
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" wire:click="openOutletForm({{ $outlet->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                                    <button type="button" wire:click="requestToggleOutlet({{ $outlet->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">{{ $outlet->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucun point de vente.</p>
                    @endforelse
                </div>
            </div>

            {{-- Dépôts --}}
            <div class="pb-10">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold text-gray-900">Dépôts</h2>
                    @if ($this->canManage)
                        <button type="button" wire:click="openWarehouseForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-2.5 py-1.5">+ Ajouter</button>
                    @endif
                </div>

                @if ($showWarehouseForm)
                    <form wire:submit="saveWarehouse" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 mb-2">
                        <div>
                            <x-input-label value="Nom" />
                            <x-text-input wire:model="warehouseName" type="text" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('warehouseName')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Adresse" />
                            <x-text-input wire:model="warehouseAddress" type="text" class="block mt-1 w-full" />
                        </div>
                        <div class="flex gap-3 pt-1">
                            <x-secondary-button type="button" wire:click="$set('showWarehouseForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                            <x-primary-button type="submit" class="flex-1 justify-center">{{ $editingWarehouseId ? 'Enregistrer' : 'Créer' }}</x-primary-button>
                        </div>
                    </form>
                @endif

                <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                    @forelse ($this->warehouses as $warehouse)
                        <div class="flex items-center justify-between px-3 py-2.5" wire:key="warehouse-{{ $warehouse->id }}">
                            <div>
                                <span class="text-sm text-gray-700">{{ $warehouse->name }} — {{ $warehouse->address }}</span>
                                @unless ($warehouse->is_active) <x-status-badge status="red" label="Inactif" class="ml-2" /> @endunless
                            </div>
                            @if ($this->canManage)
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" wire:click="openWarehouseForm({{ $warehouse->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                                    <button type="button" wire:click="requestToggleWarehouse({{ $warehouse->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">{{ $warehouse->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucun dépôt.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ════ MOBILE ════ --}}
<div class="lg:hidden p-4 space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        @if ($this->company->logo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($this->company->logo_path) }}"
                 alt="" class="h-8 w-8 rounded-lg object-cover shrink-0">
        @endif
        <div>
            <h1 class="text-base font-extrabold text-ink">{{ $this->company->name }}</h1>
            <p class="text-xs text-ink-soft">Paramètres de la société</p>
        </div>
    </div>

    @if ($this->canManage)
        @if ($profileSaved)
            <div class="rounded-xl border border-success/20 bg-success-wash px-4 py-3 text-sm font-bold text-success flex items-center gap-2">
                <span>✓</span> Enregistré avec succès.
            </div>
        @endif

        <div class="rounded-2xl border border-line bg-white">
            <div class="px-5 py-4 border-b border-line">
                <p class="text-[11px] font-extrabold text-ink-soft uppercase tracking-widest">Informations société</p>
            </div>

            <form wire:submit="saveCompanyProfile" class="px-5 py-5 space-y-4">
                <div>
                    <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Nom de la société</label>
                    <input wire:model="companyName" type="text"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                    @error('companyName') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Adresse</label>
                    <input wire:model="companyAddress" type="text"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Téléphone</label>
                    <input wire:model="companyPhone" type="text"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Email</label>
                    <input wire:model="companyEmail" type="email"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                    @error('companyEmail') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Devise</label>
                        <input wire:model="companyCurrency" type="text" maxlength="3"
                               class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink uppercase placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                        @error('companyCurrency') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Préfixe factures</label>
                        <input wire:model="companyInvoicePrefix" type="text" maxlength="10"
                               class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink uppercase placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition">
                        @error('companyInvoicePrefix') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Pied de page (factures)</label>
                    <textarea wire:model="companyFooterText" rows="2"
                              class="w-full rounded-xl border border-line bg-cream px-3 py-2.5 text-sm text-ink placeholder-ink-soft/60 focus:border-brand focus:ring-0 focus:outline-none transition resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 items-end">
                    <div>
                        <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Logo</label>
                        @if ($this->company->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($this->company->logo_path) }}"
                                 alt="" class="h-10 w-10 rounded-lg object-cover mb-2">
                        @endif
                        <input type="file" wire:model="companyLogo" accept="image/*"
                               class="w-full text-sm text-ink-soft file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white">
                        @error('companyLogo') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold text-ink-soft uppercase tracking-widest mb-1.5">Couleur d'accent</label>
                        <input type="color" wire:model="companyPrimaryColor"
                               class="h-11 w-full rounded-xl border border-line cursor-pointer p-1">
                        @error('companyPrimaryColor') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand py-3 text-sm font-extrabold text-white shadow-brand-glow active:scale-95 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Enregistrer les paramètres
                </button>
            </form>
        </div>
    @endif

    {{-- Utilisateur créé --}}
    @if ($createdUserPassword)
        <div class="rounded-xl border border-success/20 bg-success-wash px-4 py-3 text-sm text-success space-y-1">
            <p class="font-extrabold">Utilisateur créé — donne ces accès au vendeur :</p>
            <p>Téléphone : <span class="font-mono font-bold">{{ $createdUserPhone }}</span></p>
            <p>Code : <span class="font-mono font-bold">{{ $createdUserPassword }}</span></p>
        </div>
    @endif

    {{-- Utilisateurs --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-gray-900">Utilisateurs</h2>
            @if ($this->canManage)
                <button type="button" wire:click="openUserForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-2.5 py-1.5">+ Ajouter</button>
            @endif
        </div>

        @if ($showUserForm)
            <form wire:submit="saveUser" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 mb-2">
                <div>
                    <x-input-label value="Nom" />
                    <x-text-input wire:model="userName" type="text" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('userName')" class="mt-1" />
                </div>

                <div x-data="{
                    dial: '+225',
                    countries: [
                        {code:'CI', dial:'+225', name:'Côte d\'Ivoire'},
                        {code:'SN', dial:'+221', name:'Sénégal'},
                        {code:'ML', dial:'+223', name:'Mali'},
                        {code:'BF', dial:'+226', name:'Burkina Faso'},
                        {code:'GN', dial:'+224', name:'Guinée'},
                        {code:'TG', dial:'+228', name:'Togo'},
                        {code:'BJ', dial:'+229', name:'Bénin'},
                        {code:'NE', dial:'+227', name:'Niger'},
                        {code:'GH', dial:'+233', name:'Ghana'},
                        {code:'NG', dial:'+234', name:'Nigeria'},
                        {code:'CM', dial:'+237', name:'Cameroun'},
                        {code:'FR', dial:'+33',  name:'France'},
                        {code:'BE', dial:'+32',  name:'Belgique'},
                    ],
                    local: '',
                    get full() { return this.dial + this.local.replace(/\s+/g,''); },
                    sync() { $wire.set('userPhone', this.full); },
                }" x-init="
                    local = $wire.userPhone.replace(/^\+\d+/, '');
                    countries.forEach(c => { if ($wire.userPhone.startsWith(c.dial)) dial = c.dial; });
                ">
                    <x-input-label value="Téléphone" />
                    <div class="flex mt-1 gap-2">
                        <select x-model="dial" @change="sync()" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm text-sm w-36 flex-none">
                            <template x-for="c in countries" :key="c.code">
                                <option :value="c.dial" x-text="c.dial + ' ' + c.name" :selected="c.dial === dial"></option>
                            </template>
                        </select>
                        <input x-model="local" @input="sync()" type="tel" inputmode="numeric" placeholder="0718713781"
                               class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block w-full text-sm" />
                    </div>
                    <x-input-error :messages="$errors->get('userPhone')" class="mt-1" />
                </div>

                <div>
                    <x-input-label value="Rôle" />
                    <select wire:model="userRole" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                        @foreach ($this->assignableRoles as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('userRole')" class="mt-1" />
                </div>

                @if (in_array($userRole, ['SELLER', 'OUTLET_MANAGER']))
                    <div>
                        <x-input-label value="Point de vente" />
                        <select wire:model="userOutletId" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block mt-1 w-full">
                            <option value="">—</option>
                            @foreach ($this->outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('userOutletId')" class="mt-1" />
                    </div>
                @endif

                <div class="flex gap-3 pt-1">
                    <x-secondary-button type="button" wire:click="$set('showUserForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                    <x-primary-button type="submit" class="flex-1 justify-center">{{ $editingUserId ? 'Enregistrer' : 'Créer' }}</x-primary-button>
                </div>
            </form>
        @endif

        <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            @forelse ($this->users as $user)
                <div class="flex items-center justify-between px-3 py-2.5" wire:key="mob-user-{{ $user->id }}">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $user->phone ?? '—' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-status-badge status="gray" :label="$user->role->label()" />
                        @if ($this->canManage && ! in_array($user->role->value, ['ADMIN_COMPANY', 'SUPER_ADMIN']))
                            <button type="button" wire:click="openUserForm({{ $user->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                            <button type="button" wire:click="requestToggleUser({{ $user->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">{{ $user->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucun utilisateur.</p>
            @endforelse
        </div>
    </div>

    {{-- Points de vente --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-gray-900">Points de vente</h2>
            @if ($this->canManage)
                <button type="button" wire:click="openOutletForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-2.5 py-1.5">+ Ajouter</button>
            @endif
        </div>

        @if ($showOutletForm)
            <form wire:submit="saveOutlet" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 mb-2">
                <div>
                    <x-input-label value="Nom" />
                    <x-text-input wire:model="outletName" type="text" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('outletName')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Adresse" />
                    <x-text-input wire:model="outletAddress" type="text" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Téléphone" />
                    <x-text-input wire:model="outletPhone" type="text" class="block mt-1 w-full" />
                </div>
                <div class="flex gap-3 pt-1">
                    <x-secondary-button type="button" wire:click="$set('showOutletForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                    <x-primary-button type="submit" class="flex-1 justify-center">{{ $editingOutletId ? 'Enregistrer' : 'Créer' }}</x-primary-button>
                </div>
            </form>
        @endif

        <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            @forelse ($this->outlets as $outlet)
                <div class="flex items-center justify-between px-3 py-2.5" wire:key="mob-outlet-{{ $outlet->id }}">
                    <div>
                        <span class="text-sm text-gray-700">{{ $outlet->name }} — {{ $outlet->address }}</span>
                        @unless ($outlet->is_active) <x-status-badge status="red" label="Inactif" class="ml-2" /> @endunless
                    </div>
                    @if ($this->canManage)
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="openOutletForm({{ $outlet->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                            <button type="button" wire:click="requestToggleOutlet({{ $outlet->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">{{ $outlet->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                        </div>
                    @endif
                </div>
            @empty
                <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucun point de vente.</p>
            @endforelse
        </div>
    </div>

    {{-- Dépôts --}}
    <div class="pb-24">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-gray-900">Dépôts</h2>
            @if ($this->canManage)
                <button type="button" wire:click="openWarehouseForm" class="rounded-lg bg-orange-600 text-white text-xs font-medium px-2.5 py-1.5">+ Ajouter</button>
            @endif
        </div>

        @if ($showWarehouseForm)
            <form wire:submit="saveWarehouse" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 mb-2">
                <div>
                    <x-input-label value="Nom" />
                    <x-text-input wire:model="warehouseName" type="text" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('warehouseName')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Adresse" />
                    <x-text-input wire:model="warehouseAddress" type="text" class="block mt-1 w-full" />
                </div>
                <div class="flex gap-3 pt-1">
                    <x-secondary-button type="button" wire:click="$set('showWarehouseForm', false)" class="flex-1 justify-center">Annuler</x-secondary-button>
                    <x-primary-button type="submit" class="flex-1 justify-center">{{ $editingWarehouseId ? 'Enregistrer' : 'Créer' }}</x-primary-button>
                </div>
            </form>
        @endif

        <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            @forelse ($this->warehouses as $warehouse)
                <div class="flex items-center justify-between px-3 py-2.5" wire:key="mob-warehouse-{{ $warehouse->id }}">
                    <div>
                        <span class="text-sm text-gray-700">{{ $warehouse->name }} — {{ $warehouse->address }}</span>
                        @unless ($warehouse->is_active) <x-status-badge status="red" label="Inactif" class="ml-2" /> @endunless
                    </div>
                    @if ($this->canManage)
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="openWarehouseForm({{ $warehouse->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                            <button type="button" wire:click="requestToggleWarehouse({{ $warehouse->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">{{ $warehouse->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                        </div>
                    @endif
                </div>
            @empty
                <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucun dépôt.</p>
            @endforelse
        </div>
    </div>

</div>

</div>
