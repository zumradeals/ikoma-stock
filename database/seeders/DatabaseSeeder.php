<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SuperAdminSeeder::class);
        $this->call(IkomaDemoCompanySeeder::class);

        app(CompanyDemoSeeder::class)->run([
            'name' => 'Quincaillerie Koné',
            'invoice_prefix' => 'QKN',
            'categories' => ['Quincaillerie', 'Fer & Métallurgie', 'Peinture & Finition'],
        ]);

        app(CompanyDemoSeeder::class)->run([
            'name' => 'Bois & Ciment Touré',
            'invoice_prefix' => 'BCT',
            'categories' => ['Bois & Menuiserie', 'Ciment & Matériaux', 'Fer & Métallurgie'],
        ]);
    }
}
