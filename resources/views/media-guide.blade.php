@extends('layouts.app')
@section('title', 'Panduan Media')
@section('content')
<section class="page-heading">
    <p class="eyebrow">PANDUAN INTERAKTIF</p>
    <h1>Contoh audio notifikasi</h1>
    <p>Pratinjau suara singkat untuk demonstrasi fitur multimedia XenoRent.</p>
</section>
<section class="panel">
    <h2>Simulasi notifikasi rental</h2>
    <p>Gunakan kontrol pemutar untuk memutar atau menghentikan audio. Audio tidak diputar otomatis.</p>
    <audio class="media-player" controls preload="metadata" aria-label="Pratinjau audio notifikasi rental">
        <source src="{{ asset('audio/rental-signal.wav') }}" type="audio/wav">
        Browser ini tidak mendukung pemutar audio HTML5.
    </audio>
    <p class="media-note">Format: WAV PCM mono, 22.05 kHz. Pemutaran menggunakan kontrol audio bawaan browser; fitur ini tidak mengirim suara tanpa tindakan pengguna.</p>
</section>
@endsection
