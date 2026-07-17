<?php

namespace App\Policies;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Policies\Concerns\GrantsSuperAdmin;

class PlatformSettingPolicy
{
    use GrantsSuperAdmin;

    public function manage(User $user, ?PlatformSetting $setting = null): bool
    {
        return false;
    }
}
