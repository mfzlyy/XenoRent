<?php

namespace App\Application\DTOs;

class CreateUnitData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public array $categoryIds,
    ) {}
}
