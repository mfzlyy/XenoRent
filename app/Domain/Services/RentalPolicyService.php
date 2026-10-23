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
    private const MAX_ACTIVE_RENTALS = 2;

    public function assertCanRent(Unit $unit, int $activeRentalCount): void
    {
        if ($unit->status !== UnitStatus::AVAILABLE) {
            throw new UnitNotAvailableException;
        }

        if ($activeRentalCount >= self::MAX_ACTIVE_RENTALS) {
            throw new RentalQuotaExceededException;
        }
    }

    public function assertCanReturn(Rental $rental): void
    {
        if ($rental->status !== RentalStatus::ACTIVE) {
            throw new DomainException('Sewa sudah dikembalikan');
        }
    }

    public function getMaxActiveRentals(): int
    {
        return self::MAX_ACTIVE_RENTALS;
    }
}
