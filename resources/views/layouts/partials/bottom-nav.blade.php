@php
    $user = auth()->user();

    $tabs = $user->role === \App\Enums\UserRole::SUPER_ADMIN
        ? [
            ['route' => $user->role->landingRoute(), 'label' => 'Sociétés', 'icon' => 'home'],
            ['route' => 'profile', 'label' => 'Profil', 'icon' => 'users'],
        ]
        : [
            ['route' => $user->role->landingRoute(), 'label' => 'Accueil', 'icon' => 'home'],
            ['route' => 'sales.index', 'label' => 'Vendre', 'icon' => 'cart'],
            ['route' => 'stock.index', 'label' => 'Stock', 'icon' => 'box'],
            ['route' => 'customers.index', 'label' => 'Clients', 'icon' => 'users'],
        ];

    // Même condition que TransferPolicy::manage
    $canTransfer = in_array($user->role, [
        \App\Enums\UserRole::ADMIN_COMPANY,
        \App\Enums\UserRole::OUTLET_MANAGER,
        \App\Enums\UserRole::WAREHOUSE_KEEPER,
    ]);

    // Même condition que x-ikoma.desktop-sidebar
    $canManage = in_array($user->role, [
        \App\Enums\UserRole::ADMIN_COMPANY,
        \App\Enums\UserRole::OUTLET_MANAGER,
    ]);

    $colCount = count($tabs) + 1 + ($canManage ? 1 : 0); // +1 Plus, +1 Gestion si éligible
@endphp

<nav
    x-data="{ more: false, manage: false }"
    class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-100 pb-[env(safe-area-inset-bottom)]"
>
    <div class="grid h-16" style="grid-template-columns: repeat({{ $colCount }}, minmax(0, 1fr));">
        @foreach ($tabs as $tab)
            @php $active = request()->routeIs($tab['route']); @endphp
            <a
                href="{{ route($tab['route']) }}"
                wire:navigate
                class="flex flex-col items-center justify-center gap-0.5 text-[11px] {{ $active ? '' : 'text-gray-500' }}"
                @style([$active ? 'color: var(--brand, #ea580c)' : ''])
            >
                <x-icon :name="$tab['icon']" class="h-6 w-6" />
                {{ $tab['label'] }}
            </a>
        @endforeach

        @if ($canManage)
            <button
                type="button"
                @click="manage = ! manage; more = false"
                class="flex flex-col items-center justify-center gap-0.5 text-[11px] text-gray-500"
            >
                <span class="text-xl leading-none">⚙️</span>
                Gestion
            </button>
        @endif

        <button
            type="button"
            @click="more = ! more; manage = false"
            class="flex flex-col items-center justify-center gap-0.5 text-[11px] text-gray-500"
        >
            <x-icon name="more" class="h-6 w-6" />
            Plus
        </button>
    </div>

    {{-- Menu "Gestion" (ADMIN_COMPANY | OUTLET_MANAGER) --}}
    @if ($canManage)
        <div
            x-show="manage"
            x-transition
            @click.outside="manage = false"
            class="absolute bottom-16 right-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1"
            style="display: none;"
        >
            @if ($canTransfer)
                <a href="{{ route('transfers.index') }}" wire:navigate class="block px-4 py-2.5 text-sm text-gray-700">Transferts</a>
            @endif
            <a href="{{ route('admin.index') }}" wire:navigate class="block px-4 py-2.5 text-sm text-gray-700">Administration</a>
        </div>
    @endif

    {{-- Menu "Plus" --}}
    <div
        x-show="more"
        x-transition
        @click.outside="more = false"
        class="absolute bottom-16 right-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1"
        style="display: none;"
    >
        @unless ($user->role === \App\Enums\UserRole::SUPER_ADMIN)
            <a href="{{ route('deliveries.index') }}" wire:navigate class="block px-4 py-2.5 text-sm text-gray-700">Livraisons</a>
            <a href="{{ route('closing.index') }}" wire:navigate class="block px-4 py-2.5 text-sm text-gray-700">Clôture</a>
            @if ($canTransfer && ! $canManage)
                <a href="{{ route('transfers.index') }}" wire:navigate class="block px-4 py-2.5 text-sm text-gray-700">Transferts</a>
            @endif
        @endunless
        <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2.5 text-sm text-gray-700">Profil</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-600">Déconnexion</button>
        </form>
    </div>
</nav>
