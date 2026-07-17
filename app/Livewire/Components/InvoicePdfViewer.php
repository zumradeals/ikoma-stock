<?php

namespace App\Livewire\Components;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class InvoicePdfViewer extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->loadMissing(['sale.customer', 'sale.saleLines.product', 'company']);
    }

    public function getWhatsappUrlProperty(): ?string
    {
        $phone = $this->invoice->sale->customer?->phone;

        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        $message = sprintf(
            'Facture %s - Total : %s %s. Merci de votre confiance, %s.',
            $this->invoice->number,
            number_format($this->invoice->total_amount / 100, 0, ',', ' '),
            $this->invoice->company->currency,
            $this->invoice->company->name,
        );

        return 'https://wa.me/'.$digits.'?text='.urlencode($message);
    }

    public function render()
    {
        return view('livewire.components.invoice-pdf-viewer');
    }
}
