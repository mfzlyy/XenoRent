<?php

namespace App\Application\UseCases\Unit;

use App\Application\DTOs\UpdateUnitData;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Domain\Entities\Unit;
use App\Domain\Exceptions\DomainException;
use App\Domain\ValueObjects\UnitCode;

class UpdateUnit
{
    public function __construct(
        private readonly UnitRepositoryInterface $unitRepository,
    ) {}

    public function execute(int $id, UpdateUnitData $data): Unit
    {
        $unit = $this->unitRepository->findById($id);

        if (! $unit) {
            throw new DomainException('Unit tidak ditemukan');
        }

        $code = new UnitCode($data->code);

        $existing = $this->unitRepository->findByCode($code->value);
        if ($existing && $existing->id !== $id) {
            throw new DomainException('Kode unit sudah digunakan');
        }

        $updatedUnit = new Unit(
            id: $unit->id,
            code: $code->value,
            name: $data->name,
            description: $data->description,
            status: $unit->status,
            createdAt: $unit->createdAt,
            updatedAt: new \DateTimeImmutable,
            deletedAt: $unit->deletedAt,
            categories: $unit->categories,
        );

        return $this->unitRepository->update($updatedUnit, $data->categoryIds);
    }
}
