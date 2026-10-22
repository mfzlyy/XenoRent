<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Infrastructure\Eloquent\UserEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistIssuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_allows_current_email_and_rejects_another_users_email(): void
    {
        $user = User::factory()->create(['email' => 'current@example.com']);
        $other = User::factory()->create(['email' => 'other@example.com']);
        $this->actingAs($user);
        $payload = ['name' => $user->name, 'email' => $user->email, 'phone' => '081234567890', 'address' => 'Address'];
        $this->put(route('profile.update'), $payload)->assertSessionHasNoErrors();
        $payload['email'] = $other->email;
        $this->put(route('profile.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_member_search_query_is_kept_on_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(20)->create(['name' => 'Needle member']);
        $this->actingAs($admin)->get(route('admin.members.index', ['q' => 'Needle']))
            ->assertOk()->assertSee('q=Needle&amp;page=2', false);
    }

    public function test_admin_can_view_rental_history_for_soft_deleted_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = UserEloquent::create(['name' => 'Former Member', 'email' => 'former@example.test', 'password' => 'password', 'role' => 'anggota']);
        $unit = UnitEloquent::create(['code' => 'HIST-1', 'name' => 'History Unit', 'status' => 'tersedia']);
        RentalEloquent::create(['user_id' => $member->id, 'unit_id' => $unit->id, 'rented_at' => today(), 'due_at' => today()->addDays(2), 'status' => 'dikembalikan']);
        $member->delete();
        $this->actingAs($admin)->get(route('admin.members.rentals', $member->id))->assertOk()->assertSee('Former Member');
    }

    public function test_soft_deleted_user_cannot_authenticate_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'anggota', 'password' => 'password123']);
        $credentials = ['email' => $member->email, 'password' => 'password123'];

        $this->actingAs($admin)->delete(route('admin.members.destroy', $member->id))->assertRedirect();
        auth()->logout();

        $this->post(route('login.store'), $credentials)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unit_deletion_refuses_rented_unit_without_soft_deleting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = UserEloquent::create(['name' => 'Member', 'email' => 'member@example.test', 'password' => 'password', 'role' => 'anggota']);
        $unit = UnitEloquent::create(['code' => 'LOCK-1', 'name' => 'Locked Unit', 'status' => 'tersedia']);
        RentalEloquent::create(['user_id' => $user->id, 'unit_id' => $unit->id, 'rented_at' => today(), 'due_at' => today()->addDays(2), 'status' => 'disewa']);
        $this->actingAs($admin)->delete(route('admin.units.destroy', $unit->id));
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'deleted_at' => null]);
    }

    public function test_unit_can_be_created_without_description(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = CategoryEloquent::create(['name' => 'Test category']);
        $this->actingAs($admin)->post(route('admin.units.store'), ['code' => 'NULL-DESC', 'name' => 'No description', 'category_ids' => [$category->id]])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('units', ['code' => 'NULL-DESC', 'description' => null]);
    }
}
