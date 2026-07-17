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
     * @param  string  $paymentStatus   Valeur de InvoicePaymentStatus (PAID, PARTIAL, UNPAID, FREE…)
     * @param  string  $deliveryStatus  Valeur de DeliveryStatus (DELIVERED, TO_PREPARE, READY, PARTIAL_DELIVERED…)
     * @param  bool    $isCancelled     Vrai si la vente est en état CANCELLED
     * @return string  Une des clés : paid_delivered | to_deliver | partial | free | unpaid | cancelled
     */
    public static function resolve(
        string $paymentStatus,
        string $deliveryStatus,
        bool $isCancelled = false,
    ): string {
        if ($isCancelled) {
            return 'cancelled';
        }

        $payment  = strtoupper($paymentStatus);
        $delivery = strtoupper($deliveryStatus);

        if ($payment === 'FREE' || ($payment === 'PAID' && $delivery === 'FREE')) {
            return 'free';
        }

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
