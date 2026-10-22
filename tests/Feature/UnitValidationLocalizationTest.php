<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitValidationLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_unit_code_has_prd_indonesian_error_message(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.units.store'), [
            'code' => 'ZTS-001',
            'name' => 'Duplicate',
            'category_ids' => [1],
        ])->assertSessionHasErrors(['code' => 'Kode unit sudah dipakai']);
    }

    public function test_required_unit_fields_have_indonesian_validation_messages(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.units.store'), [])->assertSessionHasErrors([
            'code' => 'kode wajib diisi.',
            'name' => 'nama wajib diisi.',
            'category_ids' => 'kategori wajib diisi.',
        ]);
    }
}
