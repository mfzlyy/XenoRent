<?php

namespace App\Application\DTOs;

class UpdateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $address,
        public ?string $password = null,
    ) {}
}
