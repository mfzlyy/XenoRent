@extends('layouts.app')
@section('title', 'Cetak riwayat sewa')
@section('content')
<section class="page-heading heading-row print-controls">
    <div>
        <p class="eyebrow">ADMIN / ARSIP ANGGOTA</p>
        <h1>Riwayat sewa</h1>
        <p>{{ $member->name }} · {{ $member->email }}</p>
        <p>Periode: {{ $month ? (strlen($month) === 4 ? 'Tahun '.$month : \Illuminate\Support\Carbon::createFromFormat('!Y-m', $month)->translatedFormat('F Y')) : 'Semua bulan' }}</p>
        @include('admin.members.partials.period-range', ['rows' => $rentals])
    </div>
    <button class="button button-dark" type="button" data-print>Print riwayat</button>
</section>
@if($rentals->isEmpty())
    <div class="empty-panel"><h2>Anggota ini belum pernah menyewa.</h2></div>
@else
    @include('rentals.partials.rental-table', ['rentals' => $rentals])
@endif
@endsection
