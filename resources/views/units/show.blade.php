@extends('layouts.app')
@section('title', $unit->name)
@section('content')
<a class="back-link" href="{{ route('units.index') }}">Kembali ke daftar kapal</a>
<section class="detail-layout"><div class="ship-display"><span class="display-code">{{ $unit->code }}</span><div class="ship-mark ship-mark-large" aria-hidden="true"><span>{{ substr($unit->code, 0, 3) }}</span></div><p>Gambar kapal tidak tersedia pada versi ini.</p></div><div class="detail-copy"><p class="eyebrow">DETAIL UNIT / {{ $unit->code }}</p><h1>{{ $unit->name }}</h1><span class="status-tag status-{{ $unit->status }}">{{ ucfirst($unit->status) }}</span><p class="detail-description">{{ $unit->description ?: 'Tidak ada catatan untuk unit ini.' }}</p><h2>Kategori</h2><div class="tag-list">@forelse($unit->categories as $category)<span>{{ $category->name }}</span>@empty<span>Belum berkategori</span>@endforelse</div>
@if(auth()->user()->role === 'anggota')
    <div id="sewa" class="rental-action">@if($unit->status === 'tersedia')<form method="post" action="{{ route('rentals.store', $unit) }}" data-confirm="Sewakan unit ini?"><h2>Sewa unit ini</h2><p>Jatuh tempo dihitung otomatis lima hari kalender setelah tanggal sewa.</p>@csrf<button class="button button-primary" type="submit">Sewa kapal</button></form>@else<p class="alert alert-error">Unit sedang disewa dan belum bisa dipilih.</p>@endif</div>
@endif
</div></section>
@endsection
