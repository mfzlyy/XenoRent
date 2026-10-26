@extends('layouts.app')
@section('title', 'Riwayat sewa semua anggota')
@section('content')
@php
    $periode = match (true) {
        $month === null => 'Semua bulan',
        strlen($month) === 4 => 'Tahun '.$month,
        default => \Illuminate\Support\Carbon::createFromFormat('!Y-m', $month)->translatedFormat('F Y'),
    };
@endphp
<section class="page-heading heading-row">
    <div>
        <p class="eyebrow">ADMIN / ARSIP SEMUA ANGGOTA</p>
        <h1>Riwayat sewa</h1>
        <p>Seluruh anggota</p>
        <p>Periode: {{ $periode }}</p>
        @include('admin.members.partials.period-range', ['rows' => $allRentals])
        @if ($allRentals->isNotEmpty())
            <p>Total sewa: {{ $allRentals->count() }} · Total denda: Rp{{ number_format($allRentals->sum('fine_amount'), 0, ',', '.') }}</p>
        @endif
    </div>
    <div class="heading-actions no-print">
        @include('admin.members.partials.period-filter', ['action' => route('admin.members.rentals.all')])
        <button class="button button-dark" type="button" data-print @disabled($rentals->isEmpty())>Print riwayat</button>
    </div>
</section>
@if ($rentals->isEmpty())
    <div class="empty-panel empty-panel-wide">
        @if ($month)
            <h2>Tidak ada data sewa untuk periode {{ $periode }}.</h2>
            <p>Belum ada anggota yang menyewa pada periode tersebut. Pilih bulan atau tahun lain.</p>
        @else
            <h2>Belum ada transaksi sewa.</h2>
            <p>Riwayat akan tampil setelah ada anggota yang menyewa unit.</p>
        @endif
    </div>
@else
    <div class="no-print">
        @include('rentals.partials.rental-table', ['rentals' => $rentals, 'showMember' => true])
        <div class="pagination-wrap">{{ $rentals->links() }}</div>
    </div>
    <div class="print-only">
        @include('rentals.partials.rental-table', ['rentals' => $allRentals, 'showMember' => true])
    </div>
@endif
@endsection