<?php

namespace App\Application\UseCases\Unit;

use App\Application\Interfaces\UnitRepositoryInterface;
use App\Domain\Exceptions\DomainException;

class DeleteUnit
{
    public function __construct(
        private readonly UnitRepositoryInterface $unitRepository,
    ) {}

    public function execute(int $id): bool
    {
        $unit = $this->unitRepository->findById($id);

        if (! $unit) {
            throw new DomainException('Unit tidak ditemukan');
        }

        if ($unit->status->value === 'disewa') {
            throw new DomainException('Unit masih disewa, tidak bisa dihapus');
        }

        return $this->unitRepository->delete($id);
    }
}
