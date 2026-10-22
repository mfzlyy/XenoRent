<?php

namespace App\Application\DTOs;

class CreateRentalData
{
    public function __construct(
        public int $userId,
        public int $unitId,
    ) {}
}
