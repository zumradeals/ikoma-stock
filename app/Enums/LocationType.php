<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum LocationType: string
{
    use EnumValues;

    case WAREHOUSE = 'WAREHOUSE';
    case OUTLET = 'OUTLET';
}
