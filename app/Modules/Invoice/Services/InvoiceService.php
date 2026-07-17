<?php

namespace App\Modules\Invoice\Services;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use App\Enums\PaymentMethod;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Stock\Services\StockService;
use App\Services\DocumentNumberGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function __construct(
        protected DocumentNumberGenerator $numberGenerator,
        protected PaymentService $paymentService,
        protected StockService $stockService,
    ) {}

    public function generateNumber(Company $company): string
    {
        return $this->numberGenerator->generate('invoices', $company->id, $company->invoice_prefix);
    }

    /**
     * Délègue à PaymentService::record() — pas de logique dupliquée, ce
     * n'est qu'un point d'entrée pratique depuis le contexte "facture".
     */
    public function recordPayment(Invoice $invoice, int $amount, PaymentMethod $method, array $attributes = []): Payment
    {
        return $this->paymentService->record($invoice, $amount, $method, $attributes);
    }

    /**
     * Job quotidien : toute facture non payée/non annulée, non livrée-annulée,
     * dont l'échéance est dépassée passe OVERDUE. $companyId restreint le
     * traitement à une seule entreprise (RefreshOverdueStatuses les isole
     * les unes des autres) ; null traite toutes les entreprises.
     */
    public function markOverdue(?int $companyId = null): int
    {
        return DB::table('invoices')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->whereNotIn('payment_status', [InvoicePaymentStatus::PAID->value, InvoicePaymentStatus::CANCELLED->value])
            ->where('delivery_status', '!=', InvoiceDeliveryStatus::CANCELLED->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['payment_status' => InvoicePaymentStatus::OVERDUE->value, 'updated_at' => now()]);
    }

    /**
     * Si la facture était (partiellement) payée : crée un avoir (paiement
     * négatif, voir PaymentService::refund()) pour remettre le solde à
     * zéro. Si la marchandise n'a pas encore été livrée : libère la
     * réservation de stock de la vente liée.
     */
    public function cancel(Invoice $invoice, string $reason): void
    {
        DB::transaction(function () use ($invoice, $reason) {
            if ($invoice->paid_amount > 0) {
                $this->paymentService->refund($invoice, $invoice->paid_amount, $reason);
                $invoice->refresh();
            }

            if (! in_array($invoice->delivery_status, [InvoiceDeliveryStatus::DELIVERED, InvoiceDeliveryStatus::CANCELLED], true)) {
                $this->stockService->releaseReservation($invoice->sale);
            }

            $invoice->update([
                'payment_status' => InvoicePaymentStatus::CANCELLED,
                'delivery_status' => InvoiceDeliveryStatus::CANCELLED,
            ]);
        });
    }

    public function generatePdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['sale.saleLines.product', 'company']);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);

        $path = "invoices/{$invoice->number}.pdf";
        Storage::put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);

        return $path;
    }
}
