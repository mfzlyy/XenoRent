<?php

namespace App\Domain\Enums;

enum RentalStatus: string
{
    case ACTIVE = 'disewa';
    case RETURNED = 'dikembalikan';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Disewa',
            self::RETURNED => 'Dikembalikan',
        };
    }
}
