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
}
