<?php

namespace App\Enums\Concerns;

trait HasTransitions
{
    /**
     * @return array<string, list<self>>
     */
    abstract public static function transitions(): array;

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, static::transitions()[$this->value] ?? [], true);
    }
}
