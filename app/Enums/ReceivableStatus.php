<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum ReceivableStatus: string
{
    use EnumValues;

    case OPEN = 'OPEN';
    case PARTIAL = 'PARTIAL';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Ouverte',
            self::PARTIAL => 'Partiellement payée',
            self::PAID => 'Payée',
            self::OVERDUE => 'En retard',
        };
    }
}
