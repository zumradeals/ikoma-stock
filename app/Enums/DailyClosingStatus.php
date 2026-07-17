<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;
use App\Enums\Concerns\HasTransitions;

enum DailyClosingStatus: string
{
    use EnumValues, HasTransitions;

    case OPEN = 'OPEN';
    case PENDING_VALIDATION = 'PENDING_VALIDATION';
    case VALIDATED = 'VALIDATED';
    case REJECTED = 'REJECTED';

    /**
     * VALIDATED est terminal et verrouillé (DailyClosingLockedException).
     * REJECTED reste modifiable : resoumission directe en
     * PENDING_VALIDATION via DailyClosingService::submitForValidation().
     */
    public static function transitions(): array
    {
        return [
            self::OPEN->value => [self::PENDING_VALIDATION],
            self::PENDING_VALIDATION->value => [self::VALIDATED, self::REJECTED],
            self::VALIDATED->value => [],
            self::REJECTED->value => [self::PENDING_VALIDATION],
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'En cours',
            self::PENDING_VALIDATION => 'En attente de validation',
            self::VALIDATED => 'Validée',
            self::REJECTED => 'Rejetée',
        };
    }
}
