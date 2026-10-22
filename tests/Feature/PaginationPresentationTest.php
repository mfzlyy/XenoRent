<?php

namespace Tests\Feature;

use App\Infrastructure\Eloquent\UnitEloquent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_pagination_renders_one_compact_navigation_set(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        foreach (range(1, 13) as $number) {
            UnitEloquent::create([
                'code' => 'PAGE-'.$number,
                'name' => 'Pagination unit '.$number,
                'status' => 'tersedia',
            ]);
        }

        $response = $this->actingAs($member)->get(route('units.index'));
        $html = $response->getContent();

        $response->assertOk()
            ->assertSee('pagination-nav', false)
            ->assertSee('Sebelumnya')
            ->assertSee('Berikutnya')
            ->assertSee('page=2');

        $this->assertSame(1, substr_count($html, 'Sebelumnya'));
        $this->assertSame(1, substr_count($html, 'Berikutnya'));
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringNotContainsString('sm:hidden', $html);
    }
}
