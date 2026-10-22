<?php

namespace App\Domain\ValueObjects;

class PhoneNumber
{
    public function __construct(
        public readonly string $value
    ) {
        if (! preg_match('/^[0-9]{10,15}$/', $value)) {
            throw new \InvalidArgumentException("Phone number must be 10-15 digits: {$value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
