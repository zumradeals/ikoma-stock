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

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Espèces',
            self::MOBILE_MONEY => 'Mobile Money',
            self::BANK_TRANSFER => 'Virement bancaire',
            self::CHECK => 'Chèque',
            self::CUSTOMER_CREDIT => 'Crédit client',
            self::OTHER => 'Autre',
        };
    }
}
