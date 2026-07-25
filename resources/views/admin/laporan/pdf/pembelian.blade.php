<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian Barang & Stok (PO)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .text-right { text-align: right; }
        @include('pdf.partials.letterhead-styles')
    </style>
</head>
<body>
    @include('pdf.partials.letterhead')
    <h1>Laporan Pembelian Barang & Stok (PO)</h1>
    <p>Periode: {{ $periode }}</p>
    <p>Total PO: {{ $stats['total_po'] }} | Total Nominal Belanja: Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}</p>

    <table>
        <thead>
            <tr>
                <th>No PO</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>No Surat Jalan</th>
                <th>Status</th>
                <th>Rincian Item</th>
                <th class="text-right">Total Belanja</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $po)
                <tr>
                    <td><strong>{{ $po->nomor_po }}</strong></td>
                    <td>{{ $po->tanggal ? $po->tanggal->format('d/m/Y') : $po->created_at->format('d/m/Y') }}</td>
                    <td>{{ $po->supplier?->nama_supplier ?? '-' }}</td>
                    <td>{{ $po->no_surat_jalan ?: '-' }}</td>
                    <td>{{ ucfirst($po->status) }}</td>
                    <td>
                        @foreach($po->details as $item)
                            <div>• {{ $item->nama_item }} ({{ $item->jumlah }}) @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</div>
                        @endforeach
                    </td>
                    <td class="text-right"><strong>Rp {{ number_format($po->total, 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data pembelian (Purchase Order) sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
