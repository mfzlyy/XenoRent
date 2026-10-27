@extends('layouts.app')
@section('title', 'Riwayat sewa anggota')
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
        <p class="eyebrow">ADMIN / ARSIP ANGGOTA</p>
        <h1>Riwayat sewa</h1>
        <p>{{ $member->name }} · {{ $member->email }}</p>
        <p>Periode: {{ $periode }}</p>
        @include('admin.members.partials.period-range', ['rows' => $allRentals, 'month' => $month])
    </div>
    <div class="heading-actions no-print">
        @include('admin.members.partials.period-filter', ['action' => route('admin.members.rentals', $member->id)])
        @if ($rentals->isNotEmpty())
            <a class="button button-dark" data-currency-pdf href="{{ route('admin.members.rentals.print', array_merge(['id' => $member->id, 'currency' => 'IDR'], request()->only('bulan', 'tahun'))) }}">Unduh PDF</a>
            <button class="button button-outline" type="button" data-print>Print halaman</button>
        @else
            <button class="button button-dark" type="button" disabled>PDF tidak tersedia</button>
            <button class="button button-outline" type="button" data-print disabled>Print tidak tersedia</button>
        @endif
    </div>
</section>
@if ($rentals->isEmpty())
    <div class="empty-panel empty-panel-wide">
        @if ($month)
            <h2>Tidak ada data sewa untuk periode {{ $periode }}.</h2>
            <p>Anggota ini tidak memiliki sewa pada periode tersebut. Pilih bulan atau tahun lain.</p>
        @else
            <h2>Anggota ini belum pernah menyewa.</h2>
            <p>Riwayat akan tampil setelah transaksi pertama.</p>
        @endif
    </div>
@else
    <div class="no-print">
        @include('rentals.partials.rental-table', ['rentals' => $rentals])
        <div class="pagination-wrap">{{ $rentals->links() }}</div>
    </div>
    <div class="print-only">
        @include('rentals.partials.rental-table', ['rentals' => $allRentals])
    </div>
@endif
@endsection
