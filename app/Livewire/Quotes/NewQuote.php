<?php

namespace App\Livewire\Quotes;

use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Modules\Quote\Services\QuoteService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class NewQuote extends Component
{
    public array $lines = [];

    public string $productSearch = '';

    public ?int $customerId = null;

    public string $customerSearch = '';

    public string $validUntil = '';

    public string $discountType = '';

    public string $discountValue = '';

    public string $notes = '';

    public bool $showProductResults = false;

    public bool $showCustomerResults = false;

    public function getProductResultsProperty()
    {
        if (strlen($this->productSearch) < 2) {
            return collect();
        }

        return Product::where('name', 'like', "%{$this->productSearch}%")
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'sale_price', 'unit']);
    }

    public function getCustomerResultsProperty()
    {
        if (strlen($this->customerSearch) < 2) {
            return collect();
        }

        return Customer::where(function ($q) {
            $q->where('name', 'like', "%{$this->customerSearch}%")
              ->orWhere('phone', 'like', "%{$this->customerSearch}%");
        })
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'name', 'phone']);
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
    }

    public function getCartTotalProperty(): int
    {
        return array_sum(array_column($this->lines, 'line_total'));
    }

    public function getDiscountTotalProperty(): int
    {
        if (! $this->discountValue || ! $this->discountType) {
            return 0;
        }

        $value = (float) $this->discountValue;

        if ($this->discountType === 'percentage') {
            return (int) round($this->cartTotal * min($value, 100) / 100);
        }

        return min((int) round($value * 100), $this->cartTotal);
    }

    public function getNetTotalProperty(): int
    {
        return max(0, $this->cartTotal - $this->discountTotal);
    }

    public function selectProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $existing = collect($this->lines)->search(fn ($l) => $l['product_id'] === $productId);

        if ($existing !== false) {
            $this->lines[$existing]['quantity']++;
            $this->lines[$existing]['line_total'] = $this->lines[$existing]['unit_price'] * $this->lines[$existing]['quantity'];
        } else {
            $this->lines[] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'unit'       => $product->unit->value ?? '',
                'quantity'   => 1,
                'unit_price' => $product->sale_price,
                'line_total' => $product->sale_price,
            ];
        }

        $this->productSearch = '';
        $this->showProductResults = false;
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeLine($index);

            return;
        }

        $this->lines[$index]['quantity']   = $quantity;
        $this->lines[$index]['line_total'] = $this->lines[$index]['unit_price'] * $quantity;
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customerId       = $customerId;
        $this->customerSearch   = '';
        $this->showCustomerResults = false;
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
    }

    public function save(QuoteService $service): void
    {
        $this->validate([
            'lines'         => 'required|array|min:1',
            'validUntil'    => 'nullable|date|after_or_equal:today',
            'discountValue' => 'nullable|numeric|min:0',
        ], [
            'lines.required' => 'Ajoutez au moins un produit au devis.',
            'lines.min'      => 'Ajoutez au moins un produit au devis.',
        ]);

        $user = auth()->user();

        $quote = $service->create([
            'company_id'    => $user->company_id,
            'outlet_id'     => $user->outlet_id,
            'user_id'       => $user->id,
            'customer_id'   => $this->customerId,
            'customer_type' => $this->customerId ? CustomerType::REGISTERED : CustomerType::PASSING,
            'valid_until'   => $this->validUntil ?: null,
            'notes'         => $this->notes ?: null,
        ]);

        foreach ($this->lines as $line) {
            $product = Product::findOrFail($line['product_id']);
            $service->addLine($quote, $product, $line['quantity']);
        }

        if ($this->discountValue && $this->discountType) {
            $value = (float) $this->discountValue;

            if ($this->discountType === 'percentage') {
                $service->applyDiscount($quote->fresh(), 0, (int) $value);
            } else {
                $service->applyDiscount($quote->fresh(), (int) round($value * 100), 0);
            }
        }

        $this->redirect(route('quotes.show', $quote), navigate: true);
    }

    public function render()
    {
        return view('livewire.quotes.new-quote');
    }
}
