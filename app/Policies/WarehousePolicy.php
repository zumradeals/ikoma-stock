<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\Concerns\GrantsSuperAdmin;

class WarehousePolicy
{
    use GrantsSuperAdmin;

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN_COMPANY;
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->role === UserRole::ADMIN_COMPANY && $user->company_id === $warehouse->company_id;
    }
}
