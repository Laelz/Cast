<?php

namespace App\Exceptions;

class InvalidTransactionAmountException extends \InvalidArgumentException
{
    public function __construct(string $message = 'O valor da operação deve ser maior que zero.')
    {
        parent::__construct($message);
    }
}