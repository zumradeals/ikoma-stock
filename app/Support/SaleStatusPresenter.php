<?php

namespace App\Support;

/**
 * Calcule le statut d'affichage composite (§5.3 IKOMA_UX_ARCHITECTURE.md)
 * à partir de payment_status + delivery_status d'une facture.
 *
 * Ne stocke aucune donnée — uniquement de la présentation dérivée.
 */
class SaleStatusPresenter
{
    /**
     * Résout la clé de statut métier à passer à x-ikoma.status-badge.
     *
     * @param  string  $paymentStatus   Valeur de InvoicePaymentStatus (PAID, PARTIAL, UNPAID)
     * @param  string  $deliveryStatus  Valeur de InvoiceDeliveryStatus (DELIVERED, TO_PREPARE, READY, PARTIAL_DELIVERED)
     * @param  int     $totalAmount     Montant total de la facture en centimes (0 = vente offerte)
     * @param  bool    $isCancelled     Vrai si la vente est en état CANCELLED
     * @return string  Une des clés : paid_delivered | to_deliver | partial | free | unpaid | cancelled
     */
    public static function resolve(
        string $paymentStatus,
        string $deliveryStatus,
        int $totalAmount,
        bool $isCancelled = false,
    ): string {
        if ($isCancelled) {
            return 'cancelled';
        }

        if ($totalAmount === 0) {
            return 'free';
        }

        $payment  = strtoupper($paymentStatus);
        $delivery = strtoupper($deliveryStatus);

        if ($payment === 'PAID' && $delivery === 'DELIVERED') {
            return 'paid_delivered';
        }

        if ($payment === 'PAID') {
            return 'to_deliver';
        }

        if ($payment === 'PARTIAL') {
            return 'partial';
        }

        return 'unpaid';
    }
}
