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
}
