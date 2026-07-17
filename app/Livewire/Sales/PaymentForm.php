<?php

namespace App\Livewire\Sales;

use App\Enums\PaymentMethod;
use App\Exceptions\Business\BusinessException;
use App\Models\Sale;
use App\Modules\Payment\Services\PaymentService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PaymentForm extends Component
{
    use WithFileUploads;

    public Sale $sale;

    public int $amount = 0;

    public string $method = 'CASH';

    public ?string $reference = null;

    public $proof = null;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->loadMissing('invoice.payments');
    }

    public function getInvoiceProperty()
    {
        return $this->sale->invoice;
    }

    public function getMethodsProperty(): array
    {
        return array_filter(PaymentMethod::cases(), fn (PaymentMethod $m) => $m !== PaymentMethod::CUSTOMER_CREDIT);
    }

    public function save(): void
    {
        $this->validate([
            'amount' => 'required|integer|min:1',
            'method' => 'required|string',
            'reference' => 'nullable|string|max:100',
            'proof' => 'nullable|image|max:4096',
        ]);

        $proofPath = $this->proof?->store('payment-proofs', 'public');

        try {
            app(PaymentService::class)->record(
                $this->invoice,
                $this->amount * 100,
                PaymentMethod::from($this->method),
                array_filter([
                    'reference' => $this->reference,
                    'proof_path' => $proofPath,
                ]),
            );
        } catch (BusinessException $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        session()->flash('status', 'Paiement enregistré.');
        $this->redirect(route('sales.show', $this->sale), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.payment-form');
    }
}
