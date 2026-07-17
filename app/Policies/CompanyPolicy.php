<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

/**
 * Création/suspension : réservées à la plateforme (SUPER_ADMIN, via
 * before()). Édition : un ADMIN_COMPANY peut aussi modifier SA PROPRE
 * société (branding, coordonnées...). Attention : before() ne s'exécute QUE
 * si la méthode d'ability existe sur la policy (Gate::resolvePolicyCallback),
 * donc create()/update() doivent exister explicitement même si leur corps
 * ne sert qu'aux non-SUPER_ADMIN.
 */
class CompanyPolicy
{
    use GrantsSuperAdmin;

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Company $company): bool
    {
        return $user->role === UserRole::ADMIN_COMPANY && $user->company_id === $company->id;
    }
}
