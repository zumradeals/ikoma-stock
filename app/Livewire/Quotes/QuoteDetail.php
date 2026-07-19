<?php

namespace App\Livewire\Quotes;

use App\Enums\QuoteStatus;
use App\Exceptions\Business\SaleValidationForbiddenException;
use App\Models\Quote;
use App\Modules\Quote\Services\QuoteService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class QuoteDetail extends Component
{
    public Quote $quote;

    public function mount(Quote $quote): void
    {
        $this->quote = $quote->load(['customer', 'outlet', 'user', 'quoteLines.product', 'convertedSale']);
    }

    public function getCanEditProperty(): bool
    {
        return ! $this->quote->status->isTerminal() && $this->quote->status === QuoteStatus::DRAFT;
    }

    public function markSent(QuoteService $service): void
    {
        try {
            $this->quote = $service->markSent($this->quote);
        } catch (SaleValidationForbiddenException $e) {
            $this->addError('action', $e->getMessage());
        }
    }

    public function markAccepted(QuoteService $service): void
    {
        try {
            $this->quote = $service->markAccepted($this->quote);
        } catch (SaleValidationForbiddenException $e) {
            $this->addError('action', $e->getMessage());
        }
    }

    public function markRefused(QuoteService $service): void
    {
        try {
            $this->quote = $service->markRefused($this->quote);
        } catch (SaleValidationForbiddenException $e) {
            $this->addError('action', $e->getMessage());
        }
    }

    public function requestConvert(): void
    {
        $this->dispatch(
            'confirm-action',
            title: 'Convertir en vente',
            message: "Convertir le devis {$this->quote->number} en vente réelle ?",
            detail: 'Le stock sera réservé et une facture sera générée. Cette action est irréversible.',
            danger: false,
            eventName: 'quote.convert.confirmed',
            eventParams: [],
        );
    }

    #[On('quote.convert.confirmed')]
    public function convert(QuoteService $service): void
    {
        try {
            $invoice = $service->convert($this->quote);
            $this->quote = $this->quote->fresh(['customer', 'outlet', 'user', 'quoteLines.product', 'convertedSale']);

            session()->flash('status', 'Devis converti en vente. Facture ' . $invoice->number . ' générée.');
        } catch (SaleValidationForbiddenException $e) {
            $this->addError('action', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.quotes.quote-detail');
    }
}
