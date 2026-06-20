<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pesanan->invoiceTitle() }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.4; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 0 0 8px; }
        p { margin: 0 0 5px; }
        .row { width: 100%; margin-bottom: 20px; clear: both; }
        .col { float: left; width: 49%; }
        .box { border: 1px solid #d1d5db; padding: 10px; border-radius: 6px; background-color: #f9fafb; min-height: 120px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #d1d5db; padding: 8px 6px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total { font-size: 13px; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 8px; font-size: 10px; font-weight: bold; border-radius: 4px; uppercase: true; }
        .badge-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-warning { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .header-meta { float: right; text-align: right; }
        .header-brand { float: left; }
        .report-box { border: 1px solid #e5e7eb; border-left: 4px solid #dc2626; padding: 10px; border-radius: 4px; background: #fff; margin-top: 20px; }
    </style>
</head>
<body>
    @php
        $hidePaymentBadge = $pesanan->shouldHidePaymentStatusBadge();
        $customerName = $pesanan->pelanggan?->nama ?: $pesanan->nama_penerima ?: '-';
        $customerCompany = $pesanan->pelanggan?->perusahaan;
        $customerPhone = $pesanan->pelanggan?->no_wa ?: $pesanan->nomor_wa_penerima ?: '-';
        $customerAddress = $pesanan->alamat_pengiriman ?: $pesanan->pelanggan?->alamat ?: '-';
        $shippingServiceLabel = collect([
            $pesanan->shipping_courier ? strtoupper((string) $pesanan->shipping_courier) : null,
            $pesanan->shipping_service,
        ])->filter()->implode(' - ');
        $shippingDestination = $pesanan->shipping_destination_label ?: null;
        $shippingEtd = $pesanan->shipping_etd ?: null;
        $pricingSummary = $pesanan->pricingSummary();
        $approvedAdjustment = max(0, (float) $pesanan->purchasePriceInitialTotal() - (float) ($pricingSummary['totalPembayaran'] ?? 0));
        $shippingCostLabel = $pesanan->tipe === 'service' ? 'Biaya Penjemputan' : 'Biaya Pengiriman';
        $shippingDestinationLabel = $pesanan->tipe === 'service' ? 'Lokasi Penjemputan' : 'Tujuan Ongkir';
    @endphp
    <!-- Top Brand & Invoice Metadata Header -->
    <div class="row" style="border-b: 2px solid #dc2626; padding-bottom: 15px;">
        <div class="header-brand">
            <h1 style="color: #dc2626; font-weight: 900; margin: 0;">PD. ANUGRAH UTAMA</h1>
            <p style="font-size: 9px; color: #6b7280; margin-top: 4px; line-height: 1.3;">
                Kawasan Ruko Sentra Niaga, Jl. Utama Raya Blok B No. 12<br>
                Telp/WhatsApp: 0821-2471-6109
            </p>
        </div>
        <div class="header-meta">
            <h2 style="font-size: 18px; margin: 0; color: #111827;">{{ $pesanan->invoiceTitle() }}</h2>
            <p style="font-size: 10px; color: #6b7280; margin-top: 4px;">Tanggal Transaksi: {{ $pesanan->displayTransactionDateTime() }}</p>
            <div style="margin-top: 8px;">
                @if($hidePaymentBadge)
                    <span class="badge badge-success">SELESAI FINAL</span>
                @elseif($isLunas)
                    <span class="badge badge-success">LUNAS / PAID</span>
                @else
                    <span class="badge badge-warning">INVOICE SEMENTARA</span>
                @endif
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Billing Info columns -->
    <div class="row" style="margin-top: 20px;">
        <div class="col">
            <div class="box">
                <h2>Ditagihkan Kepada:</h2>
                <p><strong>Nama:</strong> {{ $customerName }}</p>
                @if($customerCompany)
                    <p><strong>Perusahaan:</strong> {{ $customerCompany }}</p>
                @endif
                <p><strong>No. WA:</strong> {{ $customerPhone }}</p>
                @if($customerAddress !== '-')
                    <p><strong>Alamat:</strong> {{ $customerAddress }}</p>
                @endif
            </div>
        </div>
        <div class="col" style="margin-left: 2%;">
            <div class="box">
                <h2>Rincian Transaksi:</h2>
                <p><strong>Metode Pemesanan:</strong> {{ strtoupper($pesanan->trackingMethodLabel()) }}</p>
                @if($shippingDestination)
                    <p><strong>{{ $shippingDestinationLabel }}:</strong> {{ $shippingDestination }}</p>
                @endif
                @if($shippingServiceLabel)
                    <p><strong>Layanan Kirim:</strong> {{ $shippingServiceLabel }}</p>
                @endif
                @if($shippingEtd)
                    <p><strong>Estimasi:</strong> {{ $shippingEtd }}</p>
                @endif
                <p><strong>Metode Pembayaran:</strong> {{ strtoupper($pesanan->getPaymentMethodLabel()) }}</p>
                @unless($hidePaymentBadge)
                    <p><strong>Status Pembayaran:</strong> {{ $isLunas ? 'Lunas / Paid' : 'Belum Lunas' }}</p>
                @endunless
                <p><strong>Status Transaksi:</strong> {{ strtoupper($pesanan->publicStatusLabel()) }}</p>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Items & Cost Table -->
    <h2 style="margin-top: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Rincian Item & Biaya</h2>
    
    @if($pesanan->isProductOrder())
        <table>
            <thead>
                <tr>
                    <th style="width: 35%;">Nama Produk</th>
                    <th class="text-center" style="width: 20%;">Spesifikasi</th>
                    <th class="text-center" style="width: 12%;">Jumlah</th>
                    <th class="text-right" style="width: 15%;">Harga Satuan</th>
                    <th class="text-right" style="width: 18%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pesanan->details as $detail)
                    <tr>
                        <td><strong>{{ $detail->produk?->nama ?? 'Produk APAR' }}</strong></td>
                        <td class="text-center">{{ $detail->merek ?: '-' }} ({{ $detail->kapasitas ?: '-' }})</td>
                        <td class="text-center">{{ $detail->jumlah }} unit</td>
                        <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($pesanan->isRefillOrder())
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Layanan</th>
                    <th class="text-center" style="width: 30%;">Spesifikasi APAR</th>
                    <th class="text-center" style="width: 15%;">Jumlah Unit</th>
                    <th class="text-right" style="width: 15%;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Refill / Pengisian Ulang APAR</strong><br>
                        <span style="font-size: 9px; color: #6b7280;">Bahan Refill: {{ $pesanan->serviceJenisRefill?->nama_label ?? $pesanan->service_jenis_apar ?? 'Dry Chemical Powder' }}</span>
                    </td>
                    <td class="text-center">
                        {{ $pesanan->service_jenis_apar ?: '-' }} ({{ $pesanan->service_ukuran_apar ?: '-' }})
                        @if($pesanan->service?->unitApar?->no_seri)
                            <br><span style="font-size: 8px; color: #4b5563;">No. Seri: {{ $pesanan->service->unitApar->no_seri }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ (int) ($pesanan->service_jumlah_unit ?: 1) }} unit</td>
                    <td class="text-right">Rp {{ number_format($pesanan->payableTotal(), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

    @elseif($pesanan->isServiceOrder())
        @php
            $serviceLines = $pesanan->servicePricingBreakdown();
            $servicePeralatan = $pesanan->servicePeralatanItems();
        @endphp
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Paket Service</th>
                    <th style="width: 35%;">Rincian Unit</th>
                    <th style="width: 25%;">Peralatan Paket</th>
                    <th class="text-right" style="width: 15%;">Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $pesanan->servicePaket?->nama ?? 'Paket Service APAR' }}</strong><br>
                        @if($pesanan->servicePaket?->label)
                            <span style="font-size: 9px; color: #6b7280;">{{ $pesanan->servicePaket->label }}</span>
                        @endif
                    </td>
                    <td>
                        @foreach($serviceLines as $line)
                            <div style="margin-bottom: 4px;">
                                <strong>{{ $line['label'] }}</strong><br>
                                <span style="font-size: 9px; color: #4b5563;">{{ (int) ($line['qty'] ?? 1) }} unit - Rp {{ number_format((float) ($line['total'] ?? 0), 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @forelse($servicePeralatan as $item)
                            <div style="font-size: 9px; color: #374151; margin-bottom: 2px;">{{ $item['nama'] ?? '-' }} x{{ (int) ($item['jumlah'] ?? 0) }}</div>
                        @empty
                            <span style="font-size: 9px; color: #6b7280;">Tidak ada peralatan terhubung.</span>
                        @endforelse
                    </td>
                    <td class="text-right">Rp {{ number_format($pesanan->payableTotal(), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Grand Total Section -->
    <table style="margin-top: 10px;">
        <tbody>
            <tr>
                <td class="text-right" style="background-color: #f9fafb;">Subtotal Produk / Layanan</td>
                <td class="text-right" style="width: 180px; background-color: #f9fafb;">Rp {{ number_format((float) ($pricingSummary['subtotalProduk'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-right" style="background-color: #f9fafb;">Diskon</td>
                <td class="text-right" style="background-color: #f9fafb;">{{ (float) ($pricingSummary['nominalDiskon'] ?? 0) > 0 ? '-' : '' }}Rp {{ number_format((float) ($pricingSummary['nominalDiskon'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-right" style="background-color: #f9fafb;">{{ $shippingCostLabel }}</td>
                <td class="text-right" style="background-color: #f9fafb;">Rp {{ number_format((float) ($pricingSummary['ongkir'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            @if($approvedAdjustment > 0)
                <tr>
                    <td class="text-right" style="background-color: #f9fafb;">Penyesuaian Harga Disetujui</td>
                    <td class="text-right" style="background-color: #f9fafb;">-Rp {{ number_format($approvedAdjustment, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-right total" style="background-color: #f9fafb;">Total Pembayaran</td>
                <td class="text-right total" style="width: 180px; background-color: #f9fafb; color: #dc2626;">Rp {{ number_format((float) ($pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Technician Laporan if Completed -->
    @if(in_array($pesanan->tipe, ['refill', 'service'], true) && $pesanan->isCompleted())
        <div class="report-box">
            <h3 style="margin: 0 0 6px; font-size: 11px; color: #dc2626; uppercase: true;">Laporan Pengerjaan Teknisi</h3>
            <p style="margin: 0; font-size: 10px;">
                <strong>Teknisi Petugas:</strong> {{ $pesanan->teknisi?->name ?? 'Teknisi Handal' }}
            </p>
            @if($pesanan->teknisi_catatan)
                <p style="margin-top: 4px; font-style: italic; color: #4b5563;">
                    "{{ $pesanan->teknisi_catatan }}"
                </p>
            @else
                <p style="margin-top: 4px; font-style: italic; color: #6b7280;">
                    Layanan refill/service telah selesai dikerjakan dengan standar keselamatan PD. ANUGRAH UTAMA.
                </p>
            @endif
        </div>
    @endif

    <div style="margin-top: 40px; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 15px;">
        <p style="font-size: 9px; font-weight: bold; color: #9ca3af; uppercase: true; letter-spacing: 0.5px;">Terima kasih atas kepercayaan Anda</p>
        <p style="font-size: 8px; color: #9ca3af; margin-top: 2px;">Invoice ini dicetak secara otomatis dan sah sebagai bukti transaksi.</p>
        <p style="font-size: 8px; color: #cbd5e1; margin-top: 4px;">Nomor referensi internal: {{ $pesanan->invoiceDisplayNumber() }}</p>
    </div>
</body>
</html>
