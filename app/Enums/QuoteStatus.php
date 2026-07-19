<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;
use App\Enums\Concerns\HasTransitions;

enum QuoteStatus: string
{
    use EnumValues, HasTransitions;

    case DRAFT     = 'DRAFT';
    case SENT      = 'SENT';
    case ACCEPTED  = 'ACCEPTED';
    case REFUSED   = 'REFUSED';
    case EXPIRED   = 'EXPIRED';
    case CONVERTED = 'CONVERTED';

    public static function transitions(): array
    {
        return [
            self::DRAFT->value     => [self::SENT, self::ACCEPTED, self::REFUSED, self::EXPIRED, self::CONVERTED],
            self::SENT->value      => [self::ACCEPTED, self::REFUSED, self::EXPIRED, self::CONVERTED],
            self::ACCEPTED->value  => [self::CONVERTED, self::REFUSED],
            self::REFUSED->value   => [],
            self::EXPIRED->value   => [],
            self::CONVERTED->value => [],
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Brouillon',
            self::SENT      => 'Envoyé',
            self::ACCEPTED  => 'Accepté',
            self::REFUSED   => 'Refusé',
            self::EXPIRED   => 'Expiré',
            self::CONVERTED => 'Converti',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT     => 'bg-gray-100 text-gray-500',
            self::SENT      => 'bg-blue-100 text-blue-700',
            self::ACCEPTED  => 'bg-green-100 text-green-700',
            self::REFUSED   => 'bg-red-100 text-red-600',
            self::EXPIRED   => 'bg-yellow-100 text-yellow-700',
            self::CONVERTED => 'bg-brand/10 text-brand',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::REFUSED, self::EXPIRED, self::CONVERTED], true);
    }
}
