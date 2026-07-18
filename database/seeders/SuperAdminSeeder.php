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
        User::create([
            'name'       => 'Super Administrateur',
            'phone'      => '+225 07 00 00 00 00',
            'password'   => Hash::make('IkomaSuper2026!'),
            'role'       => UserRole::SUPER_ADMIN,
            'company_id' => null,
            'outlet_id'  => null,
        ]);

        $this->command?->info('SUPER_ADMIN créé : +225 07 00 00 00 00 / IkomaSuper2026!');
    }
}
