@props(['active' => 'home'])

@php
$items = [
    ['key' => 'home',     'icon' => '🏠', 'label' => 'Accueil',   'route' => 'app.dashboard'],
    ['key' => 'sell',     'icon' => '🛒', 'label' => 'Vendre',    'route' => 'sales.create'],
    ['key' => 'payments', 'icon' => '💰', 'label' => 'Paiements', 'route' => 'payments.index'],
    ['key' => 'stock',    'icon' => '📦', 'label' => 'Stock',     'route' => 'stock.index'],
    ['key' => 'clients',  'icon' => '👥', 'label' => 'Clients',   'route' => 'customers.index'],
];

$hasDeliveries = auth()->user()?->company?->hasModule('deliveries') ?? false;

if ($hasDeliveries) {
    $items[] = ['key' => 'livraisons', 'icon' => '🚚', 'label' => 'Livraisons', 'route' => 'deliveries.index'];
}

$canManage = in_array(auth()->user()?->role, [
    \App\Enums\UserRole::ADMIN_COMPANY,
    \App\Enums\UserRole::OUTLET_MANAGER,
]);
@endphp

<aside class="w-20 flex-none bg-charcoal flex flex-col items-center py-5 gap-1.5">
    <div class="mb-5 h-9 w-9 rounded-[11px] bg-brand flex items-center justify-center text-white text-[13px] font-extrabold">
        IK
    </div>

    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}" wire:navigate
           class="flex w-16 flex-col items-center gap-1 rounded-xl py-2.5 text-[10px] font-extrabold transition
                  {{ $active === $item['key'] ? 'bg-brand/20 text-brand' : 'text-charcoal-line hover:text-white/70' }}">
            <span class="text-lg leading-none">{{ $item['icon'] }}</span>
            {{ $item['label'] }}
        </a>
    @endforeach

    @if ($canManage)
        <a href="{{ route('admin.index') }}" wire:navigate
           class="mt-auto flex w-16 flex-col items-center gap-1 rounded-xl py-2.5 text-[10px] font-extrabold transition
                  {{ $active === 'manage' ? 'bg-brand/20 text-brand' : 'text-charcoal-line hover:text-white/70' }}">
            <span class="text-lg leading-none">⚙️</span>
            Gestion
        </a>
    @endif
</aside>
