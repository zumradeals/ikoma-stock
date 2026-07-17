<div class="relative" x-data @click.outside="$wire.open = false">
    <button
        type="button"
        wire:click="toggle"
        class="relative h-9 w-9 flex items-center justify-center rounded-full text-gray-600"
        aria-label="Notifications"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 h-4 min-w-4 rounded-full bg-red-600 text-white text-[10px] leading-4 text-center px-1">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-xl shadow-lg border border-gray-100 z-50">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-900">Notifications</span>
                <button type="button" wire:click="markAllRead" class="text-xs text-orange-600 font-medium">Tout marquer lu</button>
            </div>

            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                @forelse ($alerts as $alert)
                    <a href="{{ $alert['url'] }}" wire:navigate class="block px-4 py-3 text-sm hover:bg-gray-50">
                        {{ $alert['message'] }}
                    </a>
                @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">Aucune notification.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
