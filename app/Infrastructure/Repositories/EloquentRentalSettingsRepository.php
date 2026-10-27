<?php

namespace App\Infrastructure\Repositories;

use App\Application\Interfaces\RentalSettingsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentRentalSettingsRepository implements RentalSettingsRepositoryInterface
{
    private const SETTINGS_ID = 1;

    public function getMaxActiveRentals(): int
    {
        $value = DB::table('rental_settings')
            ->where('id', self::SETTINGS_ID)
            ->value('max_active_rentals');

        return $value === null ? 2 : (int) $value;
    }

    public function setMaxActiveRentals(int $maxActiveRentals): void
    {
        $settings = DB::table('rental_settings')->where('id', self::SETTINGS_ID);

        if ($settings->exists()) {
            $settings->update([
                'max_active_rentals' => $maxActiveRentals,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('rental_settings')->insert([
            'id' => self::SETTINGS_ID,
            'max_active_rentals' => $maxActiveRentals,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
