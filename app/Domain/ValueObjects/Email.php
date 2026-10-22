<?php

namespace App\Domain\ValueObjects;

class Email
{
    public function __construct(
        public readonly string $value
    ) {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format: {$value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
