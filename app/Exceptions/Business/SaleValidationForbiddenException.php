<?php

namespace App\Exceptions\Business;

class SaleValidationForbiddenException extends BusinessException
{
    protected int $httpStatus = 403;

    public function __construct(string $reason)
    {
        parent::__construct("Cette opération est interdite sur cette vente : {$reason}");
    }
}
