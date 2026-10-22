<?php

namespace Database\Seeders;

use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Infrastructure\Eloquent\ProfileEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Infrastructure\Eloquent\UserEloquent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = UserEloquent::create([
            'name' => 'Admin XenoRent',
            'email' => 'admin@xenorent.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        ProfileEloquent::create([
            'user_id' => $admin->id,
            'phone' => '081234567890',
            'address' => 'Hanggar Pusat XenoRent, Sektor 07',
        ]);

        // Member user
        $member = UserEloquent::create([
            'name' => 'Anggota Demo',
            'email' => 'anggota@xenorent.test',
            'password' => Hash::make('password123'),
            'role' => 'anggota',
        ]);

        ProfileEloquent::create([
            'user_id' => $member->id,
            'phone' => '082345678901',
            'address' => 'Jl. Antargalaksi No. 42, Neo Jakarta',
        ]);

        // Categories
        $categories = [
            ['name' => 'Scout', 'description' => 'Kapal pengintaip ringan dan cepat'],
            ['name' => 'Cruiser', 'description' => 'Kapal menengah serbaguna'],
            ['name' => 'Battleship', 'description' => 'Kapal tempur berat bertahan lama'],
            ['name' => 'Carrier', 'description' => 'Kapal induk pembawa drone dan shuttle'],
            ['name' => 'Transport', 'description' => 'Kapal kargo dan penumpang massal'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[] = CategoryEloquent::create($cat);
        }

        // Units
        $units = [
            ['code' => 'ZTS-001', 'name' => 'Zeta Saucer', 'description' => 'Scout tipe klasik, cocok untuk rekognisi cepat', 'status' => 'tersedia', 'categories' => [1]],
            ['code' => 'ZTS-002', 'name' => 'Zeta Saucer Mk II', 'description' => 'Versi upgraded dengan sensor jangkauan ganda', 'status' => 'tersedia', 'categories' => [1]],
            ['code' => 'XCR-101', 'name' => 'Xylon Cruiser', 'description' => 'Cruiser standar armada, seimbang speed & firepower', 'status' => 'tersedia', 'categories' => [2]],
            ['code' => 'XCR-102', 'name' => 'Xylon Cruiser Prime', 'description' => 'Varian komando dengan suite komunikasi lanjut', 'status' => 'tersedia', 'categories' => [2]],
            ['code' => 'VBS-201', 'name' => 'Void Battleship', 'description' => 'Battleship kelas utama, hull tebal & battery ion', 'status' => 'tersedia', 'categories' => [3]],
            ['code' => 'NCA-301', 'name' => 'Nebula Carrier', 'description' => 'Carrier raksasa, kapasitas 144 drone tempur', 'status' => 'tersedia', 'categories' => [4]],
            ['code' => 'MTP-401', 'name' => 'Mass Transporter', 'description' => 'Transport muatan besar, cocok relokasi koloni', 'status' => 'tersedia', 'categories' => [5]],
        ];

        foreach ($units as $unitData) {
            $catIds = $unitData['categories'];
            unset($unitData['categories']);
            $unit = UnitEloquent::create($unitData);
            $unit->categories()->attach($catIds);
        }
    }
}
