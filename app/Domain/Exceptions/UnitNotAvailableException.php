<?php

namespace App\Domain\Exceptions;

class UnitNotAvailableException extends DomainException
{
    public function __construct(string $message = 'Unit tidak tersedia untuk disewa')
    {
        parent::__construct($message);
    }
}
