<?php

namespace App\Exceptions\Business;

class PaymentExceedsBalanceException extends BusinessException
{
    protected int $httpStatus = 422;

    public function __construct(int $amount, int $balanceDue)
    {
        parent::__construct(sprintf(
            'Le paiement de %s dépasse le solde restant dû (%s).',
            static::formatFcfa($amount),
            static::formatFcfa($balanceDue),
        ));
    }
}
