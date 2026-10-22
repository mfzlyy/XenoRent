<?php

namespace App\Application\UseCases\Unit;

use App\Application\Interfaces\UnitRepositoryInterface;
use App\Domain\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

class DeleteUnit
{
    public function __construct(
        private readonly UnitRepositoryInterface $unitRepository,
    ) {}

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $unit = $this->unitRepository->lockForUpdate($id);

            if (! $unit) {
                throw new DomainException('Unit tidak ditemukan');
            }

            if ($unit->status->value === 'disewa') {
                throw new DomainException('Unit masih disewa, tidak bisa dihapus');
            }

            if ($this->unitRepository->hasActiveRentals($id)) {
                throw new DomainException('Unit masih memiliki transaksi sewa aktif, tidak bisa dihapus');
            }

            return $this->unitRepository->delete($id);
        });
    }
}
