<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_without_units_or_rentals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('availableUnits', 0)
            ->assertViewHas('activeRentals', 0)
            ->assertViewHas('memberCount', 0)
            ->assertViewHas('upcomingRentals', fn ($rentals): bool => $rentals->isEmpty())
            ->assertSee('Tidak ada kapal yang sedang disewa.');
    }

    public function test_admin_dashboard_shows_totals_and_active_rentals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'anggota']);
        $deletedMember = User::factory()->create(['role' => 'anggota']);
        $deletedMember->forceFill(['deleted_at' => now()])->save();

        UnitEloquent::create([
            'code' => 'XN-001',
            'name' => 'Kapal Tersedia',
            'status' => 'tersedia',
        ]);
        $deletedUnit = UnitEloquent::create([
            'code' => 'XN-002',
            'name' => 'Kapal Dihapus',
            'status' => 'tersedia',
        ]);
        $deletedUnit->delete();
        $rentedUnit = UnitEloquent::create([
            'code' => 'XN-003',
            'name' => 'Kapal Sewa',
            'status' => 'disewa',
        ]);

        foreach (range(1, 6) as $index) {
            RentalEloquent::create([
                'user_id' => $member->id,
                'unit_id' => $rentedUnit->id,
                'rented_at' => now()->subDays($index)->toDateString(),
                'due_at' => now()->addDays($index)->toDateString(),
                'status' => 'disewa',
            ]);
        }

        RentalEloquent::create([
            'user_id' => $member->id,
            'unit_id' => $rentedUnit->id,
            'rented_at' => now()->subDays(10)->toDateString(),
            'due_at' => now()->subDays(3)->toDateString(),
            'returned_at' => now()->subDays(3)->toDateString(),
            'status' => 'dikembalikan',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('availableUnits', 1)
            ->assertViewHas('activeRentals', 6)
            ->assertViewHas('memberCount', 1)
            ->assertViewHas('upcomingRentals', fn (LengthAwarePaginator $rentals): bool => $rentals->count() === 5 && $rentals->total() === 6)
            ->assertSee($rentedUnit->name)
            ->assertSee($member->name)
            ->assertSee('Proses kembali');
    }

    public function test_admin_can_reach_dashboard_after_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->followingRedirects()
            ->post(route('login.store'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->assertSee('Panel operasional');
    }

    public function test_member_dashboard_displays_own_rentals(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $unit = UnitEloquent::create([
            'code' => 'XN-004',
            'name' => 'Kapal Anggota',
            'status' => 'disewa',
        ]);
        RentalEloquent::create([
            'user_id' => $member->id,
            'unit_id' => $unit->id,
            'rented_at' => now()->toDateString(),
            'due_at' => now()->addDays(3)->toDateString(),
            'status' => 'disewa',
        ]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('activeRentalCount', 1)
            ->assertSee($unit->name)
            ->assertDontSee('@include');
    }

    public function test_member_can_view_dashboard_with_quota(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('activeRentalCount', 0)
            ->assertViewHas('rentalLimit', 2)
            ->assertSee('Belum ada kapal yang disewa.');
    }
}
