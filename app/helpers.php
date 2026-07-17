<?php

if (! function_exists('hex_to_hsl')) {
    /**
     * Convertit un hex (#rrggbb) en tableau [h°, s%, l%].
     */
    function hex_to_hsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];

        $max   = max($r, $g, $b);
        $min   = min($r, $g, $b);
        $delta = $max - $min;
        $l     = ($max + $min) / 2;

        if ($delta === 0.0) {
            return [0, 0, round($l * 100)];
        }

        $s = $delta / (1 - abs(2 * $l - 1));

        $h = match ($max) {
            $r      => 60 * fmod(($g - $b) / $delta, 6),
            $g      => 60 * (($b - $r) / $delta + 2),
            default => 60 * (($r - $g) / $delta + 4),
        };

        if ($h < 0) {
            $h += 360;
        }

        return [round($h), round($s * 100), round($l * 100)];
    }
}

if (! function_exists('hsl_to_hex')) {
    /**
     * Convertit h° / s% / l% en hex #rrggbb.
     */
    function hsl_to_hex(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;
        $c  = (1 - abs(2 * $l - 1)) * $s;
        $x  = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m  = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60  => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default  => [$c, 0, $x],
        };

        return sprintf('#%02x%02x%02x',
            round(($r + $m) * 255),
            round(($g + $m) * 255),
            round(($b + $m) * 255),
        );
    }
}

if (! function_exists('brand_dark')) {
    /** Version assombrie de la couleur de marque (−9 pts de luminosité). */
    function brand_dark(string $hex): string
    {
        [$h, $s, $l] = hex_to_hsl($hex);
        return hsl_to_hex($h, $s, max(0, $l - 9));
    }
}

if (! function_exists('brand_wash')) {
    /** Teinte très claire de la couleur de marque (S÷3, L = 93 %). */
    function brand_wash(string $hex): string
    {
        [$h, $s] = hex_to_hsl($hex);
        return hsl_to_hex($h, round($s / 3), 93);
    }
}

if (! function_exists('current_company_id')) {
    /**
     * Résout la société courante pour le scoping multi-tenant.
     *
     * - Utilisateur connecté : company_id de son compte (peut être null pour
     *   un SUPER_ADMIN, qui n'appartient à aucune société).
     * - Personne connecté (console, seeders, jobs) : null.
     *
     * Une valeur null signifie "aucune restriction" — c'est le cas SUPER_ADMIN
     * ou un contexte console/CLI, jamais un utilisateur d'entreprise standard.
     */
    function current_company_id(): ?int
    {
        // Garde de réentrance : User utilise BelongsToTenant, donc résoudre
        // l'utilisateur courant (retrieveById, ex. depuis le cookie de
        // session) exécute une requête sur `users` qui réapplique CompanyScope,
        // qui rappelle current_company_id() avant que auth()->user() n'ait fini
        // de se résoudre — sans cette garde, boucle infinie (crash mémoire/pile).
        static $resolving = false;

        if ($resolving) {
            return null;
        }

        $resolving = true;

        try {
            return auth()->check() ? auth()->user()->company_id : null;
        } finally {
            $resolving = false;
        }
    }
}
