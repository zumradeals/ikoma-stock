<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum UserRole: string
{
    use EnumValues;

    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN_COMPANY = 'ADMIN_COMPANY';
    case OUTLET_MANAGER = 'OUTLET_MANAGER';
    case SELLER = 'SELLER';
    case WAREHOUSE_KEEPER = 'WAREHOUSE_KEEPER';

    /**
     * Nom de route vers lequel rediriger après connexion (et cible de l'onglet "Accueil").
     */
    public function landingRoute(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'platform.index',
            self::ADMIN_COMPANY, self::OUTLET_MANAGER => 'app.dashboard',
            self::SELLER => 'app.home',
            self::WAREHOUSE_KEEPER => 'app.stock',
        };
    }

    /**
     * Libellé simple en français — jamais afficher ->value brut à l'écran
     * (public non-tech, souvent peu instruit : voir mémoire projet).
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super administrateur',
            self::ADMIN_COMPANY => 'Administrateur',
            self::OUTLET_MANAGER => 'Gérant de point de vente',
            self::SELLER => 'Vendeur',
            self::WAREHOUSE_KEEPER => 'Magasinier',
        };
    }
}
