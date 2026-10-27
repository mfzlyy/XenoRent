<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceAndMediaEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_resource_metrics_and_thresholds(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.resources.index'))
            ->assertOk()
            ->assertSee('Pemantauan resource')
            ->assertSee('Disk server')
            ->assertSee('Memori proses PHP')
            ->assertSee('Database')
            ->assertViewHas('metrics', fn (array $metrics): bool => array_key_exists('disk_used_percent', $metrics)
                && array_key_exists('memory_used_percent', $metrics)
                && $metrics['database_available'])
            ->assertViewHas('thresholds', ['disk_percent' => 85, 'memory_percent' => 80]);
    }

    public function test_member_cannot_view_admin_resource_monitor(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->get(route('admin.resources.index'))
            ->assertForbidden();
    }

    public function test_authenticated_member_can_open_multimedia_audio_demo(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->get(route('media.guide'))
            ->assertOk()
            ->assertSee('Contoh audio notifikasi')
            ->assertSee('audio/rental-signal.wav');
    }
}
