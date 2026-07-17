<?php

namespace App\Exceptions\Business;

class DeliveryExceedsOrderedQuantityException extends BusinessException
{
    protected int $httpStatus = 422;

    public function __construct(int $requested, int $remaining)
    {
        parent::__construct(sprintf(
            'La quantité à livrer (%d) dépasse la quantité restant à livrer (%d).',
            $requested,
            $remaining,
        ));
    }
}
