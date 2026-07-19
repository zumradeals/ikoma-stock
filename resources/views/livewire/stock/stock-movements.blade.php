<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="stock-movements" />
    <div class="flex-1 overflow-y-auto">
<div class="p-3 space-y-3">
    <div class="grid grid-cols-2 gap-2">
        <select wire:model.live="typeFilter" class="rounded-lg border-gray-200 text-sm">
            <option value="">Tous les types</option>
            @foreach ($this->types as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>

        <select wire:model.live="productFilter" class="rounded-lg border-gray-200 text-sm">
            <option value="">Tous les produits</option>
            @foreach ($this->products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="userFilter" class="rounded-lg border-gray-200 text-sm">
            <option value="">Tous les utilisateurs</option>
            @foreach ($this->users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>

        <div class="flex gap-1">
            <input type="date" wire:model.live="dateFrom" class="w-1/2 rounded-lg border-gray-200 text-xs">
            <input type="date" wire:model.live="dateTo" class="w-1/2 rounded-lg border-gray-200 text-xs">
        </div>
    </div>

    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($movements as $movement)
            <div wire:key="movement-{{ $movement->id }}">
                <button type="button" wire:click="toggle({{ $movement->id }})" class="w-full flex items-center justify-between px-3 py-2.5 text-left">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $movement->product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $movement->movement_type->label() }} · {{ $movement->movement_date->format('d/m/Y H:i') }} · {{ $movement->user?->name ?? '—' }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">{{ number_format($movement->quantity / 100, 0, ',', ' ') }}</span>
                </button>

                @if ($expanded === $movement->id)
                    <div class="px-3 pb-3 text-xs text-gray-500 space-y-1">
                        @if ($movement->location_source_type)
                            <p>Source : {{ $movement->location_source_type->label() }} #{{ $movement->location_source_id }}</p>
                        @endif
                        @if ($movement->location_destination_type)
                            <p>Destination : {{ $movement->location_destination_type->label() }} #{{ $movement->location_destination_id }}</p>
                        @endif
                        @if ($movement->reason)
                            <p>Motif : {{ $movement->reason }}</p>
                        @endif
                        @if ($movement->document_type)
                            <p>Document : {{ $movement->document_type }} #{{ $movement->document_id }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <p class="text-center text-sm text-gray-400 py-10">Aucun mouvement.</p>
        @endforelse
    </div>

    {{ $movements->links() }}
</div>
    </div>
</div>
{{-- Mobile --}}
<div class="lg:hidden">
<div class="p-3 space-y-3">
    <div class="grid grid-cols-2 gap-2">
        <select wire:model.live="typeFilter" class="rounded-lg border-gray-200 text-sm">
            <option value="">Tous les types</option>
            @foreach ($this->types as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>

        <select wire:model.live="productFilter" class="rounded-lg border-gray-200 text-sm">
            <option value="">Tous les produits</option>
            @foreach ($this->products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="userFilter" class="rounded-lg border-gray-200 text-sm">
            <option value="">Tous les utilisateurs</option>
            @foreach ($this->users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>

        <div class="flex gap-1">
            <input type="date" wire:model.live="dateFrom" class="w-1/2 rounded-lg border-gray-200 text-xs">
            <input type="date" wire:model.live="dateTo" class="w-1/2 rounded-lg border-gray-200 text-xs">
        </div>
    </div>

    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($movements as $movement)
            <div wire:key="movement-{{ $movement->id }}">
                <button type="button" wire:click="toggle({{ $movement->id }})" class="w-full flex items-center justify-between px-3 py-2.5 text-left">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $movement->product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $movement->movement_type->label() }} · {{ $movement->movement_date->format('d/m/Y H:i') }} · {{ $movement->user?->name ?? '—' }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">{{ number_format($movement->quantity / 100, 0, ',', ' ') }}</span>
                </button>

                @if ($expanded === $movement->id)
                    <div class="px-3 pb-3 text-xs text-gray-500 space-y-1">
                        @if ($movement->location_source_type)
                            <p>Source : {{ $movement->location_source_type->label() }} #{{ $movement->location_source_id }}</p>
                        @endif
                        @if ($movement->location_destination_type)
                            <p>Destination : {{ $movement->location_destination_type->label() }} #{{ $movement->location_destination_id }}</p>
                        @endif
                        @if ($movement->reason)
                            <p>Motif : {{ $movement->reason }}</p>
                        @endif
                        @if ($movement->document_type)
                            <p>Document : {{ $movement->document_type }} #{{ $movement->document_id }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <p class="text-center text-sm text-gray-400 py-10">Aucun mouvement.</p>
        @endforelse
    </div>

    {{ $movements->links() }}
</div>
</div>
</div>
