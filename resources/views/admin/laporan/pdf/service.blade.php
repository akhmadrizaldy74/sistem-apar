<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Service</title>
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
    <h1>Laporan Service</h1>
    <p>Filter tanggal: {{ $filters['tanggal_dari'] ?? '-' }} s/d {{ $filters['tanggal_sampai'] ?? '-' }}</p>
    <p>Total biaya: Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Jenis Service</th>
                <th>Unit</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
                <tr>
                    <td>{{ $service->tgl_service->format('d-m-Y') }}</td>
                    <td>{{ $service->unitApar?->pelanggan?->nama ?? 'Unit Manual' }}</td>
                    <td>{{ $service->jenis_service }}</td>
                    <td>{{ $service->unitApar?->no_seri ?? '-' }}</td>
                    <td>Rp {{ number_format($service->biaya, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
