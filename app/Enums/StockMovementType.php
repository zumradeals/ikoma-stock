<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum StockMovementType: string
{
    use EnumValues;

    case INITIAL_ENTRY = 'INITIAL_ENTRY';
    case SUPPLY = 'SUPPLY';
    case SALE_DELIVERY = 'SALE_DELIVERY';
    case OLD_SALE_DELIVERY = 'OLD_SALE_DELIVERY';
    case TRANSFER_OUT = 'TRANSFER_OUT';
    case TRANSFER_IN = 'TRANSFER_IN';
    case CUSTOMER_RETURN = 'CUSTOMER_RETURN';
    case LOSS = 'LOSS';
    case BREAKAGE = 'BREAKAGE';
    case INVENTORY_CORRECTION = 'INVENTORY_CORRECTION';
    case AUTHORIZED_CANCELLATION = 'AUTHORIZED_CANCELLATION';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL_ENTRY => 'Stock initial',
            self::SUPPLY => 'Approvisionnement',
            self::SALE_DELIVERY => 'Livraison vente',
            self::OLD_SALE_DELIVERY => 'Livraison (ancienne vente)',
            self::TRANSFER_OUT => 'Sortie (transfert)',
            self::TRANSFER_IN => 'Entrée (transfert)',
            self::CUSTOMER_RETURN => 'Retour client',
            self::LOSS => 'Perte',
            self::BREAKAGE => 'Casse',
            self::INVENTORY_CORRECTION => 'Correction de stock',
            self::AUTHORIZED_CANCELLATION => 'Annulation autorisée',
        };
    }
}
