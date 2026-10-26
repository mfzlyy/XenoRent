<?php

namespace App\Application\Interfaces;

use App\Domain\Entities\Rental;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RentalRepositoryInterface
{
    public function findById(int $id): ?Rental;

    public function getByUser(int $userId, int $perPage = 15, ?string $month = null): LengthAwarePaginator;

    public function getAllByUser(int $userId, ?string $month = null): Collection;

    public function getHistory(int $perPage = 15, ?string $month = null): LengthAwarePaginator;

    public function getAllHistory(?string $month = null): Collection;

    public function getRentedMonths(?int $userId = null): array;

    public function getActive(int $perPage = 15): LengthAwarePaginator;

    public function countActiveByUser(int $userId): int;

    public function countActive(): int;

    public function create(Rental $rental): Rental;

    public function update(Rental $rental): Rental;

    public function lockForUpdate(int $id): ?Rental;
}
