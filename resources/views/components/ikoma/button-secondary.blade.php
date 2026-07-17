@props([
    'icon' => null,
    'type' => 'button',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-ink bg-white border border-line transition hover:border-brand/40 active:bg-cream focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand']) }}
>
    @if ($icon)
        <span class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-[10px] bg-brand-wash text-base">
            {{ $icon }}
        </span>
    @endif

    <span class="flex-1 text-left">{{ $slot }}</span>

    <span class="ml-auto text-sm opacity-30">›</span>
</button>
