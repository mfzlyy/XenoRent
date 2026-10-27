<?php

namespace App\Application\Interfaces;

interface RentalSettingsRepositoryInterface
{
    public function getMaxActiveRentals(): int;

    public function setMaxActiveRentals(int $maxActiveRentals): void;
}
