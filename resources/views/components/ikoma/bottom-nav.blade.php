@props(['active' => 'home'])

@php
    $items = [
        ['key' => 'home',     'icon' => '🏠', 'label' => 'Accueil'],
        ['key' => 'sell',     'icon' => '🛒', 'label' => 'Vendre'],
        ['key' => 'payments', 'icon' => '💰', 'label' => 'Paiements'],
        ['key' => 'clients',  'icon' => '👥', 'label' => 'Clients'],
    ];
@endphp

<nav {{ $attributes->merge(['class' => 'flex justify-around border-t border-line bg-white px-1.5 pb-4 pt-2.5']) }}>
    @foreach ($items as $item)
        @php $isActive = $active === $item['key']; @endphp
        <button
            type="button"
            class="flex flex-col items-center gap-0.5 text-[10px] font-extrabold transition
                {{ $isActive ? 'text-brand' : 'text-ink-soft' }}"
        >
            <span class="text-base leading-none">{{ $item['icon'] }}</span>
            {{ $item['label'] }}
        </button>
    @endforeach
</nav>
