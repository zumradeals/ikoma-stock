<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;
use App\Enums\Concerns\HasTransitions;

enum InvoiceDeliveryStatus: string
{
    use EnumValues, HasTransitions;

    case TO_PREPARE = 'TO_PREPARE';
    case READY = 'READY';
    case PARTIAL_DELIVERED = 'PARTIAL_DELIVERED';
    case DELIVERED = 'DELIVERED';
    case CANCELLED = 'CANCELLED';

    /**
     * markReady() est optionnel : une livraison peut être enregistrée
     * directement depuis TO_PREPARE. PARTIAL_DELIVERED -> PARTIAL_DELIVERED
     * couvre les livraisons partielles successives.
     */
    public static function transitions(): array
    {
        return [
            self::TO_PREPARE->value => [self::READY, self::PARTIAL_DELIVERED, self::DELIVERED, self::CANCELLED],
            self::READY->value => [self::PARTIAL_DELIVERED, self::DELIVERED, self::CANCELLED],
            self::PARTIAL_DELIVERED->value => [self::PARTIAL_DELIVERED, self::DELIVERED, self::CANCELLED],
            self::DELIVERED->value => [],
            self::CANCELLED->value => [],
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::TO_PREPARE => 'À préparer',
            self::READY => 'Prête',
            self::PARTIAL_DELIVERED => 'Livrée en partie',
            self::DELIVERED => 'Livrée',
            self::CANCELLED => 'Annulée',
        };
    }
}
