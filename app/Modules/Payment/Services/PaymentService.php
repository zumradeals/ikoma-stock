<?php

namespace App\Modules\Payment\Services;

use App\Enums\InvoicePaymentStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\Business\PaymentExceedsBalanceException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Modules\Receivable\Services\ReceivableService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(protected ReceivableService $receivableService) {}

    /**
     * Transaction : paiement + MAJ facture (paid_amount/payment_status) +
     * MAJ créance liée.
     */
    public function record(Invoice $invoice, int $amount, PaymentMethod $method, array $attributes = [], bool $allowOverpayment = false): Payment
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Le montant du paiement doit être strictement positif.');
        }

        if (! $allowOverpayment && $amount > $invoice->balance_due) {
            throw new PaymentExceedsBalanceException($amount, $invoice->balance_due);
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $attributes) {
            $payment = Payment::create(array_merge([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $method,
                'payment_date' => now(),
                'user_id' => auth()->id(),
            ], $attributes));

            $this->applyToInvoice($invoice, $amount);

            return $payment;
        });
    }

    /**
     * Paiement d'appoint négatif ("avoir") utilisé par
     * InvoiceService::cancel() pour apurer le solde d'une facture déjà
     * payée qu'on annule.
     */
    public function refund(Invoice $invoice, int $amount, string $reason): Payment
    {
        $amount = min($amount, $invoice->paid_amount);

        return DB::transaction(function () use ($invoice, $amount, $reason) {
            $payment = Payment::create([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'amount' => -$amount,
                'method' => PaymentMethod::OTHER,
                'payment_date' => now(),
                'user_id' => auth()->id(),
                'note' => "Avoir — {$reason}",
            ]);

            $this->applyToInvoice($invoice, -$amount);

            return $payment;
        });
    }

    /**
     * paid_amount/balance_due sont bornés à [0, total_amount] : la table
     * invoices a des contraintes CHECK (paid_amount <= total_amount,
     * balance_due >= 0) qui interdisent d'y stocker un surpaiement brut. Le
     * `Payment` créé, lui, garde le montant réellement encaissé (aucune
     * contrainte sur payments.amount) — le surplus devient un avoir client
     * qui n'est pas modélisé plus loin dans ce périmètre.
     */
    protected function applyToInvoice(Invoice $invoice, int $delta): void
    {
        $invoice->paid_amount = max(0, min($invoice->total_amount, $invoice->paid_amount + $delta));
        $invoice->balance_due = $invoice->total_amount - $invoice->paid_amount;
        $invoice->payment_status = InvoicePaymentStatus::computeFor(
            $invoice->paid_amount,
            $invoice->total_amount,
            $invoice->due_date,
            $invoice->payment_status,
        );
        $invoice->save();

        $this->receivableService->syncFromInvoice($invoice);
    }
}
