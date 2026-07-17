<?php

namespace App\Policies;

use App\Enums\DailyClosingStatus;
use App\Enums\UserRole;
use App\Models\DailyClosing;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class DailyClosingPolicy
{
    use GrantsSuperAdmin;

    /**
     * Verrouillage total après VALIDATED — voir DailyClosingLockedException,
     * levée par le service ; la policy reflète la même règle côté Gate.
     */
    public function update(User $user, DailyClosing $dailyClosing): bool
    {
        if ($dailyClosing->status === DailyClosingStatus::VALIDATED) {
            return false;
        }

        return in_array($user->role, [
            UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER, UserRole::SELLER, UserRole::WAREHOUSE_KEEPER,
        ], true);
    }

    /**
     * Maker-checker : celui qui a ouvert/soumis le point de journée ne
     * peut pas le valider lui-même.
     */
    public function validate(User $user, DailyClosing $dailyClosing): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true)
            && $user->id !== $dailyClosing->user_id;
    }

    public function reject(User $user, DailyClosing $dailyClosing): bool
    {
        return $this->validate($user, $dailyClosing);
    }
}
