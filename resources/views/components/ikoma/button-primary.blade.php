@props([
    'icon' => null,
    'type' => 'button',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-white bg-brand shadow-brand-glow border border-transparent transition hover:brightness-90 active:brightness-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand']) }}
>
    @if ($icon)
        <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-white/20 text-base">
            {{ $icon }}
        </span>
    @endif

    <span class="flex-1 text-left">{{ $slot }}</span>

    <span class="ml-auto text-sm opacity-50">›</span>
</button>
