<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

/**
 * Gestion du personnel d'une société (pas des comptes ADMIN_COMPANY /
 * SUPER_ADMIN eux-mêmes — un ADMIN_COMPANY ne peut ni se créer de pair, ni
 * s'auto-promouvoir via cet écran).
 */
class UserPolicy
{
    use GrantsSuperAdmin;

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN_COMPANY;
    }

    public function update(User $user, User $target): bool
    {
        return $user->role === UserRole::ADMIN_COMPANY
            && $user->company_id === $target->company_id
            && ! in_array($target->role, [UserRole::ADMIN_COMPANY, UserRole::SUPER_ADMIN], true);
    }
}
