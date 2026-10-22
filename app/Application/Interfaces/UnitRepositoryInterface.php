<?php

namespace App\Application\Interfaces;

use App\Domain\Entities\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UnitRepositoryInterface
{
    public function findById(int $id): ?Unit;

    public function findByCode(string $code): ?Unit;

    public function getAll(int $perPage = 15, string $search = ''): LengthAwarePaginator;

    public function getAvailable(int $perPage = 15, string $search = ''): LengthAwarePaginator;

    public function countAvailable(): int;

    public function create(Unit $unit, array $categoryIds): Unit;

    public function update(Unit $unit, array $categoryIds): Unit;

    public function delete(int $id): bool;

    public function lockForUpdate(int $id): ?Unit;

    public function hasActiveRentals(int $id): bool;
}
