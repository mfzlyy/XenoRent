@extends('layouts.app')
@section('title', 'Data tidak ditemukan')
@section('content')<section class="empty-panel error-page"><p class="eyebrow">404 / DATA TIDAK DITEMUKAN</p><h1>Unit ini tidak ada di radar.</h1><p>Data mungkin sudah dihapus atau alamatnya tidak tepat.</p><a class="button button-primary" href="{{ route('units.index') }}">Kembali ke daftar unit</a></section>@endsection
