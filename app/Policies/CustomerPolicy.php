<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class CustomerPolicy
{
    use GrantsSuperAdmin;

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->company_id === $customer->company_id;
    }
}
