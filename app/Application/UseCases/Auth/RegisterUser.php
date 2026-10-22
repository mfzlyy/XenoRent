<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\CreateUserData;
use App\Application\Interfaces\UserRepositoryInterface;
use App\Domain\Entities\Profile;
use App\Domain\Entities\User;
use App\Domain\Enums\UserRole;
use App\Domain\Exceptions\DomainException;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\PhoneNumber;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(CreateUserData $data): User
    {
        $email = new Email($data->email);
        $phone = new PhoneNumber($data->phone);

        if ($this->userRepository->findByEmail($email->value)) {
            throw new DomainException('Email sudah terdaftar');
        }

        $user = new User(
            id: null,
            name: $data->name,
            email: $email,
            password: Hash::make($data->password),
            role: UserRole::MEMBER,
        );

        $profile = new Profile(
            id: null,
            userId: 0,
            phone: $phone->value,
            address: $data->address,
        );

        return $this->userRepository->create($user, $profile);
    }
}
