<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum ProductUnit: string
{
    use EnumValues;

    case UNIT = 'UNIT';
    case BAR = 'BAR';
    case TON = 'TON';
    case KG = 'KG';
    case BAG = 'BAG';
    case SHEET = 'SHEET';
    case METER = 'METER';
    case M3 = 'M3';
    case PLANK = 'PLANK';
    case PACK = 'PACK';
}
