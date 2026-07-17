<div class="p-3 space-y-3">
    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">Sociétés</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('platform.settings') }}" wire:navigate class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1.5">
                Paramètres
            </a>
            <button
                type="button"
                wire:click="openCreateForm"
                class="rounded-lg bg-indigo-600 text-white text-xs font-medium px-3 py-1.5"
            >
                + Nouvelle société
            </button>
        </div>
    </div>

    @if ($createdPassword)
        <div class="rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800 space-y-1">
            <p class="font-medium">Société créée.</p>
            <p>Identifiants du compte administrateur (à noter, ils ne seront plus affichés) :</p>
            <p>Email : <span class="font-mono">{{ $createdAdminEmail }}</span></p>
            <p>Mot de passe : <span class="font-mono">{{ $createdPassword }}</span></p>
        </div>
    @endif

    @if ($showCreateForm)
        <form wire:submit="{{ $editingId ? 'updateCompany' : 'createCompany' }}" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <h2 class="text-sm font-semibold text-gray-900">{{ $editingId ? 'Modifier la société' : 'Nouvelle société' }}</h2>

            <div>
                <x-input-label value="Nom de la société" />
                <x-text-input wire:model="name" type="text" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Adresse" />
                    <x-text-input wire:model="address" type="text" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Téléphone" />
                    <x-text-input wire:model="phone" type="text" class="block mt-1 w-full" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <x-input-label value="Email de la société" />
                    <x-text-input wire:model="email" type="email" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Devise" />
                    <x-text-input wire:model="currency" type="text" maxlength="3" class="block mt-1 w-full uppercase" />
                    <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label value="Préfixe des factures" />
                <x-text-input wire:model="invoicePrefix" type="text" maxlength="10" class="block mt-1 w-full uppercase" />
                <x-input-error :messages="$errors->get('invoicePrefix')" class="mt-1" />
            </div>

            @unless ($editingId)
                <div class="border-t border-gray-100 pt-3 space-y-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Premier compte administrateur</p>

                    <div>
                        <x-input-label value="Nom" />
                        <x-text-input wire:model="adminName" type="text" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('adminName')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Email" />
                        <x-text-input wire:model="adminEmail" type="email" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('adminEmail')" class="mt-1" />
                    </div>
                </div>
            @endunless

            <div class="flex gap-3 pt-1">
                <x-secondary-button type="button" wire:click="cancelCreate" class="flex-1 justify-center">
                    Annuler
                </x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">
                    {{ $editingId ? 'Enregistrer' : 'Créer' }}
                </x-primary-button>
            </div>
        </form>
    @endif

    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($this->companies as $company)
            <div class="flex items-center justify-between px-3 py-3" wire:key="company-{{ $company->id }}">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $company->name }}</p>
                    <p class="text-xs text-gray-400">{{ $company->email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-status-badge :status="$company->is_active ? 'green' : 'red'" :label="$company->is_active ? 'Active' : 'Suspendue'" />
                    <button
                        type="button"
                        wire:click="openEditForm({{ $company->id }})"
                        class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-1.5"
                    >
                        Éditer
                    </button>
                    <button
                        type="button"
                        wire:click="requestToggle({{ $company->id }})"
                        class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-1.5"
                    >
                        {{ $company->is_active ? 'Suspendre' : 'Réactiver' }}
                    </button>
                </div>
            </div>
        @empty
            <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucune société pour l'instant.</p>
        @endforelse
    </div>
</div>
