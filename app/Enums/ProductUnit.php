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

    public function label(): string
    {
        return match ($this) {
            self::UNIT => 'Unité',
            self::BAR => 'Barre',
            self::TON => 'Tonne',
            self::KG => 'Kilogramme',
            self::BAG => 'Sac',
            self::SHEET => 'Tôle / Feuille',
            self::METER => 'Mètre',
            self::M3 => 'Mètre cube',
            self::PLANK => 'Planche',
            self::PACK => 'Paquet',
        };
    }
}
