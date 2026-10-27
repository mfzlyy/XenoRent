<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat sewa - {{ $member->name }}</title>
    <style>
        @page { size: A4 landscape; margin: 18mm; }
        body { color: #202a32; font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { margin: 0 0 4px; font-size: 24pt; }
        .eyebrow { margin: 0 0 7px; color: #7d321d; font-size: 8pt; font-weight: bold; letter-spacing: 1px; }
        .member { margin: 0 0 20px; color: #525d63; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 9px 8px; border: 1px solid #c9c8bf; text-align: left; }
        th { background: #e5e3da; font-size: 8pt; text-transform: uppercase; }
        .secondary { display: block; margin-top: 3px; color: #525d63; font-size: 8pt; }
        .empty { padding: 16px; border: 1px solid #c9c8bf; }
    </style>
</head>
<body>
    <p class="eyebrow">XENORENT / ARSIP ANGGOTA</p>
    <h1>Riwayat sewa</h1>
    <p class="member">{{ $member->name }} · {{ $member->email }}</p>
    <p class="member">Kurs konversi: @if($usdIdrRate) 1 USD = Rp{{ number_format($usdIdrRate, 2, ',', '.') }}@if($usdIdrDate) (referensi {{ $usdIdrDate }})@endif @else Kurs USD/IDR tidak tersedia @endif. Denda ditampilkan dalam {{ $currency === 'USD' ? 'USD' : 'Rupiah' }}.</p>
    @if($rentals->isEmpty())
        <p class="empty">Anggota ini belum pernah menyewa.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Kapal / kode</th>
                    <th>Tanggal sewa</th>
                    <th>Jatuh tempo</th>
                    <th>Tanggal kembali</th>
                    <th>Status</th>
                    <th>Denda</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rentals as $rental)
                    <tr>
                        <td>{{ $rental->unit?->name ?? 'Unit tidak tersedia' }}<span class="secondary">{{ $rental->unit?->code ?? 'Data unit tidak ditemukan' }}</span></td>
                        <td>{{ $rental->rented_at->format('d M Y') }}</td>
                        <td>{{ $rental->due_at->format('d M Y') }}</td>
                        <td>{{ $rental->returned_at?->format('d M Y') ?? 'Belum dikembalikan' }}</td>
                        <td>{{ ucfirst($rental->status) }}</td>
                        <td>@if($currency === 'USD' && $usdIdrRate) ${{ number_format($rental->fine_amount / $usdIdrRate, 2, '.', ',') }} @elseif($currency === 'USD') Kurs tidak tersedia @else Rp{{ number_format($rental->fine_amount, 0, ',', '.') }} @endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
