<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case DECOUVERTE    = 'DECOUVERTE';
    case BOUTIQUE      = 'BOUTIQUE';
    case MULTI_BOUTIQUE = 'MULTI_BOUTIQUE';

    public function label(): string
    {
        return match ($this) {
            self::DECOUVERTE     => 'Découverte',
            self::BOUTIQUE       => 'Boutique',
            self::MULTI_BOUTIQUE => 'Multi-boutique',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DECOUVERTE     => 'bg-ink/10 text-ink-soft',
            self::BOUTIQUE       => 'bg-brand/10 text-brand',
            self::MULTI_BOUTIQUE => 'bg-gold/15 text-gold',
        };
    }
}
