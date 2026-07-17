<?php

namespace App\Livewire\Sales;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class SaleCart extends Component
{
    #[Reactive]
    public array $lines = [];

    public bool $fullPage = false;

    public function updateQuantity(int $productId, int $quantity): void
    {
        $this->dispatch('cart.update-quantity', productId: $productId, quantity: $quantity);
    }

    public function removeLine(int $productId): void
    {
        $this->dispatch('cart.remove', productId: $productId);
    }

    public function chooseCustomer(): void
    {
        $this->dispatch('cart.choose-customer');
    }

    public function checkout(): void
    {
        $this->dispatch('cart.checkout');
    }

    public function getTotalProperty(): int
    {
        return collect($this->lines)->sum(fn (array $line) => $line['unit_price'] * $line['quantity']);
    }

    public function render()
    {
        return view('livewire.sales.sale-cart');
    }
}
