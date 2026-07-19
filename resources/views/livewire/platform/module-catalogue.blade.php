<div class="p-3 space-y-3">

    {{-- En-tête --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('platform.index') }}" wire:navigate class="text-xs text-brand font-medium">← Sociétés</a>
            <h1 class="text-base font-semibold text-gray-900">Catalogue de modules</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('platform.company-modules') }}" wire:navigate class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1.5">
                Modules par société
            </a>
            <button
                type="button"
                wire:click="openCreateForm"
                class="rounded-lg bg-brand text-white text-xs font-medium px-3 py-1.5"
            >
                + Nouveau module
            </button>
        </div>
    </div>

    {{-- Formulaire création / édition --}}
    @if ($showForm)
        <form wire:submit="save" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <h2 class="text-sm font-semibold text-gray-900">{{ $editingId ? 'Modifier le module' : 'Nouveau module' }}</h2>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Code <span class="text-gray-400">(unique, snake_case)</span></label>
                    <input wire:model="code" type="text" @if($editingId) readonly @endif
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm @if($editingId) opacity-60 cursor-not-allowed @endif" />
                    @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nom</label>
                    <input wire:model="name" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                <textarea wire:model="description" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm resize-none"></textarea>
                @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                    <select wire:model="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-white">
                        <option value="available">Disponible</option>
                        <option value="planned">Planifié</option>
                        <option value="deprecated">Déprécié</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tarification</label>
                    <select wire:model="pricingType" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-white">
                        <option value="">—</option>
                        <option value="free">Gratuit</option>
                        <option value="paid">Payant</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Prix ({{ auth()->user()?->company?->currency ?? 'XOF' }})</label>
                    <input wire:model="price" type="number" min="0" step="0.01"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                           placeholder="0" />
                    @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" wire:click="cancelForm"
                        class="flex-1 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium py-2">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 rounded-lg bg-brand text-white text-sm font-medium py-2">
                    {{ $editingId ? 'Enregistrer' : 'Créer' }}
                </button>
            </div>
        </form>
    @endif

    {{-- Liste --}}
    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($this->modules as $module)
            @php
                $statusColor = match ($module->status) {
                    'available'  => 'bg-green-100 text-green-700',
                    'planned'    => 'bg-blue-100 text-blue-700',
                    'deprecated' => 'bg-gray-100 text-gray-500',
                    default      => 'bg-gray-100 text-gray-500',
                };
                $statusLabel = match ($module->status) {
                    'available'  => 'Disponible',
                    'planned'    => 'Planifié',
                    'deprecated' => 'Déprécié',
                    default      => $module->status,
                };
            @endphp
            <div class="flex items-start justify-between px-3 py-3 gap-3" wire:key="module-{{ $module->id }}">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900">{{ $module->name }}</p>
                        <span class="font-mono text-[11px] text-gray-400 bg-gray-50 border border-gray-200 rounded px-1.5 py-0.5">{{ $module->code }}</span>
                        <span class="text-[11px] font-medium rounded-full px-2 py-0.5 {{ $statusColor }}">{{ $statusLabel }}</span>
                        @if ($module->pricing_type)
                            <span class="text-[11px] font-medium rounded-full px-2 py-0.5 bg-gray-50 border border-gray-200 text-gray-600">
                                {{ $module->pricing_type === 'free' ? 'Gratuit' : 'Payant' }}
                                @if ($module->price)
                                    · {{ number_format($module->price / 100, 0, ',', ' ') }}
                                @endif
                            </span>
                        @endif
                    </div>
                    @if ($module->description)
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $module->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" wire:click="openEditForm({{ $module->id }})"
                            class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-1.5">
                        Éditer
                    </button>
                    <button type="button" wire:click="requestDelete({{ $module->id }})"
                            class="rounded-lg bg-red-50 text-red-600 text-xs font-medium px-2.5 py-1.5">
                        Supprimer
                    </button>
                </div>
            </div>
        @empty
            <p class="px-3 py-6 text-sm text-gray-400 text-center">Aucun module dans le catalogue.</p>
        @endforelse
    </div>
</div>
