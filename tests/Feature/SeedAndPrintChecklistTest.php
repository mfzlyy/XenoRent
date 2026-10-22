<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedAndPrintChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_script_runs_database_seeder(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertContains('@php artisan db:seed --force', $composer['scripts']['setup']);
    }

    public function test_database_seeder_can_run_twice_without_duplicate_demo_rows(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(3, User::count());
        $this->assertSame(6, CategoryEloquent::count());
        $this->assertSame(10, UnitEloquent::count());
        $this->assertSame(3, RentalEloquent::count());
    }

    public function test_admin_print_endpoint_returns_all_member_rentals_without_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'anggota']);
        $units = collect(range(1, 16))->map(fn (int $number): UnitEloquent => UnitEloquent::create([
            'code' => 'PRINT-'.$number,
            'name' => 'Print unit '.$number,
            'status' => 'tersedia',
        ]));

        foreach ($units as $unit) {
            RentalEloquent::create([
                'user_id' => $member->id,
                'unit_id' => $unit->id,
                'rented_at' => today()->subDays(3),
                'due_at' => today()->addDays(2),
                'status' => 'dikembalikan',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.members.rentals.print', $member->id))
            ->assertOk()
            ->assertSee('Print unit 1')
            ->assertSee('Print unit 16')
            ->assertDontSee('pagination-wrap');
    }
}
