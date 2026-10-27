<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RentalHistoryPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::store('file')->flush();
        Http::fake([
            '*' => Http::response([
                'amount' => 1,
                'base' => 'USD',
                'date' => '2026-10-27',
                'rates' => ['IDR' => 16000],
            ]),
        ]);
    }

    public function test_authenticated_user_can_fetch_the_latest_usd_idr_rate(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'anggota']))
            ->getJson(route('currency.usd-idr'))
            ->assertOk()
            ->assertJsonPath('rate', 16000)
            ->assertJsonPath('date', '2026-10-27');
    }

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
                'fine_amount' => 20_000,
                'status' => 'dikembalikan',
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.members.rentals.print', $member->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        $usdResponse = $this->get(route('admin.members.rentals.print', [
            'id' => $member->id,
            'currency' => 'USD',
        ]));

        $usdResponse->assertOk();
        $usdResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $usdResponse->getContent());
        $this->assertNotSame($response->getContent(), $usdResponse->getContent());

        $rental = RentalEloquent::with('unit')->where('user_id', $member->id)->firstOrFail();
        $currencyData = [
            'member' => $member,
            'rentals' => collect([$rental]),
            'usdIdrRate' => 16000,
            'usdIdrDate' => '2026-10-27',
        ];
        $usdHtml = view('admin.members.rentals-print', $currencyData + ['currency' => 'USD'])->render();
        $idrHtml = view('admin.members.rentals-print', $currencyData + ['currency' => 'IDR'])->render();

        $this->assertStringContainsString('Denda ditampilkan dalam USD', $usdHtml);
        $this->assertStringContainsString('$1.25', $usdHtml);
        $this->assertStringContainsString('Denda ditampilkan dalam Rupiah', $idrHtml);
        $this->assertStringContainsString('Rp20.000', $idrHtml);
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
