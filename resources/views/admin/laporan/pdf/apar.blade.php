<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan APAR</title>
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
    <h1>Laporan APAR</h1>
    <p>Filter tanggal: {{ $filters['tanggal_dari'] ?? '-' }} s/d {{ $filters['tanggal_sampai'] ?? '-' }}</p>
    <table>
        <thead>
            <tr>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th>Tanggal Beli</th>
                <th>Tanggal Expired</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
                <tr>
                    <td>{{ $unit->pelanggan->nama }}</td>
                    <td>{{ $unit->produk?->nama ?? '-' }}</td>
                    <td>{{ optional($unit->tgl_beli)->format('d-m-Y') }}</td>
                    <td>{{ $unit->tgl_expired->format('d-m-Y') }}</td>
                    <td>{{ $unit->tgl_expired->isPast() ? 'Expired' : 'Aktif' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
