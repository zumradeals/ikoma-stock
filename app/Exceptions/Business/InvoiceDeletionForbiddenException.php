<?php

namespace App\Exceptions\Business;

class InvoiceDeletionForbiddenException extends BusinessException
{
    protected int $httpStatus = 403;

    public function __construct()
    {
        parent::__construct("La suppression d'une facture n'est jamais autorisée ; utilisez l'annulation.");
    }
}
