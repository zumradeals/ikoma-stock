<?php

namespace App\Modules\Transfer\Services;

use App\Enums\TransferStatus;
use App\Models\Outlet;
use App\Models\Transfer;
use App\Models\TransferLine;
use App\Models\Warehouse;
use App\Modules\Stock\Services\StockService;
use App\Services\DocumentNumberGenerator;
use Illuminate\Support\Facades\DB;
use LogicException;

class TransferService
{
    public function __construct(
        protected StockService $stockService,
        protected DocumentNumberGenerator $numberGenerator,
    ) {}

    /**
     * $source/$destination : ['warehouse_id' => x] ou ['outlet_id' => x].
     * $lines : [product_id => requested_quantity].
     */
    public function createRequest(array $source, array $destination, array $lines): Transfer
    {
        $companyId = $this->resolveCompanyId($source);

        return DB::transaction(function () use ($source, $destination, $lines, $companyId) {
            $transfer = Transfer::create([
                'company_id' => $companyId,
                'number' => $this->numberGenerator->generate('transfers', $companyId, 'TRF'),
                'source_warehouse_id' => $source['warehouse_id'] ?? null,
                'source_outlet_id' => $source['outlet_id'] ?? null,
                'destination_warehouse_id' => $destination['warehouse_id'] ?? null,
                'destination_outlet_id' => $destination['outlet_id'] ?? null,
                'user_id' => auth()->id(),
                'status' => TransferStatus::REQUESTED,
                'total_quantity' => array_sum($lines),
                'request_date' => now(),
            ]);

            foreach ($lines as $productId => $quantity) {
                TransferLine::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'requested_quantity' => $quantity,
                ]);
            }

            return $transfer;
        });
    }

    public function accept(Transfer $transfer): void
    {
        $this->guardTransition($transfer, TransferStatus::ACCEPTED);
        $transfer->update(['status' => TransferStatus::ACCEPTED]);
    }

    /**
     * $shippedQtys = [product_id => quantité expédiée].
     */
    public function ship(Transfer $transfer, array $shippedQtys): void
    {
        $this->guardTransition($transfer, TransferStatus::SHIPPED);

        DB::transaction(function () use ($transfer, $shippedQtys) {
            foreach ($transfer->transferLines as $line) {
                $line->update(['shipped_quantity' => $shippedQtys[$line->product_id] ?? 0]);
            }

            $this->stockService->processTransfer($transfer, 'ship', $shippedQtys);

            $transfer->update([
                'status' => TransferStatus::SHIPPED,
                'shipped_quantity' => array_sum($shippedQtys),
                'ship_date' => now(),
            ]);
        });
    }

    /**
     * $receivedQtys = [product_id => quantité reçue LORS DE CET APPEL
     * (delta, pas cumul — permet plusieurs réceptions partielles
     * successives). Bascule RECEIVED quand toutes les lignes atteignent
     * leur quantité expédiée, sinon PARTIALLY_RECEIVED.
     */
    public function receive(Transfer $transfer, array $receivedQtys): void
    {
        if (! in_array($transfer->status, [TransferStatus::SHIPPED, TransferStatus::PARTIALLY_RECEIVED], true)) {
            throw new LogicException("Transfert non expédié : réception impossible depuis le statut {$transfer->status->value}.");
        }

        DB::transaction(function () use ($transfer, $receivedQtys) {
            foreach ($transfer->transferLines as $line) {
                $qty = $receivedQtys[$line->product_id] ?? 0;

                if ($qty > 0) {
                    $line->increment('received_quantity', $qty);
                }
            }

            $this->stockService->processTransfer($transfer, 'receive', $receivedQtys);

            $transfer->refresh();
            $lines = $transfer->transferLines()->get();
            $allReceived = $lines->every(fn ($line) => $line->received_quantity >= $line->shipped_quantity);

            $transfer->update([
                'status' => $allReceived ? TransferStatus::RECEIVED : TransferStatus::PARTIALLY_RECEIVED,
                'received_quantity' => $lines->sum('received_quantity'),
                'receive_date' => $allReceived ? now() : $transfer->receive_date,
            ]);
        });
    }

    public function cancel(Transfer $transfer, string $reason): void
    {
        $this->guardTransition($transfer, TransferStatus::CANCELLED);

        $transfer->update([
            'status' => TransferStatus::CANCELLED,
            'note' => trim(($transfer->note ? $transfer->note.' | ' : '')."Annulé : {$reason}"),
        ]);
    }

    protected function resolveCompanyId(array $location): int
    {
        if (isset($location['warehouse_id'])) {
            return Warehouse::withoutGlobalScopes()->findOrFail($location['warehouse_id'])->company_id;
        }

        return Outlet::withoutGlobalScopes()->findOrFail($location['outlet_id'])->company_id;
    }

    protected function guardTransition(Transfer $transfer, TransferStatus $target): void
    {
        if (! $transfer->status->canTransitionTo($target)) {
            throw new LogicException("Transition de transfert {$transfer->status->value} → {$target->value} non autorisée.");
        }
    }
}
