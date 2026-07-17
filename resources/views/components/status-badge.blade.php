@props(['status' => 'gray', 'label'])

@php
    $colors = [
        'green' => 'bg-green-100 text-green-800',
        'orange' => 'bg-orange-100 text-orange-800',
        'red' => 'bg-red-100 text-red-800',
        'gray' => 'bg-gray-100 text-gray-700',
    ];
    $classes = $colors[$status] ?? $colors['gray'];
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium $classes"]) }}
    aria-label="{{ $label }}"
>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ $label }}
</span>
