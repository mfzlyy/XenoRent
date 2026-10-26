@php
    $selYear = $month ? substr($month, 0, 4) : '';
    $selMonth = ($month && strlen($month) === 7) ? (int) substr($month, 5, 2) : '';
    $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $years = collect($months)->map(fn ($m) => substr($m, 0, 4))
        ->push((string) now()->year)
        ->when($selYear !== '', fn ($c) => $c->push($selYear))
        ->unique()->sortDesc()->values();
@endphp
<form class="field" method="get" action="{{ $action }}">
    <select name="bulan" aria-label="Pilih bulan" onchange="this.form.submit()">
        <option value="">Semua bulan</option>
        @foreach ($namaBulan as $no => $nama)
            <option value="{{ $no }}" @selected($selMonth === $no)>{{ $nama }}</option>
        @endforeach
    </select>
    <select name="tahun" aria-label="Pilih tahun" onchange="this.form.submit()">
        <option value="">Semua tahun</option>
        @foreach ($years as $y)
            <option value="{{ $y }}" @selected($selYear === $y)>{{ $y }}</option>
        @endforeach
    </select>
</form>