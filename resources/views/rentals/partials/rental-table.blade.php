<div class="table-scroll">
    <table class="data-table">
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
                    <td>
                        @if($rental->unit)
                            <strong>{{ $rental->unit->name }}</strong>
                            <small class="table-secondary">{{ $rental->unit->code }}</small>
                        @else
                            <strong>Unit tidak tersedia</strong>
                            <small class="table-secondary">Data unit tidak ditemukan</small>
                        @endif
                    </td>
                    <td>{{ $rental->rented_at->format('d M Y') }}</td>
                    <td>{{ $rental->due_at->format('d M Y') }}</td>
                    <td>{{ $rental->returned_at?->format('d M Y') ?? 'Belum dikembalikan' }}</td>
                    <td><span class="status-tag status-{{ $rental->status }}">{{ ucfirst($rental->status) }}</span></td>
                    <td>Rp{{ number_format($rental->fine_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
