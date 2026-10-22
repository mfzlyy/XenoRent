@extends('layouts.app')
@section('title', 'Sewa saya')
@section('content')
<section class="page-heading"><p class="eyebrow">AKUN ANGGOTA / CATATAN SEWA</p><h1>Sewa saya</h1><p>Daftar kapal yang sedang kamu sewa dan transaksi yang sudah selesai.</p></section>
@if($rentals->isEmpty())<div class="empty-panel"><p class="eyebrow">BELUM ADA TRANSAKSI</p><h2>Kamu belum pernah menyewa unit.</h2><p>Jelajahi hanggar untuk melihat kapal yang tersedia.</p><a class="button button-primary" href="{{ route('units.index') }}">Buka daftar unit</a></div>@else@include('rentals.partials.rental-table', ['rentals' => $rentals])<div class="pagination-wrap">{{ $rentals->links() }}</div>@endif
@endsection
