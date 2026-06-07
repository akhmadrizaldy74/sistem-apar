<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Operasional - PD. ANUGRAH UTAMA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; }
        .page { padding: 20px 25px; }
        .header { border-bottom: 3px solid #dc2626; padding-bottom: 12px; margin-bottom: 16px; }
        .header-top { display: block; margin-bottom: 8px; }
        .company-info h1 { font-size: 15px; color: #0f172a; margin-bottom: 1px; }
        .report-title { font-size: 16px; font-weight: bold; color: #dc2626; margin: 8px 0 4px; }
        .report-meta { font-size: 9px; color: #64748b; }
        .report-meta strong { color: #334155; }
        .section { margin-bottom: 14px; }
        .section-title { font-size: 11px; font-weight: bold; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px; }
        .stats-grid { display: table; width: 100%; border-collapse: collapse; }
        .stats-row { display: table-row; }
        .stats-cell { display: table-cell; padding: 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        .stats-cell:nth-child(odd) { background: #fef2f2; }
        .stats-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: bold; }
        .stats-value { font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        .stats-value.red { color: #dc2626; }
        .stats-value.green { color: #059669; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; }
        th { background: #dc2626; color: white; padding: 6px 5px; text-align: left; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; }
        td { padding: 5px 5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) { background: #f8fafc; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 2px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .money { text-align: right; font-family: 'Courier New', monospace; }
        .money.positive { color: #059669; }
        .visitor-summary { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-bottom: 14px; display: flex; gap: 16px; }
        .visitor-summary-item { text-align: center; }
        .visitor-label { font-size: 7px; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; }
        .visitor-value { font-size: 14px; font-weight: bold; color: #0f172a; }
        .footer { margin-top: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #64748b; display: flex; justify-content: space-between; }
        @page { margin: 12mm; size: A4; }
        .summary-row td { background: #fef2f2 !important; font-weight: bold; }
        .total-row td { background: #0f172a !important; color: white; font-weight: bold; }
        .product-table-header { background: #f0fdf4 !important; color: #166534; padding: 6px; text-align: left; font-size: 9px; font-weight: bold; }
        .product-table-header.viewed { background: #fef2f2 !important; color: #991b1b; }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="header-top">
                <div class="company-info">
                    <h1>PD. ANUGRAH UTAMA</h1>
                </div>
            </div>
            <div class="report-title">LAPORAN OPERASIONAL</div>
            <div class="report-meta">
                <strong>Periode:</strong> {{ $periode }}
                @if($pelangganNama)<span> | <strong>Pelanggan:</strong> {{ $pelangganNama }}</span>@endif
            </div>
            <div class="report-meta" style="margin-top:3px;">
                <strong>Dicetak:</strong> {{ $printedAt->translatedFormat('d M Y, H:i') }} WIB
            </div>
        </div>

        {{-- Ringkasan Keuangan --}}
        <div class="section">
            <div class="section-title">Ringkasan Keuangan</div>
            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stats-cell" style="width:33%;">
                        <div class="stats-label">Total Pemasukan</div>
                        <div class="stats-value green">Rp {{ number_format($summary['totalPemasukan'], 0, ',', '.') }}</div>
                    </div>
                    <div class="stats-cell" style="width:34%;">
                        <div class="stats-label">Total Pengeluaran</div>
                        <div class="stats-value red">Rp {{ number_format($summary['totalPengeluaran'], 0, ',', '.') }}</div>
                    </div>
                    <div class="stats-cell" style="width:33%;">
                        <div class="stats-label">Laba / Rugi</div>
                        <div class="stats-value {{ $summary['labaBersih'] >= 0 ? 'green' : 'red' }}">
                            {{ $summary['labaBersih'] >= 0 ? '+' : '' }}Rp {{ number_format($summary['labaBersih'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Transaksi --}}
        <div class="section">
            <div class="section-title">Ringkasan Transaksi</div>
            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stats-cell" style="width:25%;">
                        <div class="stats-label">Total Pesanan</div>
                        <div class="stats-value">{{ number_format($summary['totalPesanan']) }}</div>
                    </div>
                    <div class="stats-cell" style="width:25%;">
                        <div class="stats-label">Total Service</div>
                        <div class="stats-value">{{ number_format($summary['totalService']) }}</div>
                    </div>
                    <div class="stats-cell" style="width:25%;">
                        <div class="stats-label">Total Refill</div>
                        <div class="stats-value">{{ number_format($summary['totalRefill']) }}</div>
                    </div>
                    <div class="stats-cell" style="width:25%;">
                        <div class="stats-label">Total Unit APAR</div>
                        <div class="stats-value">{{ number_format($summary['totalUnit']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Visitor Summary Ringkasan --}}
        <div class="visitor-summary">
            <div class="visitor-summary-item">
                <div class="visitor-label">Pengunjung Hari Ini</div>
                <div class="visitor-value">{{ number_format($visitorStats['hariIni'] ?? 0) }}</div>
            </div>
            <div class="visitor-summary-item">
                <div class="visitor-label">Total Pengunjung</div>
                <div class="visitor-value">{{ number_format($visitorStats['totalUnik'] ?? 0) }}</div>
            </div>
        </div>

        {{-- Analitik Produk --}}
        @if(count($mostViewedProducts) > 0 || (isset($mostSoldProducts) && count($mostSoldProducts) > 0))
        <div class="section">
            <div class="section-title">Analitik Produk</div>
            <table style="margin-bottom: 10px;">
                <tr>
                    <td style="width:50%; padding-right: 8px; vertical-align: top;">
                        @if(count($mostViewedProducts) > 0)
                        <table style="border: 1px solid #e2e8f0;">
                            <tr>
                                <th colspan="5" class="product-table-header viewed">Produk yang Sering Dilihat</th>
                            </tr>
                            <tr>
                                <th style="width:8%; background:#f8fafc; padding:4px; font-size:7px; text-align:center;">#</th>
                                <th style="background:#f8fafc; padding:4px; font-size:7px;">Nama Produk</th>
                                <th style="background:#f8fafc; padding:4px; font-size:7px;">Jenis</th>
                                <th style="background:#f8fafc; padding:4px; font-size:7px;">Ukuran</th>
                                <th style="width:15%; background:#f8fafc; padding:4px; font-size:7px; text-align:right;">Dilihat</th>
                            </tr>
                            @foreach($mostViewedProducts as $idx => $product)
                            <tr>
                                <td style="text-align:center; padding:3px; font-size:7px;">{{ $idx + 1 }}</td>
                                <td style="padding:3px; font-size:7px;">{{ $product['product_name'] }}</td>
                                <td style="padding:3px; font-size:7px;">{{ $product['jenis_apar'] }}</td>
                                <td style="padding:3px; font-size:7px;">{{ $product['ukuran'] }}</td>
                                <td style="padding:3px; font-size:7px; text-align:right; font-weight:bold; color:#2563eb;">{{ number_format($product['view_count']) }}x</td>
                            </tr>
                            @endforeach
                        </table>
                        @endif
                    </td>
                    <td style="width:50%; padding-left: 8px; vertical-align: top;">
                        @if(isset($mostSoldProducts) && count($mostSoldProducts) > 0)
                        <table style="border: 1px solid #e2e8f0;">
                            <tr>
                                <th colspan="5" class="product-table-header">Produk yang Sering Dibeli</th>
                            </tr>
                            <tr>
                                <th style="width:8%; background:#f8fafc; padding:4px; font-size:7px; text-align:center;">#</th>
                                <th style="background:#f8fafc; padding:4px; font-size:7px;">Nama Produk</th>
                                <th style="background:#f8fafc; padding:4px; font-size:7px;">Jenis</th>
                                <th style="background:#f8fafc; padding:4px; font-size:7px;">Ukuran</th>
                                <th style="width:15%; background:#f8fafc; padding:4px; font-size:7px; text-align:right;">Dibeli</th>
                            </tr>
                            @foreach($mostSoldProducts as $idx => $product)
                            <tr>
                                <td style="text-align:center; padding:3px; font-size:7px;">{{ $idx + 1 }}</td>
                                <td style="padding:3px; font-size:7px;">{{ $product['product_name'] }}</td>
                                <td style="padding:3px; font-size:7px;">{{ $product['jenis_apar'] }}</td>
                                <td style="padding:3px; font-size:7px;">{{ $product['ukuran'] }}</td>
                                <td style="padding:3px; font-size:7px; text-align:right; font-weight:bold; color:#059669;">{{ number_format($product['total_sold']) }}x</td>
                            </tr>
                            @endforeach
                        </table>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        @endif

        {{-- Rekap Transaksi --}}
        <div class="section">
            <div class="section-title">Rekap Transaksi</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:5%;">#</th>
                        <th style="width:10%;">Tanggal</th>
                        <th style="width:8%;">Jenis</th>
                        <th style="width:18%;">Pelanggan</th>
                        <th style="width:15%;">Keterangan</th>
                        <th style="width:12%;">Status</th>
                        <th style="text-align:right;width:15%;">Pemasukan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($combinedData as $i => $row)
                        @php
                            $statusBadge = match(true) {
                                str_contains($row['status'], 'selesai') || str_contains($row['status'], 'final') => ['badge-success', 'Selesai'],
                                str_contains($row['status'], 'ditolak') || str_contains($row['status'], 'batal') => ['badge-danger', 'Ditolak'],
                                str_contains($row['status'], 'diproses') || str_contains($row['status'], 'teknisi') => ['badge-info', 'Diproses'],
                                default => ['badge-gray', $row['status']],
                            };
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $i + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td>
                                @if($row['jenis'] === 'Pesanan')
                                    <span class="badge badge-info">Pesanan</span>
                                @elseif($row['jenis'] === 'Refill')
                                    <span class="badge" style="background:#fef3c7;color:#92400e;">Refill</span>
                                @else
                                    <span class="badge" style="background:#f3e8ff;color:#6b21a8;">Service</span>
                                @endif
                            </td>
                            <td>{{ $row['pelanggan'] }}</td>
                            <td>{{ $row['keterangan'] }}</td>
                            <td><span class="badge {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span></td>
                            <td class="money positive">Rp {{ number_format($row['pemasukan'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:#64748b;padding:15px;">Tidak ada data transaksi.</td>
                        </tr>
                    @endforelse
                    @if(count($combinedData) > 0)
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;"><strong>TOTAL</strong></td>
                            <td class="money"><strong>Rp {{ number_format(collect($combinedData)->sum('pemasukan'), 0, ',', '.') }}</strong></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Rincian Pengeluaran --}}
        @if(isset($pengeluarans) && $pengeluarans->count() > 0)
        <div class="section">
            <div class="section-title">Rincian Pengeluaran</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:5%;">#</th>
                        <th style="width:10%;">Tanggal</th>
                        <th style="width:15%;">Jenis</th>
                        <th style="width:30%;">Keterangan</th>
                        <th style="width:12%;">Jumlah</th>
                        <th style="text-align:right;width:15%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengeluarans as $i => $peng)
                        @php
                            $jenisLabel = match($peng->jenis_pengeluaran ?? $peng->kategori ?? 'lain') {
                                'pembelian_apar' => 'Pembelian APAR',
                                'pembelian_refill' => 'Pembelian Refill',
                                'pembelian_peralatan' => 'Peralatan',
                                'pengeluaran_lainnya' => 'Lainnya',
                                default => $peng->jenis_pengeluaran ?? $peng->kategori ?? 'Lainnya',
                            };
                            $keterangan = $peng->nama_item ?? $peng->keterangan ?? '-';
                            $jumlah = $peng->qty ?? 1;
                            $satuan = $peng->satuan ?? 'unit';
                            $total = $peng->effective_amount;
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $i + 1 }}</td>
                            <td>{{ $peng->tanggal ? \Carbon\Carbon::parse($peng->tanggal)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $jenisLabel }}</td>
                            <td>{{ $keterangan }}</td>
                            <td>{{ number_format($jumlah) }} {{ $satuan }}</td>
                            <td class="money" style="color:#dc2626;">Rp {{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="summary-row">
                        <td colspan="5" style="text-align:right;"><strong>TOTAL PENGELUARAN</strong></td>
                        <td class="money" style="color:#dc2626;"><strong>Rp {{ number_format($pengeluarans->sum('effective_amount'), 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- Data Pengunjung Website --}}
        @if($visitorRecords->count() > 0)
        <div class="section">
            <div class="section-title">Data Pengunjung Website</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:4%;">#</th>
                        <th style="width:8%;">Tanggal</th>
                        <th style="width:6%;">Jam</th>
                        <th style="width:10%;">IP</th>
                        <th style="width:15%;">Aktivitas</th>
                        <th style="width:25%;">Produk Dilihat</th>
                        <th style="width:12%;">Browser</th>
                        <th style="width:10%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visitorRecords as $i => $visit)
                        @php
                            $ua = $visit->user_agent ?? '';
                            $browser = 'Unknown';
                            $device = 'Desktop';
                            if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) { $device = 'Mobile'; }
                            if (preg_match('/Chrome/i', $ua)) { $browser = $device === 'Mobile' ? 'Mobile Chrome' : 'Chrome'; }
                            elseif (preg_match('/Firefox/i', $ua)) { $browser = $device === 'Mobile' ? 'Mobile Firefox' : 'Firefox'; }
                            elseif (preg_match('/Safari/i', $ua)) { $browser = $device === 'Mobile' ? 'Mobile Safari' : 'Safari'; }
                            elseif (preg_match('/Edge/i', $ua)) { $browser = 'Edge'; }
                            elseif (preg_match('/Opera|OPR/i', $ua)) { $browser = 'Opera'; }
                            $label = \App\Models\WebsiteVisit::getLabeledPageUrl($visit->page_url, $visit->page_title, $visit->product_id);
                            $activity = $label['activity'] ?? 'Membuka Halaman';
                            $detail = $label['detail'] ?? $visit->page_title ?? '-';
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $i + 1 }}</td>
                            <td>{{ $visit->visited_at ? $visit->visited_at->translatedFormat('d M Y') : '-' }}</td>
                            <td>{{ $visit->visited_at ? $visit->visited_at->format('H:i') : '-' }}</td>
                            <td>{{ $visit->ip_address ?? '-' }}</td>
                            <td>{{ $activity }}</td>
                            <td>{{ $detail }}</td>
                            <td>{{ $browser }} - {{ $device }}</td>
                            <td><span class="badge badge-info">Pengunjung</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <span>Dicetak oleh: <strong>{{ auth()->user()->name ?? 'Admin' }}</strong></span>
            <span>Halaman 1 dari 1</span>
            <span>{{ now()->translatedFormat('d M Y, H:i') }} WIB</span>
        </div>
    </div>
</body>
</html>
