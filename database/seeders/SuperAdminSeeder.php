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
            'name'     => 'Super Administrateur',
            'phone'    => '+225 07 00 00 00 00',
            'password' => Hash::make('IkomaSuper2026!'),
        ]);

        $this->command?->info('SUPER_ADMIN créé : +225 07 00 00 00 00 / IkomaSuper2026!');
    }
}
