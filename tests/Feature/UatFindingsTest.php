<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Infrastructure\Eloquent\CategoryEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UatFindingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_keyword_longer_than_100_characters_is_rejected_by_the_server(): void
    {
        $this->seed();
        $member = User::where('email', 'bima@xenorent.test')->firstOrFail();

        $this->actingAs($member)->get(route('units.index', ['q' => str_repeat('a', 101)]))
            ->assertOk()
            ->assertSee('Kata kunci maksimal 100 karakter.')
            ->assertSee('ZTS-001');
    }

    public function test_search_keyword_of_100_characters_is_accepted(): void
    {
        $this->seed();
        $member = User::where('email', 'bima@xenorent.test')->firstOrFail();

        $this->actingAs($member)->get(route('units.index', ['q' => str_repeat('a', 100)]))
            ->assertOk()
            ->assertDontSee('Kata kunci maksimal 100 karakter.');
    }

    public function test_unit_list_shows_ten_units_per_page(): void
    {
        $this->seed();
        UnitEloquent::create(['code' => 'EXTRA-1', 'name' => 'Extra', 'status' => 'tersedia']);
        $member = User::where('email', 'bima@xenorent.test')->firstOrFail();

        $html = $this->actingAs($member)->get(route('units.index'))->assertOk()->getContent();

        $this->assertSame(10, substr_count($html, 'class="unit-card"'));
    }

    public function test_duplicate_email_message_follows_prd(): void
    {
        $this->seed();

        $this->post(route('register.store'), [
            'name' => 'Rina Palsu',
            'email' => 'rina@xenorent.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'phone' => '081234567891',
            'address' => 'Jl. Bulan No. 1',
        ])->assertSessionHasErrors(['email' => 'Email sudah terdaftar.']);
    }

    public function test_failed_member_creation_keeps_input_and_shows_errors_under_fields(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();

        $this->actingAs($admin)->from(route('admin.members.index'))->post(route('admin.members.store'), [
            '_form' => 'tambah',
            'name' => 'Tala Pulsar',
            'email' => 'rina@xenorent.test',
            'phone' => '081311112222',
            'address' => 'Jl. Pulsar No. 3',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ])->assertRedirect(route('admin.members.index'));

        $html = $this->actingAs($admin)->get(route('admin.members.index'))->assertOk()->getContent();

        $this->assertStringContainsString('value="081311112222"', $html);
        $this->assertStringContainsString('value="Jl. Pulsar No. 3"', $html);
        $this->assertStringContainsString('Email sudah terdaftar.', $html);
        $this->assertStringContainsString('field-error', $html);
    }

    public function test_return_and_rent_rejections_use_prd_messages(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();
        $bima = User::where('email', 'bima@xenorent.test')->firstOrFail();
        $returned = RentalEloquent::where('status', 'dikembalikan')->firstOrFail();
        $rentedUnit = UnitEloquent::where('code', 'ZTS-001')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.rentals.return', $returned->id))
            ->assertSessionHasErrors(['rental' => 'Sewa sudah dikembalikan']);

        $this->actingAs($bima)->post(route('rentals.store', $rentedUnit->id))
            ->assertSessionHasErrors(['rental' => 'Unit sedang disewa']);
    }

    public function test_admin_cannot_delete_member_with_active_rental_and_sees_prd_message(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();
        $rina = User::where('email', 'rina@xenorent.test')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.members.destroy', $rina->id))
            ->assertSessionHas('error', 'Anggota masih punya sewa aktif, tidak bisa dihapus');
    }

    private function otherAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email' => 'admin2@xenorent.test', 'name' => 'Admin Kedua']);
    }

    public function test_admin_cannot_update_another_admin_account_through_member_endpoint(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();
        $other = $this->otherAdmin();

        $this->actingAs($admin)->put(route('admin.members.update', $other->id), [
            'name' => 'Diubah Paksa',
            'email' => 'admin2@xenorent.test',
            'phone' => '081200009999',
            'address' => 'Alamat paksa',
        ])->assertNotFound();

        $this->assertSame('Admin Kedua', $other->fresh()->name);
    }

    public function test_invalid_update_of_admin_account_is_still_not_found_instead_of_validation_error(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();
        $other = $this->otherAdmin();

        $this->actingAs($admin)->put(route('admin.members.update', $other->id), [])->assertNotFound();
    }

    public function test_admin_cannot_delete_an_admin_account_through_member_endpoint(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();
        $other = $this->otherAdmin();

        $this->actingAs($admin)->delete(route('admin.members.destroy', $other->id))->assertNotFound();
        $this->actingAs($admin)->delete(route('admin.members.destroy', $admin->id))->assertNotFound();

        $this->assertNotNull(User::find($other->id));
        $this->assertNotNull(User::find($admin->id));
    }

    public function test_member_rental_history_and_print_are_not_available_for_admin_accounts(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();
        $other = $this->otherAdmin();

        $this->actingAs($admin)->get(route('admin.members.rentals', $other->id))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.members.rentals.print', $other->id))->assertNotFound();
    }

    public function test_member_endpoints_still_work_for_member_accounts(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();
        $bima = User::where('email', 'bima@xenorent.test')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.members.rentals', $bima->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.members.rentals.print', $bima->id))->assertOk();

        $this->actingAs($admin)->put(route('admin.members.update', $bima->id), [
            'name' => 'Bima Nebula Prime',
            'email' => 'bima@xenorent.test',
            'phone' => '081377778888',
            'address' => 'Jl. Galaksi No. 5',
        ])->assertSessionHas('success', 'Anggota berhasil diperbarui');
        $this->assertSame('Bima Nebula Prime', $bima->fresh()->name);

        $this->actingAs($admin)->delete(route('admin.members.destroy', $bima->id))
            ->assertSessionHas('success', 'Anggota berhasil dihapus');
        $this->actingAs($admin)->get(route('admin.members.rentals', $bima->id))->assertOk();
    }

    public function test_category_can_be_created_and_updated_without_description_field(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@xenorent.test')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Tanpa Deskripsi'])
            ->assertSessionHas('success', 'Kategori berhasil ditambahkan');
        $category = CategoryEloquent::where('name', 'Tanpa Deskripsi')->firstOrFail();
        $this->assertNull($category->description);

        $this->actingAs($admin)->put(route('admin.categories.update', $category->id), ['name' => 'Tanpa Deskripsi Baru'])
            ->assertSessionHas('success', 'Kategori berhasil diperbarui');
        $this->assertSame('Tanpa Deskripsi Baru', $category->fresh()->name);
        $this->assertNull($category->fresh()->description);
    }
}
