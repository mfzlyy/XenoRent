@extends('layouts.app')
@section('title', 'Daftar anggota')
@section('content')
<section class="page-heading"><p class="eyebrow">PENDAFTARAN ANGGOTA</p><h1>Buat akun hanggar</h1><p>Setelah mendaftar, akun perlu disetujui Admin sebelum bisa digunakan untuk masuk.</p></section>
<form class="panel form-grid" method="post" action="{{ route('register.store') }}">
    @csrf
    <div class="field"><label for="name">Nama lengkap <span>Wajib</span></label><input id="name" name="name" value="{{ old('name') }}" maxlength="100" autocomplete="name" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field"><label for="email">Email <span>Wajib</span></label><input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field"><label for="password">Kata sandi <span>Wajib, minimal 8 karakter</span></label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>@error('password')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field"><label for="password_confirmation">Ulangi kata sandi <span>Wajib</span></label><input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
    <div class="field"><label for="phone">Telepon <span>10-15 angka</span></label><input id="phone" name="phone" value="{{ old('phone') }}" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" minlength="10" maxlength="15" autocomplete="tel" required>@error('phone')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field field-wide"><label for="address">Alamat <span>Wajib</span></label><textarea id="address" name="address" rows="3" maxlength="500" autocomplete="street-address" required>{{ old('address') }}</textarea>@error('address')<small class="field-error">{{ $message }}</small>@enderror</div>
    <div class="field-wide form-actions"><button class="button button-primary" type="submit">Daftar sebagai anggota</button><a class="button button-soft" href="{{ route('login') }}">Kembali ke login</a></div>
</form>
@endsection
