<?php

namespace App\Application\UseCases\Rental;

use App\Application\DTOs\ReturnRentalData;
use App\Application\Interfaces\RentalRepositoryInterface;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Domain\Entities\Rental;
use App\Domain\Entities\Unit;
use App\Domain\Enums\RentalStatus;
use App\Domain\Enums\UnitStatus;
use App\Domain\Exceptions\RentalNotFoundException;
use App\Domain\Services\RentalPolicyService;
use App\Domain\Services\RentalPricingService;
use Illuminate\Support\Facades\DB;

class ReturnRental
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentalRepository,
        private readonly UnitRepositoryInterface $unitRepository,
        private readonly RentalPolicyService $policyService,
        private readonly RentalPricingService $pricingService,
    ) {}

    public function execute(ReturnRentalData $data): Rental
    {
        return DB::transaction(function () use ($data) {
            $rental = $this->rentalRepository->lockForUpdate($data->rentalId);

            if (! $rental) {
                throw new RentalNotFoundException;
            }

            $this->policyService->assertCanReturn($rental);

            $returnedAt = new \DateTimeImmutable;
            $fineAmount = $this->pricingService->calculateFine($rental, $returnedAt);

            $updatedRental = new Rental(
                id: $rental->id,
                userId: $rental->userId,
                unitId: $rental->unitId,
                rentedAt: $rental->rentedAt,
                dueAt: $rental->dueAt,
                returnedAt: $returnedAt,
                fineAmount: $fineAmount,
                status: RentalStatus::RETURNED,
                createdAt: $rental->createdAt,
                updatedAt: $returnedAt,
                user: $rental->user,
                unit: $rental->unit,
            );

            $savedRental = $this->rentalRepository->update($updatedRental);

            $unit = $this->unitRepository->lockForUpdate($rental->unitId);

            if ($unit) {
                $updatedUnit = new Unit(
                    id: $unit->id,
                    code: $unit->code,
                    name: $unit->name,
                    description: $unit->description,
                    status: UnitStatus::AVAILABLE,
                    createdAt: $unit->createdAt,
                    updatedAt: new \DateTimeImmutable,
                    deletedAt: $unit->deletedAt,
                    categories: $unit->categories,
                );

                $this->unitRepository->update($updatedUnit, $unit->categories);
            }

            return $savedRental;
        });
    }
}
