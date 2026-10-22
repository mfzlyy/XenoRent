<?php

namespace Tests\Unit;

use App\Domain\Entities\Rental;
use App\Domain\Enums\RentalStatus;
use App\Domain\Services\RentalPricingService;
use Tests\TestCase;

class RentalPricingServiceTest extends TestCase
{
    public function test_returned_on_or_before_due_date_has_no_fine(): void
    {
        $dueAt = new \DateTimeImmutable('2026-01-06 12:00:00');
        $service = new RentalPricingService;

        foreach (['2026-01-06 12:00:00', '2026-01-05 12:00:00'] as $returnedAt) {
            $rental = new Rental(
                id: 1,
                userId: 1,
                unitId: 1,
                rentedAt: new \DateTimeImmutable('2026-01-01 12:00:00'),
                dueAt: $dueAt,
                returnedAt: new \DateTimeImmutable($returnedAt),
                fineAmount: 0,
                status: RentalStatus::ACTIVE,
            );

            $this->assertSame(0, $service->calculateFine($rental, new \DateTimeImmutable($returnedAt)));
        }
    }

    public function test_default_fine_tariff_and_rental_duration_are_configured(): void
    {
        config(['app.rental_daily_fine' => 10000, 'app.rental_duration_days' => 5]);

        $service = app(RentalPricingService::class);

        $this->assertSame(10000, $service->getDailyFineAmount());
        $this->assertSame(5, $service->getRentalDurationDays());
    }
}
