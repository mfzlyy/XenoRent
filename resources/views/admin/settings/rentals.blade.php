@extends('layouts.app')
@section('title', 'Pengaturan sewa')
@section('content')
<section class="page-heading">
    <p class="eyebrow">ADMIN / KEBIJAKAN SEWA</p>
    <h1>Pengaturan sewa</h1>
    <p>Atur jumlah maksimal unit yang dapat disewa aktif oleh setiap anggota.</p>
</section>

<section class="content-section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">BATAS GLOBAL</p>
            <h2>Maksimal sewa aktif</h2>
        </div>
    </div>

    <form class="panel form-grid form-grid-single" method="post" action="{{ route('admin.rental-settings.update') }}">
        @csrf
        @method('put')
        <div class="field">
            <label for="max-active-rentals">Batas unit per anggota <span>1 sampai 100</span></label>
            <input id="max-active-rentals" name="max_active_rentals" type="number" min="1" max="100" step="1" value="{{ old('max_active_rentals', $maxActiveRentals) }}" required>
            @error('max_active_rentals')<small class="field-error">{{ $message }}</small>@enderror
        </div>
        <div class="form-actions">
            <button class="button button-primary" type="submit">Simpan batas sewa</button>
        </div>
        <p class="field-wide media-note">
            Pengaturan ini berlaku untuk semua anggota. Sewa yang sedang berjalan tidak dibatalkan jika batas diturunkan. Anggota yang sudah mencapai batas baru tidak dapat menyewa unit tambahan sampai jumlah sewanya berada di bawah batas.
        </p>
    </form>
</section>
@endsection
