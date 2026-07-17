<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Sale;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class SalePolicy
{
    use GrantsSuperAdmin;

    public function applyDiscount(User $user, Sale $sale): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true);
    }

    public function cancel(User $user, Sale $sale): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true);
    }
}
