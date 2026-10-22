<?php

namespace App\Application\UseCases\Unit;

use App\Application\DTOs\CreateUnitData;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Domain\Entities\Unit;
use App\Domain\Enums\UnitStatus;
use App\Domain\Exceptions\DomainException;
use App\Domain\ValueObjects\UnitCode;

class CreateUnit
{
    public function __construct(
        private readonly UnitRepositoryInterface $unitRepository,
    ) {}

    public function execute(CreateUnitData $data): Unit
    {
        $code = new UnitCode($data->code);

        if ($this->unitRepository->findByCode($code->value)) {
            throw new DomainException('Kode unit sudah digunakan');
        }

        $unit = new Unit(
            id: null,
            code: $code->value,
            name: $data->name,
            description: $data->description,
            status: UnitStatus::AVAILABLE,
        );

        return $this->unitRepository->create($unit, $data->categoryIds);
    }
}
