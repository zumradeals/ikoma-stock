<?php

namespace App\Modules\Stock\Services;

use App\Enums\LocationType;
use App\Enums\StockMovementType;
use App\Exceptions\Business\InsufficientStockException;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Transfer;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Point de passage UNIQUE pour toute modification de quantité de stock.
 * Aucun autre service ne doit écrire directement dans StockLevel ou créer
 * un StockMovement.
 */
class StockService
{
    public function getAvailableQuantity(Product $product, LocationType $locationType, int $locationId): int
    {
        $level = StockLevel::query()
            ->where('product_id', $product->id)
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->first();

        return $level ? $level->quantity_physical - $level->quantity_reserved : 0;
    }

    public function reserveForSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->saleLines as $line) {
                $level = $this->lockLevel($line->product, LocationType::OUTLET, $sale->outlet_id);
                $available = $level->quantity_physical - $level->quantity_reserved;

                if ($available < $line->quantity) {
                    throw new InsufficientStockException($line->product, $available, $line->quantity);
                }

                $level->increment('quantity_reserved', $line->quantity);
            }
        });
    }

    public function releaseReservation(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->saleLines as $line) {
                $level = $this->lockLevel($line->product, LocationType::OUTLET, $sale->outlet_id);
                $level->decrement('quantity_reserved', min($line->quantity, $level->quantity_reserved));
            }
        });
    }

    public function confirmDelivery(Delivery $delivery): void
    {
        DB::transaction(function () use ($delivery) {
            $sale = $delivery->invoice->sale;

            foreach ($delivery->deliveryLines as $deliveryLine) {
                $level = $this->lockLevel($deliveryLine->product, LocationType::OUTLET, $sale->outlet_id);

                $level->decrement('quantity_physical', $deliveryLine->quantity_delivered);
                $level->decrement('quantity_reserved', min($deliveryLine->quantity_delivered, $level->quantity_reserved));

                StockMovement::create([
                    'company_id' => $delivery->company_id,
                    'product_id' => $deliveryLine->product_id,
                    'movement_type' => StockMovementType::SALE_DELIVERY,
                    'quantity' => $deliveryLine->quantity_delivered,
                    'location_source_type' => LocationType::OUTLET,
                    'location_source_id' => $sale->outlet_id,
                    'reason' => 'Livraison facture '.$delivery->invoice->number,
                    'user_id' => $delivery->user_id,
                    'movement_date' => $delivery->delivered_at,
                    'document_type' => 'Delivery',
                    'document_id' => $delivery->id,
                ]);
            }
        });
    }

    /**
     * $quantities = [product_id => quantité de CETTE opération]. Nécessaire
     * (au-delà de la signature du brief) pour gérer correctement les
     * réceptions partielles successives : le service ne doit déplacer que
     * le delta de cet appel, jamais le cumul stocké sur la ligne.
     */
    public function processTransfer(Transfer $transfer, string $direction, array $quantities): void
    {
        throw_unless(in_array($direction, ['ship', 'receive'], true), new \InvalidArgumentException("Direction invalide : {$direction}"));

        DB::transaction(function () use ($transfer, $direction, $quantities) {
            foreach ($transfer->transferLines as $line) {
                $quantity = $quantities[$line->product_id] ?? 0;

                if ($quantity <= 0) {
                    continue;
                }

                if ($direction === 'ship') {
                    [$type, $id] = $this->sourceLocation($transfer);
                    $level = $this->lockLevel($line->product, $type, $id);
                    $available = $level->quantity_physical - $level->quantity_reserved;

                    if ($available < $quantity) {
                        throw new InsufficientStockException($line->product, $available, $quantity);
                    }

                    $level->decrement('quantity_physical', $quantity);

                    StockMovement::create([
                        'company_id' => $transfer->company_id,
                        'product_id' => $line->product_id,
                        'movement_type' => StockMovementType::TRANSFER_OUT,
                        'quantity' => $quantity,
                        'location_source_type' => $type,
                        'location_source_id' => $id,
                        'reason' => 'Transfert '.$transfer->number,
                        'user_id' => $transfer->user_id,
                        'movement_date' => now(),
                        'document_type' => 'Transfer',
                        'document_id' => $transfer->id,
                    ]);
                } else {
                    [$type, $id] = $this->destinationLocation($transfer);
                    $level = $this->lockLevel($line->product, $type, $id);
                    $level->increment('quantity_physical', $quantity);

                    StockMovement::create([
                        'company_id' => $transfer->company_id,
                        'product_id' => $line->product_id,
                        'movement_type' => StockMovementType::TRANSFER_IN,
                        'quantity' => $quantity,
                        'location_destination_type' => $type,
                        'location_destination_id' => $id,
                        'reason' => 'Transfert '.$transfer->number,
                        'user_id' => $transfer->user_id,
                        'movement_date' => now(),
                        'document_type' => 'Transfer',
                        'document_id' => $transfer->id,
                    ]);
                }
            }
        });
    }

    /**
     * Stock de départ d'un produit fraîchement créé (un seul emplacement,
     * quantité forcément positive) — distinct de createInventoryCorrection()
     * pour que l'historique des mouvements distingue clairement un stock
     * initial d'une correction d'inventaire ultérieure.
     */
    public function recordInitialStock(Product $product, LocationType $locationType, int $locationId, int $quantity): void
    {
        DB::transaction(function () use ($product, $locationType, $locationId, $quantity) {
            $level = $this->lockLevel($product, $locationType, $locationId);
            $level->increment('quantity_physical', $quantity);

            StockMovement::create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'movement_type' => StockMovementType::INITIAL_ENTRY,
                'quantity' => $quantity,
                'location_destination_type' => $locationType,
                'location_destination_id' => $locationId,
                'reason' => 'Stock initial',
                'user_id' => auth()->id(),
                'movement_date' => now(),
            ]);
        });
    }

    /**
     * $quantity peut être négatif (correction à la baisse) ou positif
     * (correction à la hausse).
     */
    public function createInventoryCorrection(Product $product, LocationType $locationType, int $locationId, int $quantity, string $reason): void
    {
        DB::transaction(function () use ($product, $locationType, $locationId, $quantity, $reason) {
            $level = $this->lockLevel($product, $locationType, $locationId);
            $level->quantity_physical += $quantity;
            $level->save();

            StockMovement::create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'movement_type' => StockMovementType::INVENTORY_CORRECTION,
                'quantity' => abs($quantity),
                'location_destination_type' => $quantity >= 0 ? $locationType : null,
                'location_destination_id' => $quantity >= 0 ? $locationId : null,
                'location_source_type' => $quantity < 0 ? $locationType : null,
                'location_source_id' => $quantity < 0 ? $locationId : null,
                'reason' => $reason,
                'user_id' => auth()->id(),
                'movement_date' => now(),
            ]);
        });
    }

    protected function lockLevel(Product $product, LocationType $locationType, int $locationId): StockLevel
    {
        $query = fn () => StockLevel::query()
            ->where('product_id', $product->id)
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->lockForUpdate();

        $level = $query()->first();

        if ($level) {
            return $level;
        }

        try {
            return StockLevel::create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'location_type' => $locationType,
                'location_id' => $locationId,
                'quantity_physical' => 0,
                'quantity_reserved' => 0,
            ]);
        } catch (QueryException) {
            // Course concurrente : une autre transaction a créé la ligne
            // entre le SELECT et l'INSERT — on la relit verrouillée.
            return $query()->firstOrFail();
        }
    }

    protected function sourceLocation(Transfer $transfer): array
    {
        return $transfer->source_warehouse_id
            ? [LocationType::WAREHOUSE, $transfer->source_warehouse_id]
            : [LocationType::OUTLET, $transfer->source_outlet_id];
    }

    protected function destinationLocation(Transfer $transfer): array
    {
        return $transfer->destination_warehouse_id
            ? [LocationType::WAREHOUSE, $transfer->destination_warehouse_id]
            : [LocationType::OUTLET, $transfer->destination_outlet_id];
    }
}
