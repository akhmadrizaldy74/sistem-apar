<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pesanan->invoiceTitle() }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        h2 { font-size: 14px; margin: 0 0 10px; }
        p { margin: 0 0 6px; }
        .row { width: 100%; margin-bottom: 22px; }
        .col { display: inline-block; vertical-align: top; width: 49%; }
        .box { border: 1px solid #d1d5db; padding: 12px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 10px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .total { font-size: 15px; font-weight: bold; }
        @include('pdf.partials.letterhead-styles')
        .doc-title { font-size: 18px; margin: 0 0 4px; }
        .doc-meta { margin: 0 0 14px; font-size: 11px; color: #374151; }
    </style>
</head>
<body>
    @include('pdf.partials.letterhead')
    <h1 class="doc-title">{{ $pesanan->invoiceTitle() }}</h1>
    <p>Tanggal Transaksi: {{ $pesanan->displayTransactionDateTime() }}</p>
    <p style="font-size: 10px; color: #cbd5e1;">Nomor referensi internal: {{ $pesanan->invoiceDisplayNumber() }}</p>

    <div class="row">
        <div class="col">
            <div class="box">
                <h2>Data Pelanggan</h2>
                <p><strong>Nama:</strong> {{ $pesanan->pelanggan?->nama ?? '-' }}</p>
                <p><strong>No. WhatsApp:</strong> {{ $pesanan->pelanggan?->no_wa ?? '-' }}</p>
                <p><strong>Alamat:</strong> {{ $pesanan->pelanggan?->alamat ?? '-' }}</p>
            </div>
        </div>
        <div class="col">
            <div class="box">
                <h2>Informasi Pesanan</h2>
                <p><strong>Metode Pemesanan:</strong> WhatsApp</p>
                <p><strong>Tipe Pesanan:</strong> Pesanan Produk</p>
                <p><strong>Total Item:</strong> {{ $pesanan->details->count() }} varian</p>
                <p><strong>Total Unit:</strong> {{ $pesanan->details->sum('jumlah') }} unit</p>
                <p><strong>Total Transaksi:</strong> Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Merek</th>
                <th>Jenis</th>
                <th>Kapasitas</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesanan->details as $detail)
                <tr>
                    <td>{{ $detail->produk?->nama ?? 'Produk Terhapus' }}</td>
                    <td>{{ $detail->merek }}</td>
                    <td>{{ $detail->produk?->jenisApar?->nama ?? '-' }}</td>
                    <td>{{ $detail->kapasitas }}</td>
                    <td>{{ $detail->jumlah }} unit</td>
                    <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <tbody>
            @php
                $pricingSummary = $pesanan->pricingSummary();
            @endphp
            <tr>
                <td class="text-right" style="padding-right: 15px;">Subtotal</td>
                <td class="text-right" style="width: 180px;">Rp {{ number_format((float) $pricingSummary['subtotalProduk'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-right" style="padding-right: 15px;">Ongkos Kirim</td>
                <td class="text-right" style="width: 180px;">Rp {{ number_format((float) $pricingSummary['ongkir'], 0, ',', '.') }}</td>
            </tr>
            @if((float) $pricingSummary['nominalDiskon'] > 0)
            <tr>
                <td class="text-right" style="padding-right: 15px; color: #16a34a; font-weight: bold;">Promo Diskon Pembelian Banyak</td>
                <td class="text-right" style="width: 180px; color: #16a34a; font-weight: bold;">- Rp {{ number_format((float) $pricingSummary['nominalDiskon'], 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-right total" style="padding-right: 15px; border-top: 2px solid #000;">Total Invoice</td>
                <td class="text-right total" style="width: 180px; border-top: 2px solid #000;">Rp {{ number_format((float) $pricingSummary['totalPembayaran'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
