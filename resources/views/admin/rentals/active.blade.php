@extends('layouts.app')
@section('title', 'Sewa aktif')
@section('content')
<section class="page-heading"><p class="eyebrow">ADMIN / OPERASI PENGEMBALIAN</p><h1>Sewa aktif</h1><p>Catat pengembalian di hanggar. Anggota menghubungi Admin untuk menyerahkan unit.</p></section>
@if ($rentals->isEmpty())
    <div class="empty-panel">
        <p class="eyebrow">HANGGAR TENANG</p>
        <h2>Tidak ada unit yang sedang disewa.</h2>
        <p>Sewa aktif akan tampil di sini saat anggota mengambil kapal.</p>
    </div>
@else
    @include('admin.partials.rental-table', ['rentals' => $rentals, 'showAction' => true])
    <div class="pagination-wrap">{{ $rentals->links() }}</div>
@endif
@endsection
