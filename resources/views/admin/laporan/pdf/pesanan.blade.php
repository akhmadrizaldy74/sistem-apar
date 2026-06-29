<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pesanan</title>
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
    <h1>Laporan Pesanan</h1>
    <p>Filter tanggal: {{ $filters['tanggal_dari'] ?? '-' }} s/d {{ $filters['tanggal_sampai'] ?? '-' }}</p>
    <p>Total transaksi: {{ $stats['total_transaksi'] }}</p>
    <p>Total item: {{ $stats['total_item'] }}</p>
    <p>Total nilai: Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Tipe</th>
                <th>Ringkasan Item</th>
                <th>Total Unit</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanans as $pesanan)
                <tr>
                    <td>{{ $pesanan->tanggal->format('d-m-Y') }}</td>
                    <td>{{ $pesanan->pelanggan?->nama ?? '-' }}</td>
                    <td>Produk</td>
                    <td>{{ $pesanan->details->pluck('produk.nama')->filter()->implode(', ') ?: 'Pesanan WhatsApp' }}</td>
                    <td>{{ $pesanan->details->sum('jumlah') }} unit</td>
                    <td>Rp {{ number_format($pesanan->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada data pesanan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
