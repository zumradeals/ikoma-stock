<?php

namespace Database\Factories\Concerns;

trait GeneratesIvorianData
{
    protected function ivorianPhone(): string
    {
        $groups = [];
        for ($i = 0; $i < 5; $i++) {
            $groups[] = str_pad((string) fake()->numberBetween(0, 99), 2, '0', STR_PAD_LEFT);
        }

        return '+225'.implode('', $groups);
    }

    protected function abidjanNeighborhood(): string
    {
        return fake()->randomElement([
            'Cocody', 'Yopougon', 'Marcory', 'Treichville', 'Adjamé',
            'Abobo', 'Koumassi', 'Plateau', 'Port-Bouët', 'Attécoubé',
            'Bingerville', 'Songon',
        ]);
    }
}
