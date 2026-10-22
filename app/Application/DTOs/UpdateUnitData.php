<?php

namespace App\Application\DTOs;

class UpdateUnitData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public array $categoryIds,
    ) {}
}
