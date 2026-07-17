<?php

namespace App\Exceptions\Business;

class DailyClosingLockedException extends BusinessException
{
    protected int $httpStatus = 423;

    public function __construct()
    {
        parent::__construct("Ce point de journée est validé et verrouillé ; aucune modification n'est possible.");
    }
}
