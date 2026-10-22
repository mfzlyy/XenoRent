@extends('layouts.app')
@section('title', 'Akses ditolak')
@section('content')<section class="empty-panel error-page"><p class="eyebrow">403 / AREA TERBATAS</p><h1>Akses ditolak</h1><p>Akun ini tidak memiliki izin untuk membuka bagian hanggar tersebut.</p><a class="button button-primary" href="{{ route('dashboard') }}">Kembali ke dashboard</a></section>@endsection
