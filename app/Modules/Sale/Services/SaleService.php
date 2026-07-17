<?php

namespace App\Modules\Sale\Services;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use App\Enums\SaleStatus;
use App\Exceptions\Business\SaleValidationForbiddenException;
use App\Exceptions\Business\UnauthorizedPriceModificationException;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Modules\Invoice\Services\InvoiceService;
use App\Modules\Receivable\Services\ReceivableService;
use App\Modules\Stock\Services\StockService;
use App\Services\DocumentNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SaleService
{
    public function __construct(
        protected StockService $stockService,
        protected InvoiceService $invoiceService,
        protected ReceivableService $receivableService,
        protected DocumentNumberGenerator $numberGenerator,
    ) {}

    public function createDraft(array $data): Sale
    {
        return DB::transaction(fn () => Sale::create([
            'company_id' => $data['company_id'],
            'number' => $this->numberGenerator->generate('sales', $data['company_id'], 'VTE'),
            'outlet_id' => $data['outlet_id'],
            'user_id' => $data['user_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'customer_type' => $data['customer_type'],
            'total_amount' => 0,
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'payment_method_primary' => $data['payment_method_primary'] ?? null,
            'status' => SaleStatus::DRAFT,
        ]));
    }

    public function addLine(Sale $sale, Product $product, int $quantity): SaleLine
    {
        $this->guardDraft($sale);

        return DB::transaction(function () use ($sale, $product, $quantity) {
            $unitPrice = $product->sale_price;
            $lineTotal = $unitPrice * $quantity;

            $line = SaleLine::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_discount' => 0,
                'line_total' => $lineTotal,
            ]);

            $sale->increment('total_amount', $lineTotal);

            return $line;
        });
    }

    public function applyDiscount(Sale $sale, int $amount = 0, int $percentage = 0): Sale
    {
        $this->guardDraft($sale);

        if (($amount > 0 || $percentage > 0) && Gate::denies('applyDiscount', $sale)) {
            throw new UnauthorizedPriceModificationException;
        }

        $discountAmount = $percentage > 0
            ? (int) round($sale->total_amount * $percentage / 100)
            : $amount;

        $sale->update([
            'discount_amount' => $discountAmount,
            'discount_percentage' => $percentage,
        ]);

        return $sale->fresh();
    }

    /**
     * Transaction : réserve le stock, génère la facture, crée la créance si
     * un solde reste dû sur un client identifié.
     */
    public function validate(Sale $sale): Invoice
    {
        $this->guardTransition($sale, SaleStatus::VALIDATED);

        return DB::transaction(function () use ($sale) {
            $sale->loadMissing('saleLines');

            $this->stockService->reserveForSale($sale);

            $netTotal = $sale->total_amount - $sale->discount_amount;

            $invoice = Invoice::create([
                'company_id' => $sale->company_id,
                'sale_id' => $sale->id,
                'number' => $this->invoiceService->generateNumber($sale->company),
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'total_amount' => $netTotal,
                'paid_amount' => 0,
                'balance_due' => $netTotal,
                'payment_status' => InvoicePaymentStatus::UNPAID,
                'delivery_status' => InvoiceDeliveryStatus::TO_PREPARE,
            ]);

            if ($invoice->balance_due > 0 && $sale->customer_id) {
                $this->receivableService->syncFromInvoice($invoice);
            }

            $sale->update(['status' => SaleStatus::VALIDATED]);

            return $invoice;
        });
    }

    /**
     * DRAFT : suppression directe (jamais de réservation à libérer).
     * VALIDATED : libère la réservation de stock, annule la facture liée.
     */
    public function cancel(Sale $sale, string $reason): void
    {
        if ($sale->status === SaleStatus::DRAFT) {
            DB::transaction(function () use ($sale) {
                $sale->saleLines()->delete();
                $sale->delete();
            });

            return;
        }

        $this->guardTransition($sale, SaleStatus::CANCELLED);

        if (Gate::denies('cancel', $sale)) {
            throw new SaleValidationForbiddenException('permission refusée pour annuler cette vente');
        }

        DB::transaction(function () use ($sale, $reason) {
            $this->stockService->releaseReservation($sale);

            if ($sale->invoice) {
                $this->invoiceService->cancel($sale->invoice, $reason);
            }

            $sale->update([
                'status' => SaleStatus::CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => $reason,
            ]);
        });
    }

    protected function guardDraft(Sale $sale): void
    {
        if ($sale->status !== SaleStatus::DRAFT) {
            throw new SaleValidationForbiddenException('seule une vente en brouillon (DRAFT) peut être modifiée');
        }
    }

    protected function guardTransition(Sale $sale, SaleStatus $target): void
    {
        if (! $sale->status->canTransitionTo($target)) {
            throw new SaleValidationForbiddenException("transition {$sale->status->value} → {$target->value} non autorisée");
        }
    }
}
