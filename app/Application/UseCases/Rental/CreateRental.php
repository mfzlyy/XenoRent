<?php

namespace App\Application\UseCases\Rental;

use App\Application\DTOs\CreateRentalData;
use App\Application\Interfaces\RentalRepositoryInterface;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Domain\Entities\Rental;
use App\Domain\Entities\Unit;
use App\Domain\Enums\RentalStatus;
use App\Domain\Enums\UnitStatus;
use App\Domain\Services\RentalPolicyService;
use App\Domain\Services\RentalPricingService;
use Illuminate\Support\Facades\DB;

class CreateRental
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentalRepository,
        private readonly UnitRepositoryInterface $unitRepository,
        private readonly RentalPolicyService $policyService,
        private readonly RentalPricingService $pricingService,
    ) {}

    public function execute(CreateRentalData $data): Rental
    {
        return DB::transaction(function () use ($data) {
            $unit = $this->unitRepository->lockForUpdate($data->unitId);

            if (! $unit) {
                throw new \DomainException('Unit tidak ditemukan');
            }

            $activeCount = $this->rentalRepository->countActiveByUser($data->userId);

            $this->policyService->assertCanRent($unit, $activeCount);

            $rentedAt = new \DateTimeImmutable;
            $dueAt = $this->pricingService->calculateDueDate($rentedAt);

            $rental = new Rental(
                id: null,
                userId: $data->userId,
                unitId: $data->unitId,
                rentedAt: $rentedAt,
                dueAt: $dueAt,
                returnedAt: null,
                fineAmount: 0,
                status: RentalStatus::ACTIVE,
            );

            $createdRental = $this->rentalRepository->create($rental);

            $updatedUnit = new Unit(
                id: $unit->id,
                code: $unit->code,
                name: $unit->name,
                description: $unit->description,
                status: UnitStatus::RENTED,
                createdAt: $unit->createdAt,
                updatedAt: new \DateTimeImmutable,
                deletedAt: $unit->deletedAt,
                categories: $unit->categories,
            );

            $this->unitRepository->update($updatedUnit, $unit->categories);

            return $createdRental;
        });
    }
}
