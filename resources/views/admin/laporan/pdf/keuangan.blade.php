<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        p { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        @include('pdf.partials.letterhead-styles')
    </style>
</head>
<body>
    @include('pdf.partials.letterhead')
    <h1>Laporan Keuangan</h1>
    <p>Periode: {{ $periode }}</p>
    <p>Total pemasukan: Rp {{ number_format($totals['total_pemasukan'], 0, ',', '.') }}</p>
    <p>Total transaksi: {{ $totals['total_transaksi'] }}</p>
    <p>Total pengeluaran: Rp {{ number_format($totals['total_pengeluaran'], 0, ',', '.') }}</p>
    <p>Laba bersih: Rp {{ number_format($totals['laba_bersih'], 0, ',', '.') }}</p>
    <p>Rincian pemasukan: Produk Rp {{ number_format($incomeBreakdown['produk'], 0, ',', '.') }}, Refill Rp {{ number_format($incomeBreakdown['refill'], 0, ',', '.') }}, Service Rp {{ number_format($incomeBreakdown['service'], 0, ',', '.') }}</p>
    @if(!empty($expenseBreakdown))
        <p>Rincian pengeluaran:
            {{ collect($expenseBreakdown)->map(fn ($amount, $label) => $label . ' Rp ' . number_format($amount, 0, ',', '.'))->implode(' | ') }}
        </p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th>Sumber</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record['tanggal_label'] }}</td>
                    <td>{{ $record['jenis'] }}</td>
                    <td>{{ $record['keterangan'] }}</td>
                    <td>{{ $record['pelanggan'] }}</td>
                    <td>{{ $record['status'] }}</td>
                    <td>{{ $record['source'] }}</td>
                    <td style="color: {{ $record['direction'] === 'in' ? 'green' : 'red' }};">
                        {{ $record['direction'] === 'in' ? '' : '- ' }}Rp {{ number_format($record['nominal'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada data keuangan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
