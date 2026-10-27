@extends('layouts.app')
@section('title', 'Riwayat sewa anggota')
@section('content')
<section class="page-heading heading-row"><div><p class="eyebrow">ADMIN / ARSIP ANGGOTA</p><h1>Riwayat sewa</h1><p>{{ $member->name }} · {{ $member->email }}</p></div><a class="button button-dark" data-currency-pdf href="{{ route('admin.members.rentals.print', ['id' => $member->id, 'currency' => 'IDR']) }}">Unduh PDF riwayat</a></section>
@if ($rentals->isEmpty())
    <div class="empty-panel">
        <h2>Anggota ini belum pernah menyewa.</h2>
        <p>Riwayat akan tampil setelah transaksi pertama.</p>
        <button class="button button-soft" type="button" disabled>PDF tidak tersedia</button>
    </div>
@else
    @include('rentals.partials.rental-table', ['rentals' => $rentals])
    <div class="pagination-wrap">{{ $rentals->links() }}</div>
@endif
@endsection
