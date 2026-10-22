<?php

namespace App\Application\DTOs;

class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $phone,
        public string $address,
    ) {}
}
