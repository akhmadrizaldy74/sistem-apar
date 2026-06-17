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
    <p>Periode: {{ $periode }}</p>
    <p>Total biaya: Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Jenis Service</th>
                <th>Unit</th>
                <th>Jumlah Unit</th>
                <th>Peralatan</th>
                <th>Teknisi</th>
                <th>Status</th>
                <th>Sumber</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceRows as $service)
                <tr>
                    <td>{{ $service['tanggal_label'] }}</td>
                    <td>{{ $service['pelanggan'] }}</td>
                    <td>{{ $service['jenis_service'] }}</td>
                    <td>{{ $service['unit'] }}</td>
                    <td>{{ $service['jumlah_unit'] }} unit</td>
                    <td>{{ $service['peralatan'] }}</td>
                    <td>{{ $service['teknisi'] }}</td>
                    <td>{{ $service['status'] }}</td>
                    <td>{{ $service['source'] }}</td>
                    <td>Rp {{ number_format($service['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
