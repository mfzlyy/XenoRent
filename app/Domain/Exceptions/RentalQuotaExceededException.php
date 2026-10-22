<?php

namespace App\Domain\Exceptions;

class RentalQuotaExceededException extends DomainException
{
    public function __construct(string $message = 'Kuota sewa penuh (maksimal 2 unit bersamaan)')
    {
        parent::__construct($message);
    }
}
