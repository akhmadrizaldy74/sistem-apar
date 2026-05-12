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
    <h1>Laporan Keuangan Service</h1>
    <p>Total pemasukan: Rp {{ number_format($totals['total_pemasukan'], 0, ',', '.') }}</p>
    <p>Total transaksi: {{ $totals['total_transaksi'] }}</p>
    <p>Rata-rata: Rp {{ number_format($totals['rata_rata'], 0, ',', '.') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Jenis Service</th>
                <th>Unit</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
                <tr>
                    <td>{{ $service->tgl_service->format('d-m-Y') }}</td>
                    <td>{{ $service->unitApar->pelanggan->nama }}</td>
                    <td>{{ $service->jenis_service }}</td>
                    <td>{{ $service->unitApar->no_seri }}</td>
                    <td>Rp {{ number_format($service->biaya, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
