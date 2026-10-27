@php
    $carbon = \Illuminate\Support\Carbon::class;

    if ($month === null) {
        $dari = $rows->min('rented_at');
        $sampai = $rows->max('rented_at');
        $keterangan = ' (seluruh data)';
    } elseif (strlen($month) === 4) {
        $dari = $carbon::createFromFormat('!Y', $month)->startOfYear();
        $sampai = $dari->copy()->endOfYear();
        $keterangan = '';
    } else {
        $dari = $carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $sampai = $dari->copy()->endOfMonth();
        $keterangan = '';
    }
@endphp
@if ($dari && $sampai)
    <p>Rentang tanggal sewa: {{ $dari->translatedFormat('j F Y') }} s/d {{ $sampai->translatedFormat('j F Y') }}{{ $keterangan }}</p>
@endif