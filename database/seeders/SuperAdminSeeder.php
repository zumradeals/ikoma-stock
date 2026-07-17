<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->superAdmin()->create([
            'name' => 'Super Administrateur',
            'email' => 'superadmin@ikoma-stock.test',
            'password' => Hash::make('IkomaSuper2026!'),
        ]);

        $this->command?->info('SUPER_ADMIN créé : superadmin@ikoma-stock.test / IkomaSuper2026!');
    }
}
