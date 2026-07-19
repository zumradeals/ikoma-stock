<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $phone = '+22507000000';
        $code = User::generateAccessCode();

        User::create([
            'name'       => 'Super Administrateur',
            'phone'      => $phone,
            'email'      => 'superadmin@ikoma.local',
            'password'   => Hash::make($code),
            'role'       => UserRole::SUPER_ADMIN,
            'company_id' => null,
            'outlet_id'  => null,
        ]);

        $this->command?->info("SUPER_ADMIN créé : {$phone} / {$code}");
    }
}
