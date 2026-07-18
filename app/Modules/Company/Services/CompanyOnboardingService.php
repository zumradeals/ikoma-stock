<?php

namespace App\Modules\Company\Services;

use App\Enums\UserRole;
use App\Exceptions\Business\PhoneAlreadyRegisteredException;
use App\Models\Company;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyOnboardingService
{
    /**
     * Crée, dans une seule transaction, une société, son point de vente par
     * défaut et son administrateur. Lève PhoneAlreadyRegisteredException si le
     * numéro est déjà pris — avant toute écriture, pour éviter tout état partiel.
     */
    public function register(
        string $fullName,
        string $phone,
        string $password,
        string $companyName = 'Mon entreprise',
        string $outletName = 'Boutique principale',
    ): User {
        // Vérification métier explicite avant SQL, indépendante de la contrainte unique.
        // User utilise CompanyScope ; comme personne n'est connecté ici,
        // current_company_id() retourne null et le scope ne filtre pas — on
        // cherche bien dans toute la table.
        if (User::where('phone', $phone)->exists()) {
            throw new PhoneAlreadyRegisteredException($phone);
        }

        return DB::transaction(function () use ($fullName, $phone, $password, $companyName, $outletName) {
            $company = Company::create([
                'name' => $companyName,
                'is_active' => true,
            ]);

            $outlet = Outlet::create([
                'company_id' => $company->id,
                'name' => $outletName,
                'is_active' => true,
            ]);

            return User::create([
                'company_id' => $company->id,
                'outlet_id' => $outlet->id,
                'name' => $fullName,
                'phone' => $phone,
                'email' => $phone . '@ikoma.local',
                'password' => Hash::make($password),
                'role' => UserRole::ADMIN_COMPANY,
                'is_active' => true,
            ]);
        });
    }
}
