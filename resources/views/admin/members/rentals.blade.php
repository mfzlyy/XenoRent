@extends('layouts.app')
@section('title', 'Riwayat sewa anggota')
@section('content')
<section class="page-heading heading-row"><div><p class="eyebrow">ADMIN / ARSIP ANGGOTA</p><h1>Riwayat sewa</h1><p>{{ $member->name }} · {{ $member->email }}</p></div><button class="button button-dark" type="button" data-print>Print riwayat</button></section>
@if($rentals->isEmpty())<div class="empty-panel"><h2>Anggota ini belum pernah menyewa.</h2><p>Riwayat akan tampil setelah transaksi pertama.</p><button class="button button-soft" type="button" disabled>Print tidak tersedia</button></div>@else@include('rentals.partials.rental-table', ['rentals' => $rentals])<div class="pagination-wrap">{{ $rentals->links() }}</div>@endif
@endsection
