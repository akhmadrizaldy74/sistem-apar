<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PURCHASE ORDER - {{ $po->nomor_po }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 20px 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .company-address {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }
        .divider {
            border-bottom: 2px solid #dc2626;
            margin: 15px 0;
        }
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 4px;
            color: #0f172a;
        }
        .doc-number {
            font-size: 13px;
            font-weight: bold;
            color: #475569;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 4px 0;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 6px;
        }
        .info-box-title {
            font-weight: bold;
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row td {
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #0f172a;
            background-color: #f1f5f9 !important;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
        .signature-space {
            height: 60px;
        }
        .system-note {
            font-size: 10px;
            color: #94a3b8;
            font-style: italic;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Perusahaan -->
    <div class="header">
        <div class="company-name">PD. ANUGRAH UTAMA</div>
        <div class="company-address">
            Peralatan & Pengisian Alat Pemadam Api Ringan (APAR)<br>
            Jl. Raya Utama No. 123, Jakarta | Telp: (021) 555-0199 / WhatsApp: 0812-3456-7890
        </div>
    </div>

    <div class="divider"></div>

    <!-- Judul Dokumen -->
    <div class="title-section">
        <div class="doc-title">PURCHASE ORDER</div>
        <div class="doc-number">No. {{ $po->nomor_po }}</div>
    </div>

    <!-- Informasi Tanggal & Supplier -->
    <table class="info-table">
        <tr>
            <td width="50%" style="padding-right: 10px;">
                <div class="info-box">
                    <div class="info-box-title">Kepada Supplier</div>
                    <strong style="font-size: 13px; color: #0f172a;">{{ $po->supplier?->nama_supplier }}</strong><br>
                    @if($po->supplier?->kontak_person)
                        <span>Attn: {{ $po->supplier->kontak_person }}</span><br>
                    @endif
                    <span>Telepon / WA: {{ $po->supplier?->no_wa }}</span><br>
                    @if($po->supplier?->alamat)
                        <span style="color: #64748b;">{{ $po->supplier->alamat }}</span>
                    @endif
                </div>
            </td>
            <td width="50%" style="padding-left: 10px;">
                <div class="info-box">
                    <div class="info-box-title">Detail Dokumen</div>
                    <strong>Tanggal PO:</strong> {{ $po->tanggal_po ? $po->tanggal_po->format('d F Y') : '-' }}<br>
                    <strong>Status PO:</strong> {{ strtoupper($po->status) }}<br>
                    @if($po->no_surat_jalan)
                        <strong>No. Surat Jalan:</strong> {{ $po->no_surat_jalan }} ({{ $po->tanggal_surat_jalan ? $po->tanggal_surat_jalan->format('d/m/Y') : '-' }})<br>
                    @endif
                    @if($po->catatan)
                        <div style="margin-top: 4px; font-style: italic; color: #475569;">
                            <strong>Catatan:</strong> {{ $po->catatan }}
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Detail Item -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="45%">Nama Item</th>
                <th class="text-center" width="15%">Kategori</th>
                <th class="text-center" width="10%">Jumlah</th>
                <th class="text-right" width="12%">Harga Satuan</th>
                <th class="text-right" width="13%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $detail->nama_item }}</strong></td>
                    <td class="text-center">{{ ucfirst($detail->kategori) }}</td>
                    <td class="text-center">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right" style="color: #dc2626;">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td>
                Supplier,<br>
                <strong>{{ $po->supplier?->nama_supplier }}</strong>
                <div class="signature-space"></div>
                ( ___________________________ )
            </td>
            <td>
                Jakarta, {{ date('d F Y') }}<br>
                Admin <strong>PD. ANUGRAH UTAMA</strong>
                <div class="signature-space"></div>
                ( ___________________________ )
            </td>
        </tr>
    </table>

    <div class="system-note">
        * Dokumen ini diterbitkan secara resmi dan otomatis oleh Sistem Informasi PD. Anugrah Utama.
    </div>

</body>
</html>
