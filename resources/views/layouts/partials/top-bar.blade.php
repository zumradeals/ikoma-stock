<header class="sticky top-0 z-30 h-14 bg-white border-b border-gray-100 flex items-center justify-between px-4">
    <a href="{{ route(auth()->user()->role->landingRoute()) }}" wire:navigate class="flex items-center gap-2 min-w-0">
        @if (auth()->user()->company?->logo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->company->logo_path) }}" alt="{{ auth()->user()->company->name }}" class="h-7 w-7 rounded object-cover shrink-0">
        @endif
        <span class="font-semibold text-gray-900 truncate">{{ auth()->user()->company?->name ?? 'IKOMA STOCK' }}</span>
    </a>

    <div class="flex items-center gap-1">
        <livewire:dashboard.notifications />

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="h-9 w-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-700 text-sm font-medium">
                    {{ Illuminate\Support\Str::of(auth()->user()->name)->substr(0, 1)->upper() }}
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-4 py-2 text-xs text-gray-400">{{ auth()->user()->name }}</div>
                <x-dropdown-link :href="route('profile')" wire:navigate>
                    Profil
                </x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        Déconnexion
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
