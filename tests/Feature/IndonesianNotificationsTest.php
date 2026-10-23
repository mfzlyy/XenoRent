<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndonesianNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_validation_messages_are_in_indonesian(): void
    {
        $this->post(route('register.store'), [])
            ->assertSessionHasErrors([
                'name' => 'nama wajib diisi.',
                'email' => 'email wajib diisi.',
                'password' => 'kata sandi wajib diisi.',
            ]);
    }

    public function test_duplicate_category_notification_is_in_indonesian(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        CategoryEloquent::create(['name' => 'Kargo']);

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Kargo'])
            ->assertSessionHasErrors(['name' => 'Nama kategori sudah dipakai.']);
    }
}
