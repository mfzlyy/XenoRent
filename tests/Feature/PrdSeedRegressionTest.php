<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Infrastructure\Eloquent\UserEloquent;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrdSeedRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_matches_prd_demo_accounts_units_categories_and_rentals(): void
    {
        $seedDay = today();
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, UserEloquent::count());
        $this->assertSame(['Admin XenoRent', 'Rina Kosmos', 'Bima Nebula'], UserEloquent::orderBy('id')->pluck('name')->all());
        $this->assertTrue(UserEloquent::all()->every(fn (UserEloquent $user): bool => Hash::check('password', $user->password)));
        $this->assertSame(['Piring Terbang', 'Pengintai', 'Kargo', 'Penumpang', 'Antargalaksi', 'Ramah Atmosfer Bumi'], CategoryEloquent::orderBy('id')->pluck('name')->all());
        $units = UnitEloquent::with('categories')->orderBy('code')->get()->keyBy('code');
        $this->assertSame(10, $units->count());
        $expected = [
            'ADC-001' => ['Andromeda Cruiser', 'tersedia', ['Penumpang', 'Antargalaksi']],
            'KPH-001' => ['Kepler Hauler', 'tersedia', ['Kargo']],
            'KPH-002' => ['Kepler Hauler', 'tersedia', ['Kargo', 'Ramah Atmosfer Bumi']],
            'NBS-001' => ['Nebula Scout', 'disewa', ['Pengintai', 'Antargalaksi']],
            'NBS-002' => ['Nebula Scout', 'tersedia', ['Pengintai', 'Antargalaksi']],
            'ORG-001' => ['Orion Glider', 'tersedia', ['Pengintai', 'Ramah Atmosfer Bumi']],
            'ORG-002' => ['Orion Glider', 'tersedia', ['Pengintai', 'Ramah Atmosfer Bumi']],
            'ZTS-001' => ['Zeta Saucer', 'disewa', ['Piring Terbang', 'Penumpang']],
            'ZTS-002' => ['Zeta Saucer', 'tersedia', ['Piring Terbang', 'Penumpang']],
            'ZTS-003' => ['Zeta Saucer', 'tersedia', ['Piring Terbang', 'Penumpang', 'Ramah Atmosfer Bumi']],
        ];
        foreach ($expected as $code => [$name, $status, $categories]) {
            $this->assertSame($name, $units[$code]->name);
            $this->assertSame($status, $units[$code]->status);
            $this->assertSame($categories, $units[$code]->categories->pluck('name')->all());
        }

        $rentals = RentalEloquent::with(['user', 'unit'])->get()->keyBy(fn (RentalEloquent $rental): string => $rental->user->name.'|'.$rental->unit->code);
        $this->assertCount(3, $rentals);
        $this->assertSame([$seedDay->copy()->subDays(2)->toDateString(), $seedDay->copy()->addDays(3)->toDateString(), null, 0, 'disewa'], [$rentals['Rina Kosmos|ZTS-001']->rented_at->toDateString(), $rentals['Rina Kosmos|ZTS-001']->due_at->toDateString(), $rentals['Rina Kosmos|ZTS-001']->returned_at?->toDateString(), $rentals['Rina Kosmos|ZTS-001']->fine_amount, $rentals['Rina Kosmos|ZTS-001']->status]);
        $this->assertSame([$seedDay->copy()->subDays(8)->toDateString(), $seedDay->copy()->subDays(3)->toDateString(), null, 0, 'disewa'], [$rentals['Rina Kosmos|NBS-001']->rented_at->toDateString(), $rentals['Rina Kosmos|NBS-001']->due_at->toDateString(), $rentals['Rina Kosmos|NBS-001']->returned_at?->toDateString(), $rentals['Rina Kosmos|NBS-001']->fine_amount, $rentals['Rina Kosmos|NBS-001']->status]);
        $this->assertSame([$seedDay->copy()->subDays(12)->toDateString(), $seedDay->copy()->subDays(7)->toDateString(), $seedDay->copy()->subDays(5)->toDateString(), 20000, 'dikembalikan'], [$rentals['Bima Nebula|KPH-001']->rented_at->toDateString(), $rentals['Bima Nebula|KPH-001']->due_at->toDateString(), $rentals['Bima Nebula|KPH-001']->returned_at?->toDateString(), $rentals['Bima Nebula|KPH-001']->fine_amount, $rentals['Bima Nebula|KPH-001']->status]);
    }
}
