<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum CustomerType: string
{
    use EnumValues;

    case REGISTERED = 'REGISTERED';
    case PASSING = 'PASSING';

    public function label(): string
    {
        return match ($this) {
            self::REGISTERED => 'Client enregistré',
            self::PASSING => 'Client de passage',
        };
    }
}
