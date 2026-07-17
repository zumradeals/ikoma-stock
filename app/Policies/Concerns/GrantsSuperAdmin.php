<?php

namespace App\Policies\Concerns;

use App\Enums\UserRole;
use App\Models\User;

trait GrantsSuperAdmin
{
    /**
     * Un SUPER_ADMIN passe toujours, quelle que soit l'ability demandée —
     * convention Laravel : before() court-circuite les autres méthodes.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === UserRole::SUPER_ADMIN ? true : null;
    }
}
