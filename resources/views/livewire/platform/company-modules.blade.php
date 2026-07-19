<div class="p-3 space-y-3">

    {{-- En-tête --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('platform.modules') }}" wire:navigate class="text-xs text-brand font-medium">← Catalogue</a>
            <h1 class="text-base font-semibold text-gray-900">Modules par société</h1>
        </div>
        <a href="{{ route('platform.index') }}" wire:navigate class="rounded-lg bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1.5">
            Sociétés
        </a>
    </div>

    @if ($this->modules->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-8 text-sm text-gray-400 text-center">
            Aucun module disponible dans le catalogue.
        </div>
    @else
        {{-- Légende colonnes --}}
        <div class="hidden sm:grid grid-cols-[1fr_repeat({{ $this->modules->count() }},auto)] gap-x-4 px-3 pb-1">
            <span></span>
            @foreach ($this->modules as $module)
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400 text-center w-16">{{ $module->name }}</span>
            @endforeach
        </div>

        {{-- Cartes sociétés --}}
        <div class="space-y-2">
            @forelse ($this->companies as $company)
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3" wire:key="co-{{ $company->id }}">
                    {{-- Mobile : nom + interrupteurs empilés --}}
                    <p class="text-sm font-semibold text-gray-900 mb-2">{{ $company->name }}
                        @unless ($company->is_active)
                            <span class="ml-1 text-[11px] font-medium text-red-500 bg-red-50 rounded-full px-2 py-0.5">suspendue</span>
                        @endunless
                    </p>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($this->modules as $module)
                            @php $enabled = $this->isEnabled($company, $module->id); @endphp
                            <div class="flex items-center gap-2" wire:key="co-{{ $company->id }}-mod-{{ $module->id }}">
                                {{-- Toggle switch --}}
                                <button
                                    type="button"
                                    wire:click="toggle({{ $company->id }}, {{ $module->id }})"
                                    wire:loading.attr="disabled"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none
                                           {{ $enabled ? 'bg-brand' : 'bg-gray-200' }}"
                                    role="switch"
                                    aria-checked="{{ $enabled ? 'true' : 'false' }}"
                                    title="{{ $module->name }}"
                                >
                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200
                                                 {{ $enabled ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                </button>
                                <span class="text-xs text-gray-600 sm:hidden">{{ $module->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-8 text-sm text-gray-400 text-center">
                    Aucune société enregistrée.
                </div>
            @endforelse
        </div>
    @endif
</div>
