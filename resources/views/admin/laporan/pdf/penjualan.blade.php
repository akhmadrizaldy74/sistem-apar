<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Barang & Refill</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        p { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        @include('pdf.partials.letterhead-styles')
    </style>
</head>
<body>
    @include('pdf.partials.letterhead')
    <h1>Laporan Penjualan Barang & Refill</h1>
    <p>Periode: {{ $periode }}</p>
    <p>Total transaksi final: {{ $stats['total_transaksi'] }}</p>
    <p>Total pembayaran final: Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Jenis</th>
                <th>Item / Layanan</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Sumber</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction['tanggal_label'] }}</td>
                    <td>{{ $transaction['pelanggan'] }}</td>
                    <td>{{ $transaction['jenis_transaksi'] }}</td>
                    <td>{{ $transaction['item'] }}</td>
                    <td>{{ $transaction['jumlah'] }}</td>
                    <td>{{ $transaction['status'] }}</td>
                    <td>{{ $transaction['source'] }}</td>
                    <td>Rp {{ number_format($transaction['total'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data penjualan final.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
