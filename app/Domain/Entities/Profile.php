<?php

namespace App\Domain\Entities;

class Profile
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $phone,
        public readonly string $address,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
