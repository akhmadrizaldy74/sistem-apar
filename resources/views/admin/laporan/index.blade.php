<x-app-layout>
    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
        $monthlyPurchases = $charts['monthlyPurchases'] ?? [
            'labels' => [],
            'shortLabels' => [],
            'series' => [],
            'year' => now()->year,
            'isFallback' => true,
            'sourceLabel' => '',
            'valueLabel' => 'Total Pembelian',
        ];
        $purchaseSeries = collect($monthlyPurchases['series'] ?? [])->map(fn ($value) => (float) $value)->values();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-bold text-red-600">Laporan</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 md:text-3xl">Laporan Operasional</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-500 md:text-[15px]">
                    Rekap transaksi, kondisi unit APAR, pengeluaran, dan performa operasional dengan tampilan yang selaras dengan dashboard.
                </p>
            </div>
            <a href="{{ route('admin.laporan.index.pdf', request()->query()) }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-700 px-5 py-3 text-sm font-black text-white shadow-xl shadow-red-700/20 transition hover:bg-red-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak PDF
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1360px] space-y-6 text-[13px] md:text-sm">
        @include('admin.laporan.partials.tabs')

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto]">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-600">Pelanggan</label>
                    <select name="pelanggan_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                        <option value="">Semua Pelanggan</option>
                        @foreach($pelanggans as $p)
                            <option value="{{ $p->id }}" {{ request('pelanggan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-3 lg:justify-end">
                    <button type="submit" class="rounded-2xl bg-red-700 px-5 py-3 text-sm font-black text-white transition hover:bg-red-800">
                        Tampilkan
                    </button>
                    @if(request('tanggal_dari') || request('tanggal_sampai') || request('pelanggan_id'))
                        <a href="{{ route('admin.laporan.index') }}" class="rounded-2xl border border-red-200 px-5 py-3 text-center text-sm font-black text-red-700 transition hover:bg-red-50">
                            Reset Filter
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5 shadow-sm">
                <p class="text-sm font-bold text-emerald-700">Total Pemasukan</p>
                <p class="mt-2 text-2xl font-black text-emerald-800">{{ $formatRupiah($summary['totalPemasukan']) }}</p>
                <p class="mt-2 text-sm font-medium text-emerald-700/80">Berdasarkan transaksi yang sudah selesai final.</p>
            </div>
            <div class="rounded-2xl border border-red-200 bg-red-50/80 p-5 shadow-sm">
                <p class="text-sm font-bold text-red-700">Total Pengeluaran</p>
                <p class="mt-2 text-2xl font-black text-red-800">{{ $formatRupiah($summary['totalPengeluaran']) }}</p>
                <p class="mt-2 text-sm font-medium text-red-700/80">Pengeluaran operasional yang tercatat di sistem.</p>
            </div>
            <div class="rounded-2xl border p-5 shadow-sm {{ $summary['labaBersih'] >= 0 ? 'border-blue-200 bg-blue-50/80' : 'border-amber-200 bg-amber-50/80' }}">
                <p class="text-sm font-bold {{ $summary['labaBersih'] >= 0 ? 'text-blue-700' : 'text-amber-700' }}">Laba / Rugi</p>
                <p class="mt-2 text-2xl font-black {{ $summary['labaBersih'] >= 0 ? 'text-blue-800' : 'text-amber-800' }}">
                    {{ $summary['labaBersih'] >= 0 ? '+' : '' }}{{ $formatRupiah($summary['labaBersih']) }}
                </p>
                <p class="mt-2 text-sm font-medium {{ $summary['labaBersih'] >= 0 ? 'text-blue-700/80' : 'text-amber-700/80' }}">Selisih pemasukan final dan pengeluaran operasional.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-sm font-bold text-slate-600">Pesanan Final</p>
                <p class="mt-3 text-2xl font-black text-slate-900">{{ number_format($summary['totalPesanan']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-sm font-bold text-slate-600">Service Final</p>
                <p class="mt-3 text-2xl font-black text-slate-900">{{ number_format($summary['totalService']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-sm font-bold text-slate-600">Refill Final</p>
                <p class="mt-3 text-2xl font-black text-slate-900">{{ number_format($summary['totalRefill']) }}</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 shadow-sm sm:p-5">
                <p class="text-sm font-bold text-blue-700">Unit APAR</p>
                <p class="mt-3 text-2xl font-black text-blue-800">{{ number_format($summary['totalUnit']) }}</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-600">Stok Produk Siap Jual</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($stockSummary['produk'], 0, ',', '.') }} unit</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm">
                <p class="text-sm font-bold text-emerald-700">Stok Refill Tersedia</p>
                <p class="mt-2 text-2xl font-black text-emerald-800">{{ number_format($stockSummary['refill'], 0, ',', '.') }} Kg</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-5 shadow-sm">
                <p class="text-sm font-bold text-blue-700">Stok Peralatan</p>
                <p class="mt-2 text-2xl font-black text-blue-800">{{ number_format($stockSummary['peralatan'], 0, ',', '.') }} unit</p>
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <h3 class="text-base font-black text-slate-900 md:text-lg">Analitik Ringkas</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Diagram laporan dibuat sama dengan dashboard agar pembacaan data tetap konsisten.</p>
            </div>
            <div class="grid gap-5 p-5 lg:grid-cols-2 sm:p-6">
                <article class="flex min-h-[360px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-base font-black text-slate-900">Sumber Pendapatan</h4>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">
                                {{ $charts['revenueComposition']['scopeLabel'] ?? 'Semua transaksi selesai final' }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">Final</span>
                    </div>
                    <div class="mt-4 flex flex-1 flex-col justify-between gap-4">
                        <div class="flex min-h-[250px] items-center justify-center">
                            <div id="report-revenue-composition-chart" class="h-[250px] w-full max-w-[290px]"></div>
                        </div>
                        <div id="report-revenue-composition-legend" class="grid gap-2 text-sm font-semibold text-slate-600"></div>
                    </div>
                </article>

                <article class="flex min-h-[360px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-base font-black text-slate-900">Status Unit APAR</h4>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">
                                Konsep, warna, dan totalnya dibuat sama seperti dashboard admin.
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">Unit</span>
                    </div>
                    <div class="mt-4 flex flex-1 flex-col justify-between gap-4">
                        <div class="flex min-h-[250px] items-center justify-center">
                            <div id="report-unit-status-chart" class="h-[250px] w-full max-w-[290px]"></div>
                        </div>
                        <div id="report-unit-status-legend" class="grid gap-2 text-sm font-semibold text-slate-600"></div>
                    </div>
                </article>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900 md:text-lg">Grafik Pembelian Bulanan</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Format dan perilaku grafik mengikuti dashboard agar pembacaan tetap seragam.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                            Tahun {{ $monthlyPurchases['year'] ?? now()->year }}
                        </span>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ !empty($monthlyPurchases['isFallback']) ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                            {{ !empty($monthlyPurchases['isFallback']) ? 'Data visual sementara' : 'Data pengeluaran tersimpan' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="space-y-5 p-5 sm:p-6">
                <div class="grid gap-3 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-xs font-bold text-slate-500">Total Pembelian Tahun Ini</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $formatRupiah($purchaseSeries->sum()) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-xs font-bold text-slate-500">Data Ditampilkan</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ count($monthlyPurchases['labels'] ?? []) }} bulan</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-xs font-bold text-slate-500">Catatan Data</p>
                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">{{ $monthlyPurchases['sourceLabel'] ?? '-' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4">
                    <div class="h-[310px] sm:h-[350px]">
                        <div id="report-monthly-purchases-chart" class="h-full w-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="overflow-hidden rounded-2xl border border-violet-200 bg-white shadow-sm">
                <div class="border-b border-violet-100 bg-violet-50/40 px-5 py-4 sm:px-6">
                    <h3 class="text-base font-black text-slate-900">Produk yang Sering Dilihat</h3>
                    <p class="mt-1 text-sm font-medium text-slate-500">Produk dengan jumlah tampilan tertinggi.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">#</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">Nama Produk</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">Jenis</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">Ukuran</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-slate-500">Dilihat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($mostViewedProducts as $idx => $product)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $product['product_name'] }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-600">{{ $product['jenis_apar'] }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-600">{{ $product['ukuran'] }}</td>
                                    <td class="px-5 py-3 text-right text-sm font-black text-violet-600">{{ number_format($product['view_count']) }}x</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data produk yang dilihat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <div class="border-b border-emerald-100 bg-emerald-50/40 px-5 py-4 sm:px-6">
                    <h3 class="text-base font-black text-slate-900">Produk yang Sering Dibeli</h3>
                    <p class="mt-1 text-sm font-medium text-slate-500">Produk dengan jumlah penjualan tertinggi.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">#</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">Nama Produk</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">Jenis</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500">Ukuran</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-slate-500">Dibeli</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($mostSoldProducts as $idx => $product)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $product['product_name'] }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-600">{{ $product['jenis_apar'] }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-600">{{ $product['ukuran'] }}</td>
                                    <td class="px-5 py-3 text-right text-sm font-black text-emerald-600">{{ number_format($product['total_sold']) }}x</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data produk yang dibeli.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <h3 class="text-base font-black text-slate-900">Rekap Transaksi</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Data pesanan, service, dan refill terbaru yang sudah masuk ke laporan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Tanggal</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Jenis</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Pelanggan</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Keterangan</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Status</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Sumber</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-slate-500">Pemasukan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($combinedData->sortByDesc('tanggal')->take(15) as $row)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3 text-sm font-medium text-slate-700 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    @if($row['jenis'] === 'Pesanan')
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Pesanan</span>
                                    @elseif($row['jenis'] === 'Refill')
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Refill</span>
                                    @else
                                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">Service</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $row['pelanggan'] }}</td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ $row['keterangan'] }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $s = strtolower((string) $row['status']);
                                        $statusClass = match(true) {
                                            str_contains($s, 'selesai') || str_contains($s, 'final') => 'bg-emerald-50 text-emerald-700',
                                            str_contains($s, 'ditolak') || str_contains($s, 'batal') => 'bg-red-50 text-red-700',
                                            str_contains($s, 'diproses') || str_contains($s, 'teknisi') || str_contains($s, 'ditugas') => 'bg-amber-50 text-amber-700',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ $row['status'] }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-600">{{ $row['source'] ?? '-' }}</td>
                                <td class="px-5 py-3 text-right text-sm font-black text-emerald-700 whitespace-nowrap">
                                    @if($row['pemasukan'] > 0)
                                        {{ $formatRupiah($row['pemasukan']) }}
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Rincian Pengeluaran</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Detail semua pengeluaran yang masuk pada periode laporan.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-red-100 px-4 py-1.5 text-sm font-bold text-red-700">
                        Total: {{ $formatRupiah($pengeluarans->sum('effective_amount')) }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">#</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Tanggal</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Jenis</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Keterangan</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-slate-500">Jumlah</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-slate-500">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pengeluarans as $i => $peng)
                            @php
                                $keterangan = $peng->nama_item ?? $peng->keterangan ?? '-';
                                $jumlah = $peng->qty ?? 1;
                                $satuan = $peng->satuan ?? 'unit';
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3 text-sm text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700 whitespace-nowrap">{{ $peng->tanggal ? \Carbon\Carbon::parse($peng->tanggal)->format('d M Y') : '-' }}</td>
                                <td class="px-5 py-3"><span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">{{ $peng->jenis_pengeluaran_label }}</span></td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ $keterangan }}</td>
                                <td class="px-5 py-3 text-right text-sm text-slate-600">{{ number_format($jumlah) }} {{ $satuan }}</td>
                                <td class="px-5 py-3 text-right text-sm font-black text-red-700">{{ $formatRupiah($peng->effective_amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data pengeluaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Data Pengunjung Website</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Menampilkan {{ $visitorLimit }} aktivitas terbaru di halaman publik.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-blue-100 px-4 py-1.5 text-sm font-bold text-blue-700">{{ $visitorRecords->count() }} record</span>
                        <select
                            onchange="window.location.href=this.value"
                            class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-bold text-slate-600 focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            aria-label="Jumlah data pengunjung yang ditampilkan"
                        >
                            @foreach($visitorLimitOptions as $limitOption)
                                <option
                                    value="{{ request()->fullUrlWithQuery(['visitor_limit' => $limitOption]) }}"
                                    @selected($visitorLimit === $limitOption)
                                >
                                    {{ $limitOption }} data
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">#</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Tanggal</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Jam</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">IP</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Aktivitas</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Produk Dilihat</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Browser</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($visitorRecords as $i => $visit)
                            @php
                                $userAgent = $visit->user_agent ?? '';
                                $browser = 'Unknown';
                                $device = 'Desktop';
                                if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent)) { $device = 'Mobile'; }
                                if (preg_match('/Chrome/i', $userAgent)) { $browser = $device === 'Mobile' ? 'Mobile Chrome' : 'Chrome'; }
                                elseif (preg_match('/Firefox/i', $userAgent)) { $browser = $device === 'Mobile' ? 'Mobile Firefox' : 'Firefox'; }
                                elseif (preg_match('/Safari/i', $userAgent)) { $browser = $device === 'Mobile' ? 'Mobile Safari' : 'Safari'; }
                                elseif (preg_match('/Edge/i', $userAgent)) { $browser = 'Edge'; }
                                elseif (preg_match('/Opera|OPR/i', $userAgent)) { $browser = 'Opera'; }
                                $label = \App\Models\WebsiteVisit::getLabeledPageUrl($visit->page_url, $visit->page_title, $visit->product_id);
                                $activity = $label['activity'] ?? 'Membuka Halaman';
                                $detail = $label['detail'] ?? $visit->page_title ?? '-';
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3 text-sm text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-5 py-3 text-sm font-medium text-slate-900 whitespace-nowrap">{{ optional($visit->visited_at)->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ optional($visit->visited_at)->format('H:i') ?? '-' }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ $visit->ip_address ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $activityBadge = match(true) {
                                            str_contains($activity, 'Melihat Produk') => 'bg-violet-50 text-violet-700',
                                            str_contains($activity, 'Menambahkan') => 'bg-emerald-50 text-emerald-700',
                                            str_contains($activity, 'Membuka Beranda') => 'bg-blue-50 text-blue-700',
                                            str_contains($activity, 'Daftar Produk') => 'bg-indigo-50 text-indigo-700',
                                            str_contains($activity, 'Keranjang') => 'bg-amber-50 text-amber-700',
                                            str_contains($activity, 'Form Pemesanan') => 'bg-rose-50 text-rose-700',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $activityBadge }}">{{ $activity }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-slate-600 max-w-[160px] truncate" title="{{ $detail }}">{{ $detail }}</td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ $browser }} - {{ $device }}</td>
                                <td class="px-5 py-3"><span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">Pengunjung</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data pengunjung website.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script type="application/json" id="report-revenue-composition-data">@json($charts['revenueComposition'])</script>
    <script type="application/json" id="report-unit-status-data">@json($charts['unitStatus'])</script>
    <script type="application/json" id="report-monthly-purchases-data">@json($charts['monthlyPurchases'])</script>

    @push('scripts')
        @include('admin.partials.analytics-charts-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const chartKit = window.AdminAnalyticsCharts;

                if (!chartKit) {
                    return;
                }

                const revenueComposition = chartKit.parseJson('report-revenue-composition-data');
                const unitStatus = chartKit.parseJson('report-unit-status-data');
                const monthlyPurchases = chartKit.parseJson('report-monthly-purchases-data');

                chartKit.createChart('#report-revenue-composition-chart', chartKit.makeCurrencyDonutChart({
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: revenueComposition.colors,
                    totalLabel: revenueComposition.totalLabel || 'Total',
                }));

                chartKit.renderLegend(document.querySelector('#report-revenue-composition-legend'), {
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: revenueComposition.colors,
                    valueFormatter: (value) => chartKit.rupiah(value),
                    emptyLabel: 'Belum ada data pendapatan final.',
                });

                chartKit.createChart('#report-unit-status-chart', chartKit.makeCountDonutChart({
                    labels: unitStatus.labels,
                    series: unitStatus.series,
                    colors: unitStatus.colors,
                    totalLabel: unitStatus.totalLabel || 'Total Unit',
                    unitLabel: 'unit',
                }));

                chartKit.renderLegend(document.querySelector('#report-unit-status-legend'), {
                    labels: unitStatus.labels,
                    series: unitStatus.series,
                    colors: unitStatus.colors,
                    valueFormatter: (value) => `${chartKit.numberId(value)} unit`,
                    emptyLabel: 'Belum ada data status unit.',
                });

                chartKit.createChart('#report-monthly-purchases-chart', chartKit.makeMonthlyPurchasesChart({
                    labels: monthlyPurchases.labels,
                    shortLabels: monthlyPurchases.shortLabels,
                    series: monthlyPurchases.series,
                    year: monthlyPurchases.year,
                    valueLabel: monthlyPurchases.valueLabel,
                    lineColor: monthlyPurchases.lineColor,
                    lineFill: monthlyPurchases.lineFill,
                }));
            });
        </script>
    @endpush
</x-app-layout>
