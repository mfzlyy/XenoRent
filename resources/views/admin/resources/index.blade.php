@extends('layouts.app')
@section('title', 'Pemantauan Resource')
@section('content')
<section class="page-heading">
    <p class="eyebrow">ADMIN / OPERASIONAL</p>
    <h1>Pemantauan resource</h1>
    <p>Snapshot resource aplikasi pada {{ $metrics['checked_at']->format('d M Y H:i:s') }}.</p>
</section>

@if ($alerts)
    <div class="resource-alert {{ ! $metrics['database_available'] ? 'resource-alert-error' : '' }}" role="alert">
        <strong>Perlu perhatian:</strong>
        <ul>@foreach ($alerts as $alert)<li>{{ $alert }}</li>@endforeach</ul>
    </div>
@else
    <div class="alert alert-success" role="status">Semua pemeriksaan berada di bawah ambang peringatan.</div>
@endif

<div class="resource-grid">
    <section class="resource-card">
        <h2>Disk server</h2>
        @if ($metrics['disk_used_percent'] !== null)
            <p>{{ $metrics['disk_used_percent'] }}% terpakai · ambang {{ $thresholds['disk_percent'] }}%</p>
            <progress class="resource-meter" value="{{ min(100, $metrics['disk_used_percent']) }}" max="100" aria-label="Penggunaan disk"></progress>
            <p>Sisa {{ number_format($metrics['disk_free_bytes'] / 1073741824, 2) }} GB dari {{ number_format($metrics['disk_total_bytes'] / 1073741824, 2) }} GB.</p>
        @else
            <p>Data disk tidak tersedia pada lingkungan ini.</p>
        @endif
    </section>

    <section class="resource-card">
        <h2>Memori proses PHP</h2>
        @if ($metrics['memory_used_percent'] !== null)
            <p>{{ $metrics['memory_used_percent'] }}% dari batas proses · ambang {{ $thresholds['memory_percent'] }}%</p>
            <progress class="resource-meter" value="{{ min(100, $metrics['memory_used_percent']) }}" max="100" aria-label="Penggunaan memori PHP"></progress>
            <p>{{ number_format($metrics['memory_used_bytes'] / 1048576, 1) }} MB digunakan dari {{ number_format($metrics['memory_limit_bytes'] / 1048576, 0) }} MB.</p>
        @else
            <p>Batas memori PHP tidak dibatasi atau tidak dapat dibaca.</p>
        @endif
    </section>

    <section class="resource-card">
        <h2>Database</h2>
        <p>Status: <strong>{{ $metrics['database_available'] ? 'Terhubung' : 'Tidak terhubung' }}</strong></p>
        <p>File SQLite: {{ $metrics['database_size_bytes'] !== null ? number_format($metrics['database_size_bytes'] / 1048576, 2).' MB' : 'tidak tersedia (misalnya saat tes in-memory)' }}</p>
        <p>Ambang peringatan: disk {{ $thresholds['disk_percent'] }}%, memori PHP {{ $thresholds['memory_percent'] }}%.</p>
    </section>
</div>
@endsection
