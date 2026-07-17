<div class="p-3 space-y-3">
    <div class="flex items-center gap-2">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Rechercher par nom ou téléphone..."
            class="flex-1 rounded-lg border-gray-200 text-sm"
        >
        <button type="button" wire:click="openCreateForm" class="shrink-0 rounded-lg bg-orange-600 text-white text-xs font-medium px-3 py-2">
            + Nouveau client
        </button>
    </div>

    @if ($showCreateForm)
        <form wire:submit="saveCustomer" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <div>
                <x-input-label value="Nom" />
                <x-text-input wire:model="name" type="text" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <x-input-label value="Téléphone" />
                <x-text-input wire:model="phone" type="text" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('phone')" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Adresse" />
                    <x-text-input wire:model="address" type="text" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="Quartier / Ville" />
                    <x-text-input wire:model="neighborhoodCity" type="text" class="block mt-1 w-full" />
                </div>
            </div>

            <div>
                <x-input-label value="Plafond de crédit" />
                <x-text-input wire:model="creditLimit" type="number" step="1" min="0" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('creditLimit')" class="mt-1" />
            </div>

            <div>
                <x-input-label value="Notes" />
                <textarea wire:model="notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-200 text-sm"></textarea>
            </div>

            <div class="flex gap-3">
                <x-secondary-button type="button" wire:click="$set('showCreateForm', false)" class="flex-1 justify-center">
                    Annuler
                </x-secondary-button>
                <x-primary-button type="submit" class="flex-1 justify-center">
                    {{ $editingId ? 'Enregistrer' : 'Créer' }}
                </x-primary-button>
            </div>
        </form>
    @endif

    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($customers as $customer)
            <div class="flex items-center justify-between px-3 py-3" wire:key="customer-{{ $customer->id }}">
                <a href="{{ route('customers.show', $customer) }}" wire:navigate class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                        {{ $customer->name }}
                        @unless ($customer->is_active)
                            <x-status-badge status="red" label="Inactif" class="ml-1" />
                        @endunless
                    </p>
                    <p class="text-xs text-gray-400">{{ $customer->phone }}</p>
                </a>
                <div class="flex items-center gap-2 shrink-0">
                    @if ($customer->open_debt > 0)
                        <x-status-badge status="red" :label="'Dette : '.number_format($customer->open_debt / 100, 0, ',', ' ')" />
                    @endif
                    <button type="button" wire:click="openEditForm({{ $customer->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">Éditer</button>
                    <button type="button" wire:click="requestToggle({{ $customer->id }})" class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1">
                        {{ $customer->is_active ? 'Désactiver' : 'Réactiver' }}
                    </button>
                </div>
            </div>
        @empty
            <p class="text-center text-sm text-gray-400 py-10">Aucun client trouvé.</p>
        @endforelse
    </div>

    {{ $customers->links() }}
</div>
