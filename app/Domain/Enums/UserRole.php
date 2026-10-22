<?php

namespace App\Domain\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MEMBER = 'anggota';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::MEMBER => 'Anggota',
        };
    }
}
