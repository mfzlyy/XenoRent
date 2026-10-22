<?php

namespace App\Domain\Entities;

use App\Domain\Enums\RentalStatus;

class Rental
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly int $unitId,
        public readonly \DateTimeImmutable $rentedAt,
        public readonly \DateTimeImmutable $dueAt,
        public readonly ?\DateTimeImmutable $returnedAt,
        public readonly int $fineAmount,
        public readonly RentalStatus $status,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
        public readonly ?User $user = null,
        public readonly ?Unit $unit = null,
    ) {}

    public function isActive(): bool
    {
        return $this->status === RentalStatus::ACTIVE;
    }

    public function isReturned(): bool
    {
        return $this->status === RentalStatus::RETURNED;
    }

    public function daysLate(?\DateTimeImmutable $referenceDate = null): int
    {
        if ($this->isReturned() && $this->returnedAt) {
            $end = $this->returnedAt;
        } else {
            $end = $referenceDate ?? new \DateTimeImmutable;
        }

        $diff = $this->dueAt->diff($end);

        if ($end <= $this->dueAt) {
            return 0;
        }

        return max(0, $diff->days);
    }
}
