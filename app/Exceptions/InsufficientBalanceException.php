<?php

namespace App\Exceptions;

class InsufficientBalanceException extends \DomainException
{
    public function __construct(string $message = 'Saldo insuficiente.')
    {
        parent::__construct($message);
    }
}