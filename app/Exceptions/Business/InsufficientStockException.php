<?php

namespace App\Exceptions\Business;

use App\Models\Product;

class InsufficientStockException extends BusinessException
{
    protected int $httpStatus = 422;

    public function __construct(Product $product, int $available, int $requested)
    {
        parent::__construct(sprintf(
            'Stock disponible insuffisant pour "%s" : %d disponible(s) pour %d demandé(s).',
            $product->name,
            $available,
            $requested,
        ));
    }
}
