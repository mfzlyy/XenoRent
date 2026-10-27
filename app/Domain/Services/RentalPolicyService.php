<?php

namespace App\Domain\Services;

use App\Domain\Entities\Rental;
use App\Domain\Entities\Unit;
use App\Domain\Enums\RentalStatus;
use App\Domain\Enums\UnitStatus;
use App\Domain\Exceptions\DomainException;
use App\Domain\Exceptions\RentalQuotaExceededException;
use App\Domain\Exceptions\UnitNotAvailableException;

class RentalPolicyService
{
    public function assertCanRent(Unit $unit, int $activeRentalCount, int $maxActiveRentals): void
    {
        if ($unit->status !== UnitStatus::AVAILABLE) {
            throw new UnitNotAvailableException;
        }

        if ($activeRentalCount >= $maxActiveRentals) {
            throw new RentalQuotaExceededException($maxActiveRentals);
        }
    }

    public function assertCanReturn(Rental $rental): void
    {
        if ($rental->status !== RentalStatus::ACTIVE) {
            throw new DomainException('Sewa sudah dikembalikan');
        }
    }

}
