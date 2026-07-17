<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;
use App\Enums\Concerns\HasTransitions;

enum TransferStatus: string
{
    use EnumValues, HasTransitions;

    case DRAFT = 'DRAFT';
    case REQUESTED = 'REQUESTED';
    case ACCEPTED = 'ACCEPTED';
    case IN_PREPARATION = 'IN_PREPARATION';
    case SHIPPED = 'SHIPPED';
    case RECEIVED = 'RECEIVED';
    case PARTIALLY_RECEIVED = 'PARTIALLY_RECEIVED';
    case CANCELLED = 'CANCELLED';

    /**
     * Annulation possible uniquement avant expédition (DRAFT/REQUESTED/
     * ACCEPTED) : une fois IN_PREPARATION/SHIPPED, du stock a déjà bougé.
     * ACCEPTED -> SHIPPED direct est autorisé car TransferService n'expose
     * pas de méthode dédiée pour marquer IN_PREPARATION séparément —
     * ship() peut donc partir directement d'ACCEPTED, IN_PREPARATION
     * restant un état intermédiaire optionnel. PARTIALLY_RECEIVED ->
     * RECEIVED couvre la réception du reliquat.
     */
    public static function transitions(): array
    {
        return [
            self::DRAFT->value => [self::REQUESTED, self::CANCELLED],
            self::REQUESTED->value => [self::ACCEPTED, self::CANCELLED],
            self::ACCEPTED->value => [self::IN_PREPARATION, self::SHIPPED, self::CANCELLED],
            self::IN_PREPARATION->value => [self::SHIPPED],
            self::SHIPPED->value => [self::RECEIVED, self::PARTIALLY_RECEIVED],
            self::RECEIVED->value => [],
            self::PARTIALLY_RECEIVED->value => [self::RECEIVED],
            self::CANCELLED->value => [],
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::REQUESTED => 'Demandé',
            self::ACCEPTED => 'Accepté',
            self::IN_PREPARATION => 'En préparation',
            self::SHIPPED => 'Expédié',
            self::RECEIVED => 'Reçu',
            self::PARTIALLY_RECEIVED => 'Reçu en partie',
            self::CANCELLED => 'Annulé',
        };
    }
}
