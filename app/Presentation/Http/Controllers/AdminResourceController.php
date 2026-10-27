<?php

namespace App\Presentation\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminResourceController extends Controller
{
    private const DISK_WARNING_PERCENT = 85;

    private const PHP_MEMORY_WARNING_PERCENT = 80;

    public function index(): View
    {
        $diskTotalBytes = @disk_total_space(base_path());
        $diskFreeBytes = @disk_free_space(base_path());
        $diskTotal = is_numeric($diskTotalBytes) ? (float) $diskTotalBytes : null;
        $diskFree = is_numeric($diskFreeBytes) ? (float) $diskFreeBytes : null;
        $diskUsedPercent = $diskTotal && $diskTotal > 0 && $diskFree !== null
            ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 1)
            : null;

        $memoryUsedBytes = memory_get_usage(true);
        $memoryLimitBytes = $this->memoryLimitToBytes((string) ini_get('memory_limit'));
        $memoryUsedPercent = $memoryLimitBytes
            ? round(min(100, ($memoryUsedBytes / $memoryLimitBytes) * 100), 1)
            : null;

        $databasePath = config('database.connections.sqlite.database');
        if (is_string($databasePath) && $databasePath !== ':memory:' && ! is_file($databasePath)) {
            $databasePath = database_path($databasePath);
        }
        $databaseSizeBytes = is_string($databasePath) && is_file($databasePath)
            ? filesize($databasePath)
            : null;

        try {
            DB::connection()->select('SELECT 1');
            $databaseAvailable = true;
        } catch (Throwable) {
            $databaseAvailable = false;
        }

        $alerts = [];
        if ($diskUsedPercent !== null && $diskUsedPercent >= self::DISK_WARNING_PERCENT) {
            $alerts[] = 'Penggunaan disk mencapai ambang peringatan '.self::DISK_WARNING_PERCENT.'%.';
        }
        if ($memoryUsedPercent !== null && $memoryUsedPercent >= self::PHP_MEMORY_WARNING_PERCENT) {
            $alerts[] = 'Penggunaan memori PHP mendekati batas proses.';
        }
        if (! $databaseAvailable) {
            $alerts[] = 'Database tidak dapat dihubungi. Halaman monitoring tetap tersedia dengan status database bermasalah.';
        }

        return view('admin.resources.index', [
            'metrics' => [
                'disk_total_bytes' => $diskTotal,
                'disk_free_bytes' => $diskFree,
                'disk_used_percent' => $diskUsedPercent,
                'memory_used_bytes' => $memoryUsedBytes,
                'memory_limit_bytes' => $memoryLimitBytes,
                'memory_used_percent' => $memoryUsedPercent,
                'database_available' => $databaseAvailable,
                'database_size_bytes' => is_int($databaseSizeBytes) ? $databaseSizeBytes : null,
                'checked_at' => now(),
            ],
            'alerts' => $alerts,
            'thresholds' => [
                'disk_percent' => self::DISK_WARNING_PERCENT,
                'memory_percent' => self::PHP_MEMORY_WARNING_PERCENT,
            ],
        ]);
    }

    private function memoryLimitToBytes(string $limit): ?int
    {
        if ($limit === '' || $limit === '-1') {
            return null;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => is_numeric($limit) ? (int) $limit : null,
        };
    }
}
