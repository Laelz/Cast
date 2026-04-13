<?php

namespace App\Exceptions;

class AccountNotFoundException extends \DomainException
{
    public function __construct(string $message = 'Conta não encontrada.')
    {
        parent::__construct($message);
    }
}