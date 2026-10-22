<?php

namespace App\Domain\Services;

use App\Domain\Entities\Rental;
use App\Domain\Enums\RentalStatus;

class RentalPricingService
{
    public function __construct(
        private readonly int $dailyFineAmount = 50000,
        private readonly int $rentalDurationDays = 5,
    ) {}

    public function calculateDueDate(\DateTimeImmutable $rentedAt): \DateTimeImmutable
    {
        return $rentedAt->modify("+{$this->rentalDurationDays} days");
    }

    public function calculateFine(Rental $rental, \DateTimeImmutable $returnedAt): int
    {
        if ($rental->status === RentalStatus::RETURNED) {
            return $rental->fineAmount;
        }

        $daysLate = $rental->daysLate($returnedAt);

        return $daysLate * $this->dailyFineAmount;
    }

    public function getDailyFineAmount(): int
    {
        return $this->dailyFineAmount;
    }

    public function getRentalDurationDays(): int
    {
        return $this->rentalDurationDays;
    }
}
