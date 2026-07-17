@props([
    'selected' => false,
    'icon'     => null,
])

<div
    {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-[15px] border-2 px-3.5 py-3 font-extrabold text-[13.5px] text-ink transition cursor-pointer '
        . ($selected
            ? 'border-brand bg-brand-wash'
            : 'border-line bg-white hover:border-brand/30')
    ]) }}
>
    {{-- Radio custom --}}
    <span
        class="flex h-[19px] w-[19px] flex-none items-center justify-center rounded-full border-2 transition
            {{ $selected ? 'border-brand' : 'border-line' }}"
    >
        @if ($selected)
            <span class="h-[9px] w-[9px] rounded-full bg-brand"></span>
        @endif
    </span>

    @if ($icon)
        <span class="text-base">{{ $icon }}</span>
    @endif

    <span class="flex-1">{{ $slot }}</span>
</div>
