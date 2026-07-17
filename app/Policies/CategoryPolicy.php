<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class CategoryPolicy
{
    use GrantsSuperAdmin;

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::WAREHOUSE_KEEPER], true);
    }

    public function update(User $user, Category $category): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::WAREHOUSE_KEEPER], true)
            && $user->company_id === $category->company_id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }
}
