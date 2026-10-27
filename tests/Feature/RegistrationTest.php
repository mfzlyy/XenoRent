<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_registration_creates_an_unapproved_account_and_profile(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New Member',
            'email' => 'new-member@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'address' => '1 Main Street',
        ]);

        $user = User::where('email', 'new-member@example.com')->firstOrFail();

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();
        $this->assertFalse($user->is_approved);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'phone' => '081234567890',
            'address' => '1 Main Street',
        ]);
    }

    public function test_pending_account_cannot_log_in_until_an_admin_approves_it(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Member',
            'email' => 'new-member@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'address' => '1 Main Street',
        ]);

        $this->post(route('login.store'), [
            'email' => 'new-member@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors(['email' => 'Akun kamu belum disetujui Admin. Silakan coba lagi setelah validasi.']);

        $this->assertGuest();
    }

    public function test_admin_can_approve_pending_registration_before_member_logs_in(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Member',
            'email' => 'new-member@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'address' => '1 Main Street',
        ]);

        $member = User::where('email', 'new-member@example.com')->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.nadhif-validasi-akun.index'))
            ->assertOk()
            ->assertSee('New Member')
            ->assertSee('Setujui akun');

        $this->post(route('admin.nadhif-validasi-akun.approve', $member->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'is_approved' => true,
        ]);

        $this->post(route('logout'));
        $this->post(route('login.store'), [
            'email' => 'new-member@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($member);
    }
}
