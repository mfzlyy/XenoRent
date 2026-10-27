<div class="table-scroll" data-currency-table data-currency-rate-url="{{ route('currency.usd-idr') }}" data-usd-idr-rate="">
    <table class="data-table">
        <caption class="currency-caption"><span data-currency-rate-label aria-live="polite">Memuat kurs USD/IDR...</span> Sumber: Frankfurter. Denda ditampilkan dalam <span data-current-currency>Rupiah</span>.</caption>
        <thead>
            <tr>
                <th>Kapal / kode</th>
                <th>Tanggal sewa</th>
                <th>Jatuh tempo</th>
                <th>Tanggal kembali</th>
                <th>Status</th>
                <th>Denda <button class="currency-toggle" type="button" data-currency-toggle data-currency="IDR" aria-pressed="false" disabled>Tampilkan USD</button></th>
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
                    <td data-fine-amount="{{ $rental->fine_amount }}">Rp{{ number_format($rental->fine_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
