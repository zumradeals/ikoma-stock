<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum LocationType: string
{
    use EnumValues;

    case WAREHOUSE = 'WAREHOUSE';
    case OUTLET = 'OUTLET';

    public function label(): string
    {
        return match ($this) {
            self::WAREHOUSE => 'Dépôt',
            self::OUTLET => 'Point de vente',
        };
    }
}
