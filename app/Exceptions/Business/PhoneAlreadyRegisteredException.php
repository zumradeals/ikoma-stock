<?php

namespace App\Exceptions\Business;

class PhoneAlreadyRegisteredException extends BusinessException
{
    public function __construct(string $phone)
    {
        parent::__construct("Le numéro {$phone} est déjà associé à un compte.");
    }
}
