<div class="{{ $fullPage ? '' : 'border-t border-gray-100 bg-white' }}">
    @if (empty($lines))
        <p class="text-center text-sm text-gray-400 py-6">Panier vide.</p>
    @else
        <div class="divide-y divide-gray-100">
            @foreach ($lines as $productId => $line)
                <div class="flex items-center gap-3 px-3 py-2.5" wire:key="cart-line-{{ $productId }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $line['name'] }}</p>
                        <p class="text-xs text-gray-500"><x-money :amount="$line['unit_price']" /> / {{ $line['unit'] }}</p>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="updateQuantity({{ $productId }}, {{ $line['quantity'] - 1 }})" class="h-7 w-7 rounded-full bg-gray-100 text-gray-600">−</button>
                        <span class="w-6 text-center text-sm">{{ $line['quantity'] }}</span>
                        <button type="button" wire:click="updateQuantity({{ $productId }}, {{ $line['quantity'] + 1 }})" class="h-7 w-7 rounded-full bg-gray-100 text-gray-600">+</button>
                    </div>

                    <p class="w-20 text-right text-sm font-semibold text-gray-900">
                        <x-money :amount="$line['unit_price'] * $line['quantity']" />
                    </p>

                    <button type="button" wire:click="removeLine({{ $productId }})" class="text-gray-300" aria-label="Retirer">✕</button>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between px-3 py-3 border-t border-gray-100">
            <span class="text-sm text-gray-500">Total</span>
            <span class="text-lg font-semibold text-gray-900"><x-money :amount="$this->total" /></span>
        </div>

        <div class="flex gap-2 px-3 pb-3">
            <button type="button" wire:click="chooseCustomer" class="flex-1 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium py-2.5">
                Choisir client
            </button>
            <button type="button" wire:click="checkout" class="flex-1 rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5">
                Passer au paiement
            </button>
        </div>
    @endif
</div>
