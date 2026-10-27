<?php

namespace App\Domain\Exceptions;

class RentalQuotaExceededException extends DomainException
{
    public function __construct(int $maxActiveRentals = 2)
    {
        parent::__construct("Kuota sewa penuh (maksimal {$maxActiveRentals} unit bersamaan)");
    }
}
