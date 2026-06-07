<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-red-600">Laporan</p>
                <h2 class="mt-1 text-2xl font-black text-gray-900 tracking-tight">Laporan Operasional</h2>
                <p class="mt-1 text-sm text-gray-500 font-medium">Rekap data transaksi dan keuangan untuk owner.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.laporan.index.pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">

        {{-- A. Filter Section --}}
        <form method="GET" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                        class="text-sm rounded-lg border-gray-200 px-3 py-2 focus:border-red-400 focus:ring-1 focus:ring-red-400 outline-none w-36">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                        class="text-sm rounded-lg border-gray-200 px-3 py-2 focus:border-red-400 focus:ring-1 focus:ring-red-400 outline-none w-36">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Pelanggan</label>
                    <select name="pelanggan_id" class="text-sm rounded-lg border-gray-200 px-3 py-2 focus:border-red-400 focus:ring-1 focus:ring-red-400 outline-none">
                        <option value="">Semua</option>
                        @foreach(\App\Models\Pelanggan::orderBy('nama')->get() as $p)
                            <option value="{{ $p->id }}" {{ request('pelanggan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition shadow-sm">
                    Tampilkan
                </button>
                @if(request('tanggal_dari') || request('tanggal_sampai') || request('pelanggan_id'))
                    <a href="{{ route('admin.laporan.index') }}" class="px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50 rounded-lg transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- B. Ringkasan Keuangan --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Total Pemasukan</p>
                <p class="text-xl font-black text-emerald-700">Rp {{ number_format($summary['totalPemasukan'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-red-600 mb-1">Total Pengeluaran</p>
                <p class="text-xl font-black text-red-700">Rp {{ number_format($summary['totalPengeluaran'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border p-4 shadow-sm {{ $summary['labaBersih'] >= 0 ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Laba / Rugi</p>
                <p class="text-xl font-black {{ $summary['labaBersih'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    {{ $summary['labaBersih'] >= 0 ? '+' : '' }}Rp {{ number_format($summary['labaBersih'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- C. Ringkasan Transaksi Compact --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div class="bg-white rounded-xl border border-gray-100 p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Pesanan</p>
                <p class="text-lg font-black text-gray-900">{{ number_format($summary['totalPesanan']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Service</p>
                <p class="text-lg font-black text-gray-900">{{ number_format($summary['totalService']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Refill</p>
                <p class="text-lg font-black text-gray-900">{{ number_format($summary['totalRefill']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Unit APAR</p>
                <p class="text-lg font-black text-gray-900">{{ number_format($summary['totalUnit']) }}</p>
            </div>
        </div>

        {{-- D. Produk Sering Dilihat & Dibeli --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Produk yang Sering Dilihat --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-sm">Produk yang Sering Dilihat</h3>
                    <p class="text-[10px] text-gray-500">Produk dengan jumlah tampilan tertinggi.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Nama Produk</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Jenis</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Ukuran</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider text-right">Dilihat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($mostViewedProducts as $idx => $product)
                            <tr class="hover:bg-gray-50/30">
                                <td class="px-4 py-2 text-[10px] text-gray-500">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2 text-[10px] font-semibold text-gray-900">{{ $product['product_name'] }}</td>
                                <td class="px-4 py-2 text-[10px] text-gray-600">{{ $product['jenis_apar'] }}</td>
                                <td class="px-4 py-2 text-[10px] text-gray-600">{{ $product['ukuran'] }}</td>
                                <td class="px-4 py-2 text-[10px] text-right font-bold text-violet-600">{{ number_format($product['view_count']) }}x</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-[10px] text-gray-400">Belum ada data produk yang dilihat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Produk yang Sering Dibeli --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-sm">Produk yang Sering Dibeli</h3>
                    <p class="text-[10px] text-gray-500">Produk dengan jumlah penjualan tertinggi.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Nama Produk</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Jenis</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Ukuran</th>
                                <th class="px-4 py-2.5 text-[9px] font-bold text-gray-400 uppercase tracking-wider text-right">Dibeli</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($mostSoldProducts as $idx => $product)
                            <tr class="hover:bg-gray-50/30">
                                <td class="px-4 py-2 text-[10px] text-gray-500">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2 text-[10px] font-semibold text-gray-900">{{ $product['product_name'] }}</td>
                                <td class="px-4 py-2 text-[10px] text-gray-600">{{ $product['jenis_apar'] }}</td>
                                <td class="px-4 py-2 text-[10px] text-gray-600">{{ $product['ukuran'] }}</td>
                                <td class="px-4 py-2 text-[10px] text-right font-bold text-emerald-600">{{ number_format($product['total_sold']) }}x</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-[10px] text-gray-400">Belum ada data produk yang dibeli.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- E. Chart Section --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-sm">Analitik Ringkas</h3>
                <p class="text-[10px] text-gray-500">Komposisi pendapatan, status transaksi, dan kondisi unit APAR.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                <div class="text-center">
                    <h4 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-500">Komposisi Pendapatan</h4>
                    <div id="revenue-composition-chart" class="mx-auto" style="height: 220px; width: 220px; max-height: 220px; max-width: 220px;"></div>
                </div>
                <div class="text-center">
                    <h4 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-500">Status Unit APAR</h4>
                    <div id="unit-status-chart" class="mx-auto" style="height: 220px; width: 220px; max-height: 220px; max-width: 220px;"></div>
                </div>
            </div>
        </div>

        {{-- F. Rekap Transaksi --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-sm">Rekap Transaksi</h3>
                <p class="text-[10px] text-gray-500">Data pesanan dan service terbaru.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Jenis</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Pelanggan</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Keterangan</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider text-right">Pemasukan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($combinedData->sortByDesc('tanggal')->take(15) as $row)
                            <tr class="hover:bg-gray-50/30">
                                <td class="px-4 py-3 text-[10px] font-medium text-gray-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row['tanggal'])->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($row['jenis'] === 'Pesanan')
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[9px] font-bold uppercase rounded">Pesanan</span>
                                    @elseif($row['jenis'] === 'Refill')
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[9px] font-bold uppercase rounded">Refill</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-violet-50 text-violet-700 text-[9px] font-bold uppercase rounded">Service</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[10px] font-semibold text-gray-900">{{ $row['pelanggan'] }}</td>
                                <td class="px-4 py-3 text-[10px] text-gray-600">{{ $row['keterangan'] }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $s = $row['status'];
                                        $statusClass = match(true) {
                                            str_contains($s, 'selesai') || str_contains($s, 'final') => 'bg-emerald-50 text-emerald-700',
                                            str_contains($s, 'ditolak') || str_contains($s, 'batal') => 'bg-red-50 text-red-700',
                                            str_contains($s, 'diproses') || str_contains($s, 'teknisi') || str_contains($s, 'ditugas') => 'bg-amber-50 text-amber-700',
                                            default => 'bg-gray-50 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 {{ $statusClass }} text-[9px] font-bold uppercase rounded">{{ $s }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-[10px] font-bold text-emerald-700 whitespace-nowrap">
                                    @if($row['pemasukan'] > 0)
                                        Rp {{ number_format($row['pemasukan'], 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-[10px] text-gray-400">Belum ada data transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($combinedData->isNotEmpty())
                        <tfoot class="bg-gray-50/80 border-t border-gray-100">
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-right text-[10px] font-bold text-gray-600 uppercase tracking-wider">Total</td>
                                <td class="px-4 py-2 text-right text-[10px] font-bold text-emerald-700 whitespace-nowrap">
                                    Rp {{ number_format($combinedData->sum('pemasukan'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- G. Rincian Pengeluaran --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Rincian Pengeluaran</h3>
                        <p class="text-[10px] text-gray-500">Detail semua pengeluaran dalam periode ini.</p>
                    </div>
                    @php
                        $totalPengeluaran = $pengeluarans->sum('effective_amount');
                    @endphp
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                        Total: Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Jenis</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Keterangan</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider text-right">Jumlah</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pengeluarans as $i => $peng)
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
                            <tr class="hover:bg-gray-50/30">
                                <td class="px-4 py-2.5 text-[10px] text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-2.5 text-[10px] text-gray-700 whitespace-nowrap">
                                    {{ $peng->tanggal ? \Carbon\Carbon::parse($peng->tanggal)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 bg-red-50 text-red-700 text-[9px] font-bold rounded">{{ $jenisLabel }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-[10px] text-gray-600">{{ $keterangan }}</td>
                                <td class="px-4 py-2.5 text-[10px] text-gray-600 text-right">{{ number_format($jumlah) }} {{ $satuan }}</td>
                                <td class="px-4 py-2.5 text-[10px] text-right font-bold text-red-600">Rp {{ number_format($total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-[10px] text-gray-400">Belum ada data pengeluaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- H. Data Pengunjung Website --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Data Pengunjung Website</h3>
                        <p class="text-[10px] text-gray-500">Menampilkan {{ $visitorLimit }} aktivitas terbaru di halaman publik.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                            {{ $visitorRecords->count() }} Records
                        </span>
                        <select
                            onchange="window.location.href=this.value"
                            class="rounded-full border border-gray-200 bg-white px-3 py-1 text-[10px] font-bold text-gray-600 focus:border-blue-400 focus:ring-blue-100"
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
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Jam</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">IP</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Aktivitas</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Produk Dilihat</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Browser</th>
                            <th class="px-4 py-3 text-[9px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
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
                            <tr class="hover:bg-gray-50/30">
                                <td class="px-4 py-2.5 text-[10px] text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-2.5 text-[10px] font-medium text-gray-900 whitespace-nowrap">
                                    {{ optional($visit->visited_at)->translatedFormat('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-[10px] text-gray-600">
                                    {{ optional($visit->visited_at)->format('H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-[10px] text-gray-700">
                                    {{ $visit->ip_address ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $activityBadge = match(true) {
                                            str_contains($activity, 'Melihat Produk') => 'bg-violet-50 text-violet-700',
                                            str_contains($activity, 'Menambahkan') => 'bg-emerald-50 text-emerald-700',
                                            str_contains($activity, 'Membuka Beranda') => 'bg-blue-50 text-blue-700',
                                            str_contains($activity, 'Daftar Produk') => 'bg-indigo-50 text-indigo-700',
                                            str_contains($activity, 'Keranjang') => 'bg-amber-50 text-amber-700',
                                            str_contains($activity, 'Form Pemesanan') => 'bg-rose-50 text-rose-700',
                                            default => 'bg-gray-50 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 {{ $activityBadge }} text-[9px] font-bold rounded">{{ $activity }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-[10px] text-gray-600 max-w-[120px] truncate" title="{{ $detail }}">
                                    {{ $detail }}
                                </td>
                                <td class="px-4 py-2.5 text-[10px] text-gray-600">
                                    {{ $browser }} - {{ $device }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-bold uppercase rounded">Pengunjung</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-[10px] text-gray-400">Belum ada data pengunjung website.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script type="application/json" id="revenue-composition-data">@json($revenueComposition)</script>
    <script type="application/json" id="transaction-status-data">@json($transactionStatus)</script>
    <script type="application/json" id="unit-status-data">@json($unitStatus)</script>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const parseJson = (id, fallback = {}) => {
                    const el = document.getElementById(id);
                    if (!el) return fallback;
                    try { return JSON.parse(el.textContent || ''); } catch { return fallback; }
                };

                const revenueComposition = parseJson('revenue-composition-data');
                const transactionStatus = parseJson('transaction-status-data');
                const unitStatus = parseJson('unit-status-data');

                const palette = {
                    red: '#dc2626',
                    blue: '#2563eb',
                    amber: '#f59e0b',
                    emerald: '#059669',
                    soft: '#e2e8f0'
                };

                const makeChart = (selector, labels, series, colors, config = {}) => {
                    const hasData = (series || []).some(v => Number(v) > 0);
                    const total = (series || []).reduce((a, b) => a + Number(b || 0), 0);
                    
                    const isCurrency = config.isCurrency ?? false;
                    const totalLabel = config.totalLabel ?? 'Total';
                    
                    const formatValue = (val) => {
                        if (isCurrency) {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(val || 0);
                        }
                        return new Intl.NumberFormat('id-ID').format(val || 0);
                    };

                    const options = {
                        chart: {
                            type: 'donut',
                            height: 220,
                            width: 220,
                            toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 800 }
                        },
                        series: hasData ? series : [1],
                        labels: hasData ? labels : ['Tidak Ada Data'],
                        colors: hasData ? colors : [palette.soft],
                        stroke: { width: 3, colors: ['#ffffff'] },
                        dataLabels: { enabled: false },
                        legend: {
                            position: 'bottom',
                            fontSize: '10px',
                            fontFamily: 'system-ui, sans-serif',
                            labels: { colors: '#64748b' },
                            markers: { width: 8, height: 8, radius: 2 },
                            itemMargin: { horizontal: 6, vertical: 0 }
                        },
                        plotOptions: {
                            pie: {
                                expandOnClick: false,
                                customScale: 1.0,
                                donut: {
                                    size: '72%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '11px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 600,
                                            color: '#94a3b8',
                                            offsetY: -8
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '14px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 700,
                                            color: '#0f172a',
                                            offsetY: 6,
                                            formatter: (val) => hasData ? formatValue(val) : formatValue(0)
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: totalLabel,
                                            fontSize: '11px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 600,
                                            color: '#94a3b8',
                                            formatter: (w) => {
                                                if (!hasData) return formatValue(0);
                                                const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                return formatValue(sum);
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                            y: {
                                formatter: (val) => {
                                    if (!hasData) return 'Tidak ada data';
                                    const percent = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return formatValue(val) + ' (' + percent + '%)';
                                }
                            }
                        },
                        states: {
                            hover: { filter: { type: 'none' } },
                            active: { filter: { type: 'none' } }
                        },
                        responsive: [{
                            breakpoint: 640,
                            options: {
                                legend: { position: 'bottom', fontSize: '9px' }
                            }
                        }]
                    };
                    const el = document.querySelector(selector);
                    if (el) new ApexCharts(el, options).render();
                };

                makeChart('#revenue-composition-chart',
                    revenueComposition.labels || [],
                    revenueComposition.series || [],
                    [palette.red, palette.blue, palette.amber],
                    { isCurrency: true, totalLabel: 'Total' }
                );

                makeChart('#unit-status-chart',
                    unitStatus.labels || [],
                    unitStatus.series || [],
                    [palette.emerald, palette.amber, palette.red],
                    { isCurrency: false, totalLabel: 'Unit' }
                );
            });
        </script>
    @endpush
</x-app-layout>
