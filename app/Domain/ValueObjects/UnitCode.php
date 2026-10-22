<?php

namespace App\Domain\ValueObjects;

class UnitCode
{
    public function __construct(
        public readonly string $value
    ) {
        if (! preg_match('/^[A-Za-z0-9_-]{1,20}$/', $value)) {
            throw new \InvalidArgumentException("Unit code must be 1-20 alphanumeric/underscore/hyphen: {$value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
