<?php

namespace App\Application\DTOs;

class CreateCategoryData
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {}
}
