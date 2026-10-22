@extends('layouts.app')
@section('title', 'Profil saya')
@section('content')
<section class="page-heading"><p class="eyebrow">AKUN / DATA ANGGOTA</p><h1>Profil saya</h1><p>Perbarui informasi kontak pada akun XenoRent.</p></section>
<form class="panel form-grid form-grid-single" method="post" action="{{ route('profile.update') }}">
    @csrf @method('put')
    <div class="field"><label for="name">Nama lengkap <span>Wajib</span></label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" maxlength="100" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field"><label for="email">Email <span>Wajib</span></label><input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" maxlength="255" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field"><label for="phone">Telepon <span>10-15 angka</span></label><input id="phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" value="{{ old('phone', auth()->user()->profile?->phone) }}" minlength="10" maxlength="15" required>@error('phone')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field"><label for="address">Alamat <span>Wajib</span></label><textarea id="address" name="address" rows="4" maxlength="500" required>{{ old('address', auth()->user()->profile?->address) }}</textarea>@error('address')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="form-actions"><button class="button button-primary" type="submit">Simpan profil</button></div>
</form>
@endsection
