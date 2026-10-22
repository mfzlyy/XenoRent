<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_registration_creates_account_and_profile_and_authenticates_user(): void
    {
        $sessionId = session()->getId();

        $response = $this->post(route('register.store'), [
            'name' => 'New Member',
            'email' => 'new-member@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'address' => '1 Main Street',
        ]);

        $user = User::where('email', 'new-member@example.com')->firstOrFail();

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'phone' => '081234567890',
            'address' => '1 Main Street',
        ]);
        $this->assertNotSame($sessionId, session()->getId());
    }
}
