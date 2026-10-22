<?php

namespace App\Domain\Services;

use App\Domain\Entities\Rental;
use App\Domain\Enums\RentalStatus;

class RentalPricingService
{
    private readonly int $dailyFineAmount;

    private readonly int $rentalDurationDays;

    public function __construct(?int $dailyFineAmount = null, ?int $rentalDurationDays = null)
    {
        $this->dailyFineAmount = $dailyFineAmount ?? (int) config('app.rental_daily_fine', 10000);
        $this->rentalDurationDays = $rentalDurationDays ?? (int) config('app.rental_duration_days', 5);
    }

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
