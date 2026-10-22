<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Infrastructure\Eloquent\UserEloquent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletedUnitHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_rental_history_relationship_resolves_soft_deleted_unit(): void
    {
        $user = UserEloquent::create(['name' => 'Test', 'email' => 'test@example.test', 'password' => 'password', 'role' => 'anggota']);
        $unit = UnitEloquent::create(['code' => 'TEST-001', 'name' => 'Test Unit', 'status' => 'tersedia']);
        $rental = RentalEloquent::create(['user_id' => $user->id, 'unit_id' => $unit->id, 'rented_at' => today(), 'due_at' => today()->addDays(5), 'status' => 'disewa']);
        $unit->delete();

        $this->assertTrue($unit->trashed());
        $this->assertSame($unit->id, $rental->fresh()->unit?->id);
    }
}
