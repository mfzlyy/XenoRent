<?php

namespace App\Domain\Entities;

use App\Domain\Enums\UserRole;
use App\Domain\ValueObjects\Email;

class User
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly Email $email,
        public readonly string $password,
        public readonly UserRole $role,
        public readonly ?Profile $profile = null,
        public readonly ?string $rememberToken = null,
        public readonly ?\DateTimeImmutable $emailVerifiedAt = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
        public readonly ?\DateTimeImmutable $deletedAt = null,
        public readonly bool $isApproved = true,
    ) {}

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === UserRole::MEMBER;
    }
}
