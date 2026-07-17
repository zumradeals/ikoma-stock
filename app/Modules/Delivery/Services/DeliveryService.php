<?php

namespace App\Modules\Delivery\Services;

use App\Enums\InvoiceDeliveryStatus;
use App\Exceptions\Business\DeliveryExceedsOrderedQuantityException;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\Invoice;
use App\Modules\Stock\Services\StockService;
use Illuminate\Support\Facades\DB;
use LogicException;

class DeliveryService
{
    public function __construct(protected StockService $stockService) {}

    public function markReady(Invoice $invoice): void
    {
        $this->guardTransition($invoice, InvoiceDeliveryStatus::READY);

        $invoice->update(['delivery_status' => InvoiceDeliveryStatus::READY]);
    }

    /**
     * $lines = [sale_line_id => quantité à livrer maintenant]. Totale ou
     * partielle selon que toutes les lignes atteignent leur quantité
     * commandée ou non.
     */
    public function deliver(Invoice $invoice, array $lines): Delivery
    {
        $saleLines = $invoice->sale->saleLines->keyBy('id');

        foreach ($lines as $saleLineId => $quantity) {
            $saleLine = $saleLines->get($saleLineId);
            $remaining = $saleLine->remainingToDeliver();

            if ($quantity > $remaining) {
                throw new DeliveryExceedsOrderedQuantityException($quantity, $remaining);
            }
        }

        return DB::transaction(function () use ($invoice, $lines, $saleLines) {
            $delivery = Delivery::create([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'delivered_at' => now(),
            ]);

            foreach ($lines as $saleLineId => $quantity) {
                if ($quantity <= 0) {
                    continue;
                }

                $saleLine = $saleLines->get($saleLineId);

                DeliveryLine::create([
                    'delivery_id' => $delivery->id,
                    'sale_line_id' => $saleLine->id,
                    'product_id' => $saleLine->product_id,
                    'quantity_delivered' => $quantity,
                ]);

                $saleLine->increment('delivered_quantity', $quantity);
            }

            $delivery->load('deliveryLines.product');
            $this->stockService->confirmDelivery($delivery);

            $this->updateInvoiceDeliveryStatus($invoice->fresh(), $invoice->sale->fresh('saleLines'));

            return $delivery;
        });
    }

    protected function updateInvoiceDeliveryStatus(Invoice $invoice, \App\Models\Sale $sale): void
    {
        $saleLines = $sale->saleLines;
        $allDelivered = $saleLines->every(fn ($line) => $line->delivered_quantity >= $line->quantity);
        $anyDelivered = $saleLines->contains(fn ($line) => $line->delivered_quantity > 0);

        $target = $allDelivered
            ? InvoiceDeliveryStatus::DELIVERED
            : ($anyDelivered ? InvoiceDeliveryStatus::PARTIAL_DELIVERED : $invoice->delivery_status);

        $invoice->update(['delivery_status' => $target]);
    }

    protected function guardTransition(Invoice $invoice, InvoiceDeliveryStatus $target): void
    {
        if (! $invoice->delivery_status->canTransitionTo($target)) {
            throw new LogicException("Transition de livraison {$invoice->delivery_status->value} → {$target->value} non autorisée.");
        }
    }
}
