<?php

namespace App\Exceptions;

class InvalidTransferException extends \DomainException
{
    public function __construct(string $message = 'Transferência inválida.')
    {
        parent::__construct($message);
    }
}