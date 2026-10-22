<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalBackendChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_exceed_active_rental_quota(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $units = collect(range(1, 3))->map(fn (int $number): UnitEloquent => UnitEloquent::create([
            'code' => 'QUOTA-'.$number,
            'name' => 'Quota unit '.$number,
            'status' => $number < 3 ? 'disewa' : 'tersedia',
        ]));

        foreach ($units->take(2) as $unit) {
            RentalEloquent::create([
                'user_id' => $member->id,
                'unit_id' => $unit->id,
                'rented_at' => today()->subDay(),
                'due_at' => today()->addDays(4),
                'status' => 'disewa',
            ]);
        }

        $this->actingAs($member)->post(route('rentals.store', $units->last()->id))
            ->assertRedirect()
            ->assertSessionHasErrors('rental');

        $this->assertDatabaseMissing('rentals', ['unit_id' => $units->last()->id, 'user_id' => $member->id]);
    }

    public function test_admin_can_return_active_rental_and_record_zero_fine_when_on_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'anggota']);
        $unit = UnitEloquent::create(['code' => 'RETURN-1', 'name' => 'Return unit', 'status' => 'disewa']);
        $rental = RentalEloquent::create([
            'user_id' => $member->id,
            'unit_id' => $unit->id,
            'rented_at' => today()->subDays(2),
            'due_at' => today(),
            'status' => 'disewa',
        ]);

        $this->actingAs($admin)->post(route('admin.rentals.return', $rental->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'status' => 'dikembalikan',
            'fine_amount' => 0,
        ]);
        $this->assertSame(today()->toDateString(), $rental->fresh()->returned_at->toDateString());
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'status' => 'tersedia']);
    }
}
