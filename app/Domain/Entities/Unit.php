<?php

namespace App\Domain\Entities;

use App\Domain\Enums\UnitStatus;

class Unit
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly UnitStatus $status,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
        public readonly ?\DateTimeImmutable $deletedAt = null,
        /** @var Category[] */
        public readonly array $categories = [],
    ) {}

    public function isAvailable(): bool
    {
        return $this->status === UnitStatus::AVAILABLE;
    }
}
