<?php

namespace App\Application\UseCases\Auth;

use App\Application\Interfaces\UserRepositoryInterface;
use App\Domain\Entities\User;
use Illuminate\Support\Facades\Hash;

class LoginUser
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
