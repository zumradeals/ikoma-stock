<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum PaymentMethod: string
{
    use EnumValues;

    case CASH = 'CASH';
    case MOBILE_MONEY = 'MOBILE_MONEY';
    case BANK_TRANSFER = 'BANK_TRANSFER';
    case CHECK = 'CHECK';
    case CUSTOMER_CREDIT = 'CUSTOMER_CREDIT';
    case OTHER = 'OTHER';
}
