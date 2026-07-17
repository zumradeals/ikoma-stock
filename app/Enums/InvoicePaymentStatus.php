<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;
use Carbon\Carbon;

enum InvoicePaymentStatus: string
{
    use EnumValues;

    case UNPAID = 'UNPAID';
    case PARTIAL = 'PARTIAL';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';
    case CANCELLED = 'CANCELLED';

    /**
     * Contrairement aux autres enums de ce module, InvoicePaymentStatus
     * n'est pas piloté par des transitions manuelles : il est recalculé à
     * chaque changement de paid_amount/due_date. CANCELLED est le seul
     * état manuel, et gagne toujours (une facture annulée ne "redevient"
     * jamais UNPAID/PARTIAL/PAID/OVERDUE automatiquement).
     */
    public static function computeFor(int $paidAmount, int $totalAmount, ?Carbon $dueDate, self $current): self
    {
        if ($current === self::CANCELLED) {
            return self::CANCELLED;
        }

        if ($paidAmount >= $totalAmount) {
            return self::PAID;
        }

        if ($dueDate !== null && $dueDate->isPast()) {
            return self::OVERDUE;
        }

        if ($paidAmount > 0) {
            return self::PARTIAL;
        }

        return self::UNPAID;
    }

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Non payée',
            self::PARTIAL => 'Paiement partiel',
            self::PAID => 'Payée',
            self::OVERDUE => 'En retard',
            self::CANCELLED => 'Annulée',
        };
    }
}
