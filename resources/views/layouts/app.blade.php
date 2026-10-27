<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'XenoRent') | XenoRent</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
<a class="skip-link" href="#main-content">Lewati ke konten</a>
<header class="topbar">
    <a class="brand" href="{{ route('dashboard') }}"><span>XENORENT<small>SEKTOR HANGGAR 07</small></span></a>
    @auth
        <nav class="main-nav" aria-label="Navigasi utama">
            <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
            <a href="{{ route('units.index') }}" @if(request()->routeIs('units.*')) aria-current="page" @endif>Daftar Unit</a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.units.index') }}" @if(request()->routeIs('admin.units.*')) aria-current="page" @endif>Kelola Unit</a>
                <a href="{{ route('admin.categories.index') }}" @if(request()->routeIs('admin.categories.*')) aria-current="page" @endif>Kategori</a>
                <a href="{{ route('admin.members.index') }}" @if(request()->routeIs('admin.members.*')) aria-current="page" @endif>Anggota</a>
                <a href="{{ route('admin.nadhif-validasi-akun.index') }}" @if(request()->routeIs('admin.nadhif-validasi-akun.*')) aria-current="page" @endif>Validasi Register</a>
                <a href="{{ route('admin.rentals.active') }}" @if(request()->routeIs('admin.rentals.*')) aria-current="page" @endif>Sewa Aktif</a>
                <a href="{{ route('admin.resources.index') }}" @if(request()->routeIs('admin.resources.*')) aria-current="page" @endif>Resource</a>
                <a href="{{ route('admin.rental-settings.edit') }}" @if(request()->routeIs('admin.rental-settings.*')) aria-current="page" @endif>Pengaturan Sewa</a>
            @else
                <a href="{{ route('rentals.mine') }}" @if(request()->routeIs('rentals.mine')) aria-current="page" @endif>Sewa Saya</a>
            @endif
            <a href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>Profil</a>
            <a href="{{ route('media.guide') }}" @if(request()->routeIs('media.guide')) aria-current="page" @endif>Panduan Media</a>
        </nav>
        <div class="user-menu"><span>{{ auth()->user()->name }}</span><form method="post" action="{{ route('logout') }}">@csrf<button class="button button-ghost" type="submit">Keluar</button></form></div>
    @endauth
</header>
<main id="main-content" class="page-wrap">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" role="alert"><strong>Periksa isian berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer class="site-footer"><span>XENORENT / Logistik hanggar antargalaksi</span><span>Pengembalian unit diproses oleh Admin.</span></footer>
@stack('scripts')
</body>
</html>
