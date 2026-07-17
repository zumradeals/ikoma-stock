<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Transfer;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class TransferPolicy
{
    use GrantsSuperAdmin;

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER, UserRole::WAREHOUSE_KEEPER], true);
    }

    public function manage(User $user, Transfer $transfer): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER, UserRole::WAREHOUSE_KEEPER], true);
    }
}
