<?php

namespace App\Application\DTOs;

class ReturnRentalData
{
    public function __construct(
        public int $rentalId,
    ) {}
}
