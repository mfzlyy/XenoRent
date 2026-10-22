@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@if(auth()->user()->role === 'admin')
    <section class="page-heading heading-row"><div><p class="eyebrow">KONTROL HANGGAR / ADMIN</p><h1>Panel operasional</h1><p>Pantau armada dan transaksi sewa yang perlu ditangani.</p></div><span class="role-stamp">ADMIN / AKSES PENUH</span></section>
    <section class="metric-grid" aria-label="Ringkasan hanggar"><article class="metric-panel"><span>Unit tersedia</span><strong>{{ $availableUnits }}</strong><small>Siap disewa anggota</small></article><article class="metric-panel"><span>Sewa aktif</span><strong>{{ $activeRentals }}</strong><small>Menunggu pengembalian</small></article><article class="metric-panel"><span>Anggota</span><strong>{{ $memberCount }}</strong><small>Akun terdaftar</small></article></section>
    <section class="content-section"><div class="section-heading"><div><p class="eyebrow">PERLU DITANGANI</p><h2>Sewa yang sedang berjalan</h2></div><a class="text-link" href="{{ route('admin.rentals.active') }}">Buka daftar sewa aktif</a></div>
        @if($upcomingRentals->isEmpty())
            <div class="empty-panel"><h3>Tidak ada kapal yang sedang disewa.</h3><p>Transaksi aktif akan muncul di sini.</p></div>
        @else
            @include('admin.partials.rental-table', ['rentals' => $upcomingRentals, 'showAction' => true])
        @endif

    </section>
@else
    <section class="member-welcome"><div><p class="eyebrow">DASHBOARD ANGGOTA / HANGGAR 07</p><h1>Siap berangkat, {{ auth()->user()->name }}?</h1><p>Cari unit tersedia untuk rencana perjalanan antargalaksimu.</p><a class="button button-primary" href="{{ route('units.index') }}">Cari kapal tersedia</a></div><div class="quota-panel"><span>SISA KUOTA SEWA</span><strong>{{ $rentalLimit - $activeRentalCount }} <small>/ {{ $rentalLimit }}</small></strong><p>unit yang bisa disewa bersamaan</p></div></section>
    <section class="content-section"><div class="section-heading"><div><p class="eyebrow">AKTIVITAS ANGGOTA</p><h2>Sewa saya</h2></div><a class="text-link" href="{{ route('rentals.mine') }}">Lihat semua sewa</a></div>
        @if($myRentals->isEmpty())
            <div class="empty-panel"><h3>Belum ada kapal yang disewa.</h3><p>Jelajahi daftar unit hanggar untuk memulai.</p><a class="text-link" href="{{ route('units.index') }}">Buka daftar unit</a></div>
        @else
            @include('rentals.partials.rental-table', ['rentals' => $myRentals])
        @endif
    </section>
@endif
@endsection
