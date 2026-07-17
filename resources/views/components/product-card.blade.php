@props(['product', 'available' => 0])

@php
    $lowStock = $available <= ($product->low_stock_threshold ?? 0);
@endphp

<div class="rounded-xl border border-gray-200 bg-white overflow-hidden flex flex-col" wire:key="product-card-{{ $product->id }}">
    <button type="button" wire:click="$dispatch('product.show', { productId: {{ $product->id }} })" class="aspect-square bg-gray-50 flex items-center justify-center">
        @if ($product->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
        @else
            <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        @endif
    </button>

    <div class="p-2.5 flex-1 flex flex-col gap-1">
        <p class="text-sm font-medium text-gray-900 line-clamp-2">{{ $product->name }}</p>
        <p class="text-sm font-semibold text-gray-900"><x-money :amount="$product->sale_price" /></p>

        <div class="mt-auto flex items-center justify-between pt-1.5">
            <x-status-badge
                :status="$lowStock ? 'red' : 'green'"
                :label="($available / 100) . ' ' . ($product->unit?->label() ?? '')"
            />

            <button
                type="button"
                wire:click="$dispatch('cart.add', { productId: {{ $product->id }} })"
                class="h-7 w-7 rounded-full bg-orange-600 text-white text-lg leading-none flex items-center justify-center"
                aria-label="Ajouter {{ $product->name }} au panier"
            >
                +
            </button>
        </div>
    </div>
</div>
