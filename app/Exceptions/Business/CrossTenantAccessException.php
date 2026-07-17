<?php

namespace App\Exceptions\Business;

class CrossTenantAccessException extends BusinessException
{
    protected int $httpStatus = 403;

    public function __construct()
    {
        parent::__construct('Accès refusé : cette ressource appartient à une autre entreprise.');
    }
}
