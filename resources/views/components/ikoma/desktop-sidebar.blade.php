@props(['active' => 'home'])

@php
use App\Enums\UserRole;

$user        = auth()->user();
$role        = $user?->role;
$isSuperAdmin     = $role === UserRole::SUPER_ADMIN;
$isAdmin          = $role === UserRole::ADMIN_COMPANY;
$isManager        = $role === UserRole::OUTLET_MANAGER;
$isSeller         = $role === UserRole::SELLER;
$isWarehouseKeeper = $role === UserRole::WAREHOUSE_KEEPER;

$canSell       = $isAdmin || $isManager || $isSeller;
$canManageCo   = $isAdmin || $isManager;
$canCorrectStock = $isAdmin || $isWarehouseKeeper;

$hasDeliveries = ! $isSuperAdmin && ($user?->company?->hasModule('deliveries') ?? false);
$hasQuotes     = ! $isSuperAdmin && ($user?->company?->hasModule('quotes') ?? false);

// Accueil = landing page du rôle courant
$homeRoute = match ($role) {
    UserRole::SUPER_ADMIN          => 'platform.index',
    UserRole::ADMIN_COMPANY,
    UserRole::OUTLET_MANAGER       => 'app.dashboard',
    UserRole::SELLER               => 'app.home',
    UserRole::WAREHOUSE_KEEPER     => 'app.stock',
    default                        => 'app.dashboard',
};
@endphp

<aside class="w-52 flex-none bg-charcoal flex flex-col py-5 overflow-y-auto shrink-0">

    {{-- Logo --}}
    <div class="px-4 mb-6 flex items-center gap-2.5">
        <div class="h-8 w-8 rounded-[10px] bg-brand flex items-center justify-center text-white text-[12px] font-extrabold shrink-0">
            IK
        </div>
        <span class="text-white text-sm font-extrabold tracking-tight">Ikoma</span>
    </div>

    {{-- ── Groupe : Principal ──────────────────────────────────── --}}
    @if (! $isSuperAdmin)
        <div class="px-3 mb-3">
            <p class="text-[10px] font-extrabold uppercase tracking-widest text-charcoal-line px-2 mb-1">Principal</p>

            <a href="{{ route($homeRoute) }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'home',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'home'])>
                <span class="w-5 text-center leading-none">🏠</span>
                <span>Accueil</span>
            </a>

            @if ($canSell)
                <a href="{{ route('sales.create') }}" wire:navigate
                   @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                           'bg-brand/20 text-brand'        => $active === 'sell',
                           'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'sell'])>
                    <span class="w-5 text-center leading-none">🛒</span>
                    <span>Vendre</span>
                </a>

                <a href="{{ route('sales.index') }}" wire:navigate
                   @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                           'bg-brand/20 text-brand'        => $active === 'history',
                           'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'history'])>
                    <span class="w-5 text-center leading-none">📋</span>
                    <span>Historique</span>
                </a>

                <a href="{{ route('payments.index') }}" wire:navigate
                   @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                           'bg-brand/20 text-brand'        => $active === 'payments',
                           'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'payments'])>
                    <span class="w-5 text-center leading-none">💰</span>
                    <span>Encaissements</span>
                </a>
            @endif

            @if ($hasQuotes && $canSell)
                <a href="{{ route('quotes.index') }}" wire:navigate
                   @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                           'bg-brand/20 text-brand'        => $active === 'quotes',
                           'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'quotes'])>
                    <span class="w-5 text-center leading-none">📄</span>
                    <span>Devis</span>
                </a>
            @endif

            @if ($hasDeliveries && $canSell)
                <a href="{{ route('deliveries.index') }}" wire:navigate
                   @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                           'bg-brand/20 text-brand'        => $active === 'livraisons',
                           'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'livraisons'])>
                    <span class="w-5 text-center leading-none">🚚</span>
                    <span>Livraisons</span>
                </a>
            @endif
        </div>
    @endif

    {{-- ── Groupe : Catalogue ──────────────────────────────────── --}}
    @if (! $isSuperAdmin)
        <div class="px-3 mb-3">
            <p class="text-[10px] font-extrabold uppercase tracking-widest text-charcoal-line px-2 mb-1">Catalogue</p>

            <a href="{{ route('stock.index') }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'stock',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'stock'])>
                <span class="w-5 text-center leading-none">📦</span>
                <span>Stock & produits</span>
            </a>

            @if ($canSell)
                <a href="{{ route('customers.index') }}" wire:navigate
                   @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                           'bg-brand/20 text-brand'        => $active === 'clients',
                           'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'clients'])>
                    <span class="w-5 text-center leading-none">👥</span>
                    <span>Clients</span>
                </a>
            @endif

            <a href="{{ route('transfers.index') }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'transfers',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'transfers'])>
                <span class="w-5 text-center leading-none">🔄</span>
                <span>Transferts</span>
            </a>

            @if ($canCorrectStock)
                <a href="{{ route('stock.correction') }}" wire:navigate
                   @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                           'bg-brand/20 text-brand'        => $active === 'stock-correction',
                           'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'stock-correction'])>
                    <span class="w-5 text-center leading-none">✏️</span>
                    <span>Corriger le stock</span>
                </a>
            @endif
        </div>
    @endif

    {{-- ── Groupe : Gestion ──────────────────────────────────── --}}
    @if ($canManageCo)
        <div class="px-3 mb-3">
            <p class="text-[10px] font-extrabold uppercase tracking-widest text-charcoal-line px-2 mb-1">Gestion</p>

            <a href="{{ route('closing.index') }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'closing',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'closing'])>
                <span class="w-5 text-center leading-none">🔒</span>
                <span>Clôture</span>
            </a>

            <a href="{{ route('admin.index') }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'manage',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'manage'])>
                <span class="w-5 text-center leading-none">⚙️</span>
                <span>Paramètres</span>
            </a>
        </div>
    @endif

    {{-- ── Groupe : Éditeur (SUPER_ADMIN) ──────────────────────── --}}
    @if ($isSuperAdmin)
        <div class="px-3 mb-3">
            <p class="text-[10px] font-extrabold uppercase tracking-widest text-charcoal-line px-2 mb-1">Éditeur</p>

            <a href="{{ route('platform.index') }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'platform',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'platform'])>
                <span class="w-5 text-center leading-none">🏢</span>
                <span>Entreprises clientes</span>
            </a>

            <a href="{{ route('platform.modules') }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'platform-modules',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'platform-modules'])>
                <span class="w-5 text-center leading-none">🧩</span>
                <span>Modules</span>
            </a>

            <a href="{{ route('platform.settings') }}" wire:navigate
               @class(['flex items-center gap-2.5 px-2 py-2 rounded-xl text-[13px] font-bold transition',
                       'bg-brand/20 text-brand'        => $active === 'platform-settings',
                       'text-charcoal-line hover:text-white/70 hover:bg-white/5' => $active !== 'platform-settings'])>
                <span class="w-5 text-center leading-none">⚙️</span>
                <span>Paramètres</span>
            </a>
        </div>
    @endif

</aside>
