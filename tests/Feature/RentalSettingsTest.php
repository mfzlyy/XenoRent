<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_the_global_rental_limit_for_all_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $members = collect([
            User::factory()->create(['role' => 'anggota']),
            User::factory()->create(['role' => 'anggota']),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.rental-settings.edit'))
            ->assertOk()
            ->assertSee('value="2"', false);

        $this->put(route('admin.rental-settings.update'), ['max_active_rentals' => 1])
            ->assertRedirect(route('admin.rental-settings.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rental_settings', [
            'id' => 1,
            'max_active_rentals' => 1,
        ]);

        foreach ($members as $index => $member) {
            $activeUnit = UnitEloquent::create([
                'code' => 'ACTIVE-'.$index,
                'name' => 'Active unit '.$index,
                'status' => 'disewa',
            ]);
            $availableUnit = UnitEloquent::create([
                'code' => 'AVAILABLE-'.$index,
                'name' => 'Available unit '.$index,
                'status' => 'tersedia',
            ]);

            RentalEloquent::create([
                'user_id' => $member->id,
                'unit_id' => $activeUnit->id,
                'rented_at' => today()->subDay(),
                'due_at' => today()->addDays(4),
                'status' => 'disewa',
            ]);

            $this->actingAs($member)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertViewHas('rentalLimit', 1);

            $this->post(route('rentals.store', $availableUnit->id))
                ->assertRedirect()
                ->assertSessionHasErrors('rental');

            $this->assertDatabaseMissing('rentals', [
                'user_id' => $member->id,
                'unit_id' => $availableUnit->id,
            ]);
        }
    }

    public function test_increasing_the_limit_allows_members_to_rent_an_additional_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($admin)
            ->put(route('admin.rental-settings.update'), ['max_active_rentals' => 3])
            ->assertRedirect(route('admin.rental-settings.edit'));

        foreach (range(1, 2) as $number) {
            $unit = UnitEloquent::create([
                'code' => 'ACTIVE-'.$number,
                'name' => 'Active unit '.$number,
                'status' => 'disewa',
            ]);

            RentalEloquent::create([
                'user_id' => $member->id,
                'unit_id' => $unit->id,
                'rented_at' => today()->subDay(),
                'due_at' => today()->addDays(4),
                'status' => 'disewa',
            ]);
        }

        $availableUnit = UnitEloquent::create([
            'code' => 'AVAILABLE-3',
            'name' => 'Available unit',
            'status' => 'tersedia',
        ]);

        $this->actingAs($member)
            ->post(route('rentals.store', $availableUnit->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rentals', [
            'user_id' => $member->id,
            'unit_id' => $availableUnit->id,
            'status' => 'disewa',
        ]);
    }

    public function test_member_cannot_open_or_update_rental_settings(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->get(route('admin.rental-settings.edit'))
            ->assertForbidden();

        $this->put(route('admin.rental-settings.update'), ['max_active_rentals' => 5])
            ->assertForbidden();
    }

    public function test_admin_cannot_set_an_invalid_rental_limit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.rental-settings.update'), ['max_active_rentals' => 0])
            ->assertSessionHasErrors('max_active_rentals');

        $this->assertDatabaseHas('rental_settings', [
            'id' => 1,
            'max_active_rentals' => 2,
        ]);
    }
}
