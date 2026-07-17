<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class OutletPolicy
{
    use GrantsSuperAdmin;

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN_COMPANY;
    }

    public function update(User $user, Outlet $outlet): bool
    {
        return $user->role === UserRole::ADMIN_COMPANY && $user->company_id === $outlet->company_id;
    }
}
