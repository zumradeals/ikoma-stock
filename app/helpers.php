<?php

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
