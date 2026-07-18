<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class IkomaDemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'name'           => 'Ikoma Stock Demo',
            'address'        => 'Plateau, Abidjan',
            'phone'          => '+225 27 20 00 00 00',
            'email'          => 'contact@ikoma-demo.ci',
            'currency'       => 'XOF',
            'invoice_prefix' => 'IKD',
            'footer_text'    => 'Merci de votre confiance.',
            'is_active'      => true,
            'primary_color'  => '#ea580c',
        ]);

        $outlet = Outlet::create([
            'company_id' => $company->id,
            'name'       => 'Boutique principale',
        ]);

        User::create([
            'name'       => 'Administrateur Demo',
            'phone'      => '+225 07 00 00 00 01',
            'password'   => Hash::make('IkomaAdmin2026!'),
            'company_id' => $company->id,
            'outlet_id'  => $outlet->id,
            'role'       => UserRole::ADMIN_COMPANY,
        ]);

        $this->command?->info('Société "Ikoma Stock Demo" créée.');
        $this->command?->info('Admin société : +225 07 00 00 00 01 / IkomaAdmin2026!');
    }
}
