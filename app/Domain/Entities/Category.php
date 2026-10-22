<?php

namespace App\Domain\Entities;

class Category
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
        public readonly int $unitsCount = 0,
    ) {}
}
