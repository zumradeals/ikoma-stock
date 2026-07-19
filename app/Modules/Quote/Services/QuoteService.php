<?php

namespace App\Modules\Quote\Services;

use App\Enums\CustomerType;
use App\Enums\QuoteStatus;
use App\Exceptions\Business\SaleValidationForbiddenException;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Modules\Sale\Services\SaleService;
use App\Services\DocumentNumberGenerator;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        protected SaleService $saleService,
        protected DocumentNumberGenerator $numberGenerator,
    ) {}

    public function create(array $data): Quote
    {
        return DB::transaction(fn () => Quote::create([
            'company_id'          => $data['company_id'],
            'number'              => $this->numberGenerator->generate('quotes', $data['company_id'], 'DEV'),
            'outlet_id'           => $data['outlet_id'],
            'user_id'             => $data['user_id'],
            'customer_id'         => $data['customer_id'] ?? null,
            'customer_type'       => $data['customer_type'] ?? CustomerType::PASSING,
            'valid_until'         => $data['valid_until'] ?? null,
            'total_amount'        => 0,
            'discount_amount'     => 0,
            'discount_percentage' => 0,
            'status'              => QuoteStatus::DRAFT,
            'notes'               => $data['notes'] ?? null,
        ]));
    }

    public function addLine(Quote $quote, Product $product, int $quantity): QuoteLine
    {
        $this->guardEditable($quote);

        return DB::transaction(function () use ($quote, $product, $quantity) {
            $unitPrice = $product->sale_price;
            $lineTotal = $unitPrice * $quantity;

            $line = QuoteLine::create([
                'quote_id'      => $quote->id,
                'product_id'    => $product->id,
                'quantity'      => $quantity,
                'unit_price'    => $unitPrice,
                'line_discount' => 0,
                'line_total'    => $lineTotal,
            ]);

            $quote->increment('total_amount', $lineTotal);

            return $line;
        });
    }

    public function updateLine(QuoteLine $line, int $quantity): QuoteLine
    {
        $this->guardEditable($line->quote);

        return DB::transaction(function () use ($line, $quantity) {
            $oldTotal = $line->line_total;
            $newTotal = $line->unit_price * $quantity;

            $line->update([
                'quantity'   => $quantity,
                'line_total' => $newTotal,
            ]);

            $line->quote->increment('total_amount', $newTotal - $oldTotal);

            return $line->fresh();
        });
    }

    public function removeLine(QuoteLine $line): void
    {
        $this->guardEditable($line->quote);

        DB::transaction(function () use ($line) {
            $line->quote->decrement('total_amount', $line->line_total);
            $line->delete();
        });
    }

    public function applyDiscount(Quote $quote, int $amount = 0, int $percentage = 0): Quote
    {
        $this->guardEditable($quote);

        $discountAmount = $percentage > 0
            ? (int) round($quote->total_amount * $percentage / 100)
            : $amount;

        $quote->update([
            'discount_amount'     => $discountAmount,
            'discount_percentage' => $percentage,
        ]);

        return $quote->fresh();
    }

    public function markSent(Quote $quote): Quote
    {
        $this->guardTransition($quote, QuoteStatus::SENT);
        $quote->update(['status' => QuoteStatus::SENT]);

        return $quote->fresh();
    }

    public function markAccepted(Quote $quote): Quote
    {
        $this->guardTransition($quote, QuoteStatus::ACCEPTED);
        $quote->update(['status' => QuoteStatus::ACCEPTED]);

        return $quote->fresh();
    }

    public function markRefused(Quote $quote): Quote
    {
        $this->guardTransition($quote, QuoteStatus::REFUSED);
        $quote->update(['status' => QuoteStatus::REFUSED]);

        return $quote->fresh();
    }

    public function markExpired(Quote $quote): Quote
    {
        $this->guardTransition($quote, QuoteStatus::EXPIRED);
        $quote->update(['status' => QuoteStatus::EXPIRED]);

        return $quote->fresh();
    }

    /**
     * Convert a quote into a validated sale. Stock is reserved only at this
     * point — never during quote creation or editing.
     */
    public function convert(Quote $quote): Invoice
    {
        $this->guardTransition($quote, QuoteStatus::CONVERTED);

        return DB::transaction(function () use ($quote) {
            $quote->loadMissing('quoteLines.product');

            $sale = $this->saleService->createDraft([
                'company_id'    => $quote->company_id,
                'outlet_id'     => $quote->outlet_id,
                'user_id'       => auth()->id(),
                'customer_id'   => $quote->customer_id,
                'customer_type' => $quote->customer_type,
            ]);

            foreach ($quote->quoteLines as $line) {
                $this->saleService->addLine($sale, $line->product, $line->quantity);
            }

            if ($quote->discount_percentage > 0) {
                $this->saleService->applyDiscount($sale->fresh(), 0, $quote->discount_percentage);
            } elseif ($quote->discount_amount > 0) {
                $this->saleService->applyDiscount($sale->fresh(), $quote->discount_amount, 0);
            }

            $invoice = $this->saleService->validate($sale->fresh());

            $quote->update([
                'status'            => QuoteStatus::CONVERTED,
                'converted_sale_id' => $sale->id,
                'converted_at'      => now(),
                'converted_by'      => auth()->id(),
            ]);

            return $invoice;
        });
    }

    protected function guardEditable(Quote $quote): void
    {
        if ($quote->status->isTerminal()) {
            throw new SaleValidationForbiddenException("un devis {$quote->status->value} ne peut plus être modifié");
        }
    }

    protected function guardTransition(Quote $quote, QuoteStatus $target): void
    {
        if (! $quote->status->canTransitionTo($target)) {
            throw new SaleValidationForbiddenException("transition {$quote->status->value} → {$target->value} non autorisée");
        }
    }
}
