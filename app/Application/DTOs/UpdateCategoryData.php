<?php

namespace App\Application\DTOs;

class UpdateCategoryData
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {}
}
