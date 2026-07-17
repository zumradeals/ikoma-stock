<?php

namespace App\Enums\Concerns;

trait EnumValues
{
    /**
     * Toutes les valeurs brutes de l'enum, dans l'ordre de déclaration.
     * Source unique utilisée aussi bien par les casts Eloquent que par les
     * contraintes CHECK générées dans les migrations.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
