<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitCodeFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_creation_rejects_code_with_spaces_as_validation_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = CategoryEloquent::create(['name' => 'Test']);

        $this->actingAs($admin)->post(route('admin.units.store'), [
            'code' => 'INVALID CODE',
            'name' => 'Unit Test',
            'description' => null,
            'category_ids' => [$category->id],
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('units', ['code' => 'INVALID CODE']);
    }

    public function test_unit_update_rejects_code_with_spaces_as_validation_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = CategoryEloquent::create(['name' => 'Test']);
        $unit = UnitEloquent::create(['code' => 'VALID-1', 'name' => 'Unit Test', 'status' => 'tersedia']);
        $unit->categories()->attach($category);

        $this->actingAs($admin)->put(route('admin.units.update', $unit->id), [
            'code' => 'INVALID CODE',
            'name' => 'Unit Test',
            'description' => null,
            'category_ids' => [$category->id],
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'code' => 'VALID-1']);
    }
}
