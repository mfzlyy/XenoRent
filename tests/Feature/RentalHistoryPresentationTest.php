<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalHistoryPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rental_table_shows_return_date_and_pending_return_state(): void
    {
        $returnedRental = $this->makeRental([
            'returned_at' => '2025-03-12',
            'status' => 'dikembalikan',
        ]);
        $activeRental = $this->makeRental([
            'returned_at' => null,
            'status' => 'disewa',
        ]);

        $html = view('rentals.partials.rental-table', [
            'rentals' => collect([$returnedRental, $activeRental]),
        ])->render();

        $this->assertStringContainsString('Tanggal kembali', $html);
        $this->assertStringContainsString('12 Mar 2025', $html);
        $this->assertStringContainsString('Belum dikembalikan', $html);
    }

    public function test_rental_table_shows_fallback_when_unit_relation_is_missing(): void
    {
        $rental = $this->makeRental();
        $rental->setRelation('unit', null);

        $html = view('rentals.partials.rental-table', [
            'rentals' => collect([$rental]),
        ])->render();

        $this->assertStringContainsString('Unit tidak tersedia', $html);
        $this->assertStringContainsString('Data unit tidak ditemukan', $html);
    }

    public function test_admin_rental_pages_render_their_table_partials(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'anggota']);
        $unit = UnitEloquent::create([
            'code' => 'RENDER-01',
            'name' => 'Rental render unit',
            'status' => 'disewa',
        ]);
        RentalEloquent::create([
            'user_id' => $member->id,
            'unit_id' => $unit->id,
            'rented_at' => '2025-03-01',
            'due_at' => '2025-03-10',
            'status' => 'disewa',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.rentals.active'))
            ->assertOk()
            ->assertSee('Rental render unit')
            ->assertSee('Proses kembali')
            ->assertDontSee("@include('admin.partials.rental-table'");

        $this->get(route('admin.members.rentals', $member->id))
            ->assertOk()
            ->assertSee('Rental render unit')
            ->assertSee('Belum dikembalikan')
            ->assertDontSee("@include('rentals.partials.rental-table'");
    }

    public function test_print_view_shows_return_date_and_rentals_beyond_first_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'anggota']);

        foreach (range(1, 16) as $number) {
            $unit = UnitEloquent::create([
                'code' => 'HISTORY-'.$number,
                'name' => 'Riwayat kapal '.$number,
                'status' => 'tersedia',
            ]);

            RentalEloquent::create([
                'user_id' => $member->id,
                'unit_id' => $unit->id,
                'rented_at' => '2025-03-01',
                'due_at' => '2025-03-10',
                'returned_at' => '2025-03-12',
                'fine_amount' => 0,
                'status' => 'dikembalikan',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.members.rentals.print', $member->id))
            ->assertOk()
            ->assertSee('Tanggal kembali')
            ->assertSee('12 Mar 2025')
            ->assertSee('Riwayat kapal 16')
            ->assertDontSee('pagination-wrap');
    }

    private function makeRental(array $attributes = []): RentalEloquent
    {
        $rental = new RentalEloquent;
        $rental->forceFill(array_merge([
            'rented_at' => '2025-03-01',
            'due_at' => '2025-03-10',
            'returned_at' => null,
            'fine_amount' => 0,
            'status' => 'disewa',
        ], $attributes));
        $rental->setRelation('unit', new UnitEloquent([
            'name' => 'Kapal Presentasi',
            'code' => 'PRESENTASI-01',
        ]));

        return $rental;
    }
}
