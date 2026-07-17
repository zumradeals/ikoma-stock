<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class ProductPolicy
{
    use GrantsSuperAdmin;

    public function updatePrice(User $user, Product $product): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::WAREHOUSE_KEEPER], true);
    }

    public function update(User $user, Product $product): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::WAREHOUSE_KEEPER], true)
            && $user->company_id === $product->company_id;
    }
}
