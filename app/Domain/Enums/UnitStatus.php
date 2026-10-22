<?php

namespace App\Domain\Enums;

enum UnitStatus: string
{
    case AVAILABLE = 'tersedia';
    case RENTED = 'disewa';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Tersedia',
            self::RENTED => 'Disewa',
        };
    }
}
