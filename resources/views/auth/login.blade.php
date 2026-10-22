@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<section class="auth-layout">
    <div class="auth-story">
        <p class="eyebrow">HANGGAR / AKSES ANGGOTA</p>
        <h1>Siapkan perjalanan berikutnya.</h1>
        <p>Masuk ke konsol XenoRent untuk mencari kapal yang tersedia atau mengelola armada hanggar.</p>
        <div class="story-rule"><span>07</span><p>Setiap kapal punya kode registrasi sendiri. Nama armada boleh sama, kodenya tidak.</p></div>
    </div>
    <form class="panel form-stack" method="post" action="{{ route('login.store') }}">
        @csrf
        <div><p class="eyebrow">AKUN TERDAFTAR</p><h2>Masuk ke XenoRent</h2><p class="quiet">Anggota baru? <a href="{{ route('register') }}">Buat akun anggota</a></p></div>
        <div class="field"><label for="email">Email <span>Wajib</span></label><input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="username" required autofocus>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="password">Kata sandi <span>Wajib</span></label><input id="password" type="password" name="password" autocomplete="current-password" required>@error('password')<small class="field-error">{{ $message }}</small>@enderror</div>
        <label class="check-row"><input type="checkbox" name="remember" value="1"> Tetap masuk di perangkat ini</label>
        <button class="button button-primary button-block" type="submit">Masuk</button>
    </form>
</section>
@endsection
