<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum CustomerType: string
{
    use EnumValues;

    case REGISTERED = 'REGISTERED';
    case PASSING = 'PASSING';
}
