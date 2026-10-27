@extends('layouts.app')
@section('title', 'Validasi register')
@section('content')
<section class="page-heading">
    <p class="eyebrow">ADMIN / PENDAFTARAN</p>
    <h1>Validasi register</h1>
    <p>Periksa data pendaftar dan setujui akun agar anggota dapat masuk.</p>
</section>

@if($pendingAccounts->isEmpty())
    <div class="empty-panel">
        <h2>Tidak ada pendaftaran yang menunggu.</h2>
        <p>Pendaftaran baru akan muncul di sini setelah formulir dikirim.</p>
    </div>
@else
    <div class="record-list">
        @foreach($pendingAccounts as $account)
            <article class="record-card">
                <div class="record-summary">
                    <div>
                        <span class="unit-code">{{ $account->email }}</span>
                        <h3>{{ $account->name }}</h3>
                        <p>{{ $account->profile?->phone }} · {{ $account->profile?->address }}</p>
                        <p class="muted">Mendaftar {{ $account->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <form method="post" action="{{ route('admin.nadhif-validasi-akun.approve', $account->id) }}" data-confirm="Setujui akun {{ $account->name }}?">
                        @csrf
                        <button class="button button-primary" type="submit">Setujui akun</button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $pendingAccounts->links() }}</div>
@endif
@endsection
