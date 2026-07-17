<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;
use App\Enums\Concerns\HasTransitions;

enum SaleStatus: string
{
    use EnumValues, HasTransitions;

    case DRAFT = 'DRAFT';
    case VALIDATED = 'VALIDATED';
    case CANCELLED = 'CANCELLED';

    /**
     * DRAFT n'a pas de transition vers CANCELLED : un brouillon n'a jamais
     * réservé de stock ni généré de facture, il se supprime directement
     * (voir SaleService::cancel()) plutôt que de passer par un état annulé.
     */
    public static function transitions(): array
    {
        return [
            self::DRAFT->value => [self::VALIDATED],
            self::VALIDATED->value => [self::CANCELLED],
            self::CANCELLED->value => [],
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::VALIDATED => 'Validée',
            self::CANCELLED => 'Annulée',
        };
    }
}
