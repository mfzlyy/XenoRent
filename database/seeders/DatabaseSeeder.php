<?php

namespace Database\Seeders;

use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Infrastructure\Eloquent\ProfileEloquent;
use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Infrastructure\Eloquent\UserEloquent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin XenoRent', 'email' => 'admin@xenorent.test', 'role' => 'admin', 'phone' => '081200000001', 'address' => 'Hanggar Utama, Sektor 7'],
            ['name' => 'Rina Kosmos', 'email' => 'rina@xenorent.test', 'role' => 'anggota', 'phone' => '081200000002', 'address' => 'Jl. Orbit No. 12'],
            ['name' => 'Bima Nebula', 'email' => 'bima@xenorent.test', 'role' => 'anggota', 'phone' => '081200000003', 'address' => 'Jl. Galaksi No. 5'],
        ];
        $createdUsers = [];
        foreach ($users as $userData) {
            $user = UserEloquent::withTrashed()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                    'deleted_at' => null,
                ],
            );
            ProfileEloquent::updateOrCreate(
                ['user_id' => $user->id],
                ['phone' => $userData['phone'], 'address' => $userData['address']],
            );
            $createdUsers[$userData['name']] = $user;
        }

        $categoryNames = ['Piring Terbang', 'Pengintai', 'Kargo', 'Penumpang', 'Antargalaksi', 'Ramah Atmosfer Bumi'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[$name] = CategoryEloquent::updateOrCreate(['name' => $name], ['description' => null]);
        }

        $unitData = [
            ['ZTS-001', 'Zeta Saucer', 'disewa', ['Piring Terbang', 'Penumpang']],
            ['ZTS-002', 'Zeta Saucer', 'tersedia', ['Piring Terbang', 'Penumpang']],
            ['ZTS-003', 'Zeta Saucer', 'tersedia', ['Piring Terbang', 'Penumpang', 'Ramah Atmosfer Bumi']],
            ['NBS-001', 'Nebula Scout', 'disewa', ['Pengintai', 'Antargalaksi']],
            ['NBS-002', 'Nebula Scout', 'tersedia', ['Pengintai', 'Antargalaksi']],
            ['KPH-001', 'Kepler Hauler', 'tersedia', ['Kargo']],
            ['KPH-002', 'Kepler Hauler', 'tersedia', ['Kargo', 'Ramah Atmosfer Bumi']],
            ['ADC-001', 'Andromeda Cruiser', 'tersedia', ['Penumpang', 'Antargalaksi']],
            ['ORG-001', 'Orion Glider', 'tersedia', ['Pengintai', 'Ramah Atmosfer Bumi']],
            ['ORG-002', 'Orion Glider', 'tersedia', ['Pengintai', 'Ramah Atmosfer Bumi']],
        ];
        $units = [];
        foreach ($unitData as [$code, $name, $status, $categoryNames]) {
            $unit = UnitEloquent::withTrashed()->updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'description' => null, 'status' => $status, 'deleted_at' => null],
            );
            $unit->categories()->sync(array_map(fn (string $categoryName): int => $categories[$categoryName]->id, $categoryNames));
            $units[$code] = $unit;
        }

        $seedDay = today();
        $rentals = [
            ['Rina Kosmos', 'ZTS-001', -2, 3, null, 0, 'disewa'],
            ['Rina Kosmos', 'NBS-001', -8, -3, null, 0, 'disewa'],
            ['Bima Nebula', 'KPH-001', -12, -7, -5, 20000, 'dikembalikan'],
        ];
        foreach ($rentals as [$userName, $unitCode, $rentedOffset, $dueOffset, $returnedOffset, $fine, $status]) {
            RentalEloquent::updateOrCreate(
                ['user_id' => $createdUsers[$userName]->id, 'unit_id' => $units[$unitCode]->id],
                [
                    'rented_at' => $seedDay->copy()->addDays($rentedOffset),
                    'due_at' => $seedDay->copy()->addDays($dueOffset),
                    'returned_at' => $returnedOffset === null ? null : $seedDay->copy()->addDays($returnedOffset),
                    'fine_amount' => $fine,
                    'status' => $status,
                ],
            );
        }
    }
}
