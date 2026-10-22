<?php

namespace App\Application\UseCases\Profile;

use App\Application\DTOs\UpdateUserData;
use App\Application\Interfaces\UserRepositoryInterface;
use App\Domain\Entities\Profile;
use App\Domain\Entities\User;
use App\Domain\Exceptions\DomainException;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\PhoneNumber;
use Illuminate\Support\Facades\Hash;

class UpdateProfile
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(int $userId, UpdateUserData $data): User
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new DomainException('User tidak ditemukan');
        }

        $email = new Email($data->email);
        $phone = new PhoneNumber($data->phone);

        $existingUser = $this->userRepository->findByEmail($email->value);
        if ($existingUser && $existingUser->id !== $userId) {
            throw new DomainException('Email sudah digunakan');
        }

        $updatedUser = new User(
            id: $user->id,
            name: $data->name,
            email: $email,
            password: $data->password ? Hash::make($data->password) : $user->password,
            role: $user->role,
            profile: $user->profile,
            rememberToken: $user->rememberToken,
            emailVerifiedAt: $user->emailVerifiedAt,
            createdAt: $user->createdAt,
            updatedAt: new \DateTimeImmutable,
            deletedAt: $user->deletedAt,
        );

        $updatedProfile = new Profile(
            id: $user->profile?->id,
            userId: $userId,
            phone: $phone->value,
            address: $data->address,
            createdAt: $user->profile?->createdAt,
            updatedAt: new \DateTimeImmutable,
        );

        return $this->userRepository->update($updatedUser, $updatedProfile);
    }
}
