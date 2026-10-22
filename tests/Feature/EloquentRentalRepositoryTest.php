<?php

namespace Tests\Feature;

use App\Domain\Entities\Rental;
use App\Domain\Entities\Unit;
use App\Domain\Enums\RentalStatus;
use App\Domain\Enums\UnitStatus;
use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Infrastructure\Repositories\EloquentRentalRepository;
use App\Infrastructure\Repositories\EloquentUnitRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentRentalRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_rental_returns_valid_date_entities(): void
    {
        $user = User::factory()->create(['role' => 'anggota']);
        $unit = UnitEloquent::create([
            'code' => 'XN-101',
            'name' => 'Unit Test',
            'status' => 'tersedia',
        ]);
        $category = CategoryEloquent::create(['name' => 'Kapal']);
        $unit->categories()->attach($category);
        $rentedAt = new \DateTimeImmutable('2026-10-22');
        $dueAt = new \DateTimeImmutable('2026-10-27');
        $rental = new Rental(
            id: null,
            userId: $user->id,
            unitId: $unit->id,
            rentedAt: $rentedAt,
            dueAt: $dueAt,
            returnedAt: null,
            fineAmount: 0,
            status: RentalStatus::ACTIVE,
        );

        $created = (new EloquentRentalRepository)->create($rental);

        $this->assertSame($rentedAt->format('Y-m-d'), $created->rentedAt->format('Y-m-d'));
        $this->assertSame($dueAt->format('Y-m-d'), $created->dueAt->format('Y-m-d'));
        $this->assertInstanceOf(\DateTimeImmutable::class, $created->rentedAt);
        $this->assertInstanceOf(\DateTimeImmutable::class, $created->dueAt);
        $this->assertNull($created->returnedAt);
        $this->assertSame(1, RentalEloquent::count());

        $unitRepository = new EloquentUnitRepository;
        $lockedUnit = $unitRepository->lockForUpdate($unit->id);
        $updatedUnit = new Unit(
            id: $lockedUnit->id,
            code: $lockedUnit->code,
            name: $lockedUnit->name,
            description: $lockedUnit->description,
            status: UnitStatus::RENTED,
            categories: $lockedUnit->categories,
        );

        $unitRepository->update($updatedUnit, $lockedUnit->categories);

        $this->assertDatabaseHas('category_unit', [
            'unit_id' => $unit->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_member_can_rent_unit_with_categories_attached(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $unit = UnitEloquent::create([
            'code' => 'XN-102',
            'name' => 'Unit Sewa',
            'status' => 'tersedia',
        ]);
        $category = CategoryEloquent::create(['name' => 'Sewa Test']);
        $unit->categories()->attach($category);

        $this->actingAs($member)
            ->post(route('rentals.store', $unit->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rentals', [
            'user_id' => $member->id,
            'unit_id' => $unit->id,
            'status' => 'disewa',
        ]);
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'status' => 'disewa',
        ]);
        $this->assertDatabaseHas('category_unit', [
            'unit_id' => $unit->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_member_rental_history_renders_rental_table(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $unit = UnitEloquent::create([
            'code' => 'XN-103',
            'name' => 'Kapal Riwayat',
            'status' => 'disewa',
        ]);
        RentalEloquent::create([
            'user_id' => $member->id,
            'unit_id' => $unit->id,
            'rented_at' => '2026-10-22',
            'due_at' => '2026-10-27',
            'status' => 'disewa',
        ]);

        $this->actingAs($member)
            ->get(route('rentals.mine'))
            ->assertOk()
            ->assertDontSee("@include('rentals.partials.rental-table")
            ->assertSee('data-table')
            ->assertSee('Kapal Riwayat');
    }
}
