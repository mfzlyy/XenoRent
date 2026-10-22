<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendChecklistEightToTenTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_unit_listing_includes_units_of_every_status(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        UnitEloquent::create(['code' => 'XN-A', 'name' => 'Available listing', 'status' => 'tersedia']);
        UnitEloquent::create(['code' => 'XN-R', 'name' => 'Rented listing', 'status' => 'disewa']);

        $this->actingAs($member)->get(route('units.index'))
            ->assertOk()
            ->assertSee('Available listing')
            ->assertSee('Rented listing');
    }

    public function test_rental_domain_error_returns_user_facing_redirect_instead_of_server_error(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $unit = UnitEloquent::create(['code' => 'XN-B', 'name' => 'Rented unit', 'status' => 'disewa']);

        $this->actingAs($member)->post(route('rentals.store', $unit->id))
            ->assertRedirect()
            ->assertSessionHasErrors();
    }

    public function test_deleting_rented_unit_returns_user_facing_redirect_instead_of_server_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $unit = UnitEloquent::create(['code' => 'XN-C', 'name' => 'Rented unit', 'status' => 'disewa']);

        $this->actingAs($admin)->delete(route('admin.units.destroy', $unit->id))
            ->assertRedirect()
            ->assertSessionHasErrors();
    }

    public function test_returning_unknown_rental_returns_user_facing_redirect_instead_of_server_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.rentals.return', 999))
            ->assertRedirect()
            ->assertSessionHasErrors();
    }
}
