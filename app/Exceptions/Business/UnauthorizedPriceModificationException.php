<?php

namespace App\Exceptions\Business;

class UnauthorizedPriceModificationException extends BusinessException
{
    protected int $httpStatus = 403;

    public function __construct()
    {
        parent::__construct("Vous n'êtes pas autorisé à modifier le prix de ce produit.");
    }
}
