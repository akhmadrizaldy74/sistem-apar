<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900">Manajemen Stok</h2>
                <p class="text-sm font-medium text-gray-500">Menampilkan stok APAR, refill, dan peralatan yang tersedia.</p>
            </div>
        </div>
    </x-slot>

    @php
        $totalUnitApar = (int) ($aparStockSummary['totalPhysicalStock'] ?? 0);
        $aparCounts = $aparStockSummary['counts'] ?? [];
        $expiryModalItems = collect($aparStockSummary['rows'] ?? [])
            ->pluck('modal')
            ->filter(fn ($item) => (int) ($item['primary_batch_id'] ?? 0) > 0)
            ->values();
        $formatQty = static function ($value) {
            $number = (float) $value;
            if ((int) $number === $number) {
                return number_format($number, 0, ',', '.');
            }

            return rtrim(rtrim(number_format($number, 2, ',', '.'), '0'), ',');
        };
    @endphp

    <div class="py-6" x-data="{
        tab: @js($activeTab),
        expiryModalItems: @js($expiryModalItems),
        openExpiryModal: false,
        selectedExpiryItem: null,
        refillDateInput: @js(old('tanggal_refill', now()->format('Y-m-d'))),
        expiryPreviewLabel: '-',
        init() {
            const previousBatchId = @js((int) old('batch_id_refill', 0));
            if (previousBatchId > 0) {
                const selected = this.expiryModalItems.find(item => Number(item.primary_batch_id) === previousBatchId);
                if (selected) {
                    this.openExpiryUpdate(selected, this.refillDateInput || selected.default_refill_date);
                }
            }
        },
        openExpiryUpdate(item, overrideDate = null) {
            this.selectedExpiryItem = item;
            this.refillDateInput = overrideDate || item.default_refill_date || '';
            this.refreshExpiryPreview();
            this.openExpiryModal = true;
            document.body.classList.add('overflow-hidden');
        },
        closeExpiryUpdate() {
            this.openExpiryModal = false;
            this.selectedExpiryItem = null;
            document.body.classList.remove('overflow-hidden');
        },
        refreshExpiryPreview() {
            if (!this.selectedExpiryItem || !this.refillDateInput) {
                this.expiryPreviewLabel = '-';
                return;
            }

            const baseDate = new Date(`${this.refillDateInput}T00:00:00`);
            if (Number.isNaN(baseDate.getTime())) {
                this.expiryPreviewLabel = '-';
                return;
            }

            const rawSize = String(this.selectedExpiryItem.kapasitas || '').toLowerCase().replace(/\s+/g, '');
            const isOneKg = /^1(?:[.,]0+)?kg?$/.test(rawSize) || rawSize === '1';
            const expiryDate = new Date(baseDate);
            if (isOneKg) {
                expiryDate.setMonth(expiryDate.getMonth() + 6);
            } else {
                expiryDate.setFullYear(expiryDate.getFullYear() + 1);
            }

            this.expiryPreviewLabel = new Intl.DateTimeFormat('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }).format(expiryDate);
        }
    }">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Stok APAR</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format((float) $totalUnitApar, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $produks->count() }} produk APAR dimonitor</p>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Refill</p>
                        <p class="mt-2 text-3xl font-black text-gray-900">{{ $formatQty($jenisRefills->sum(fn ($item) => (float) ($item->stok ?? 0))) }}</p>
                        <p class="mt-1 text-xs font-semibold text-gray-500">{{ $jenisRefills->count() }} jenis refill dimonitor</p>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Peralatan</p>
                        <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format((float) $peralatans->sum(fn ($item) => (int) ($item->stok ?? 0)), 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs font-semibold text-gray-500">{{ $peralatans->count() }} peralatan dimonitor</p>
                </div>
            </div>

            <div class="flex w-fit flex-wrap gap-2 rounded-2xl bg-gray-100 p-1.5">
                <button
                    type="button"
                    @click="tab = 'apar'"
                    :class="tab === 'apar' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="rounded-xl px-5 py-2.5 text-xs font-black uppercase tracking-widest transition"
                >
                    Stok APAR
                </button>
                <button
                    type="button"
                    @click="tab = 'refill'"
                    :class="tab === 'refill' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="rounded-xl px-5 py-2.5 text-xs font-black uppercase tracking-widest transition"
                >
                    Stok Refill
                </button>
                <button
                    type="button"
                    @click="tab = 'peralatan'"
                    :class="tab === 'peralatan' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="rounded-xl px-5 py-2.5 text-xs font-black uppercase tracking-widest transition"
                >
                    Stok Peralatan
                </button>
            </div>

            <section x-show="tab === 'apar'" class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-8 py-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Stok APAR</h3>
                        <p class="text-xs font-semibold text-gray-500">Pantau semua stok APAR, fokuskan ke produk yang hampir expired atau sudah expired, lalu perbarui masa berlakunya tanpa menambah stok.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.pengeluaran.index', ['open' => 1, 'jenis_pengeluaran' => \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR]) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            Catat Pembelian APAR
                        </a>
                        <a href="{{ route('admin.produk.index') }}" class="text-xs font-black uppercase tracking-widest text-red-600 hover:text-red-700">Kelola Produk</a>
                    </div>
                </div>
                <div class="border-b border-gray-100 px-8 py-5">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.stok.index', ['tab' => 'apar', 'filter' => 'semua']) }}" class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition {{ $activeAparFilter === 'semua' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Semua Stok APAR
                            <span class="ml-1">{{ number_format((int) ($aparCounts['semua'] ?? 0), 0, ',', '.') }}</span>
                        </a>
                        <a href="{{ route('admin.stok.index', ['tab' => 'apar', 'filter' => 'masa-berlaku']) }}" class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition {{ $activeAparFilter === 'masa-berlaku' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                            Perlu Perhatian
                            <span class="ml-1">{{ number_format((int) ($aparCounts['masa-berlaku'] ?? 0), 0, ',', '.') }}</span>
                        </a>
                        <a href="{{ route('admin.stok.index', ['tab' => 'apar', 'filter' => 'hampir-expired']) }}" class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition {{ $activeAparFilter === 'hampir-expired' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                            Hampir Expired
                            <span class="ml-1">{{ number_format((int) ($aparCounts['hampir-expired'] ?? 0), 0, ',', '.') }}</span>
                        </a>
                        <a href="{{ route('admin.stok.index', ['tab' => 'apar', 'filter' => 'expired']) }}" class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition {{ $activeAparFilter === 'expired' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                            Expired
                            <span class="ml-1">{{ number_format((int) ($aparCounts['expired'] ?? 0), 0, ',', '.') }}</span>
                        </a>
                    </div>
                    <p class="mt-3 text-xs font-semibold text-gray-500">{{ $aparStockSummary['helperText'] ?? '' }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Produk</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Merek</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis APAR</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Ukuran</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Stok</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Masa Berlaku</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse(($aparStockSummary['rows'] ?? []) as $row)
                                <tr class="transition hover:bg-gray-50/40">
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $row['product_name'] }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-700">{{ $row['brand'] }}</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ $row['jenis_apar'] }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $row['kapasitas'] }}</td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $row['stock_total_label'] }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black {{ $row['status_text_class'] }}">{{ $row['masa_berlaku_label'] }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $row['status_badge_class'] }}">
                                            {{ $row['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($row['has_issue'] && $row['primary_batch_id'] > 0)
                                                <button type="button" @click='openExpiryUpdate(@json($row['modal']))' class="rounded-xl bg-amber-600 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-amber-700">
                                                    Perbarui Masa Berlaku
                                                </button>
                                            @endif
                                            @if($row['can_add_stock'])
                                                <a href="{{ route('admin.pengeluaran.index', ['open' => 1, 'jenis_pengeluaran' => \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR, 'produk_id' => $row['product_id']]) }}" class="rounded-xl bg-red-700 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-red-800">
                                                    Tambah Stok
                                                </a>
                                            @else
                                                <button type="button" onclick='window.alert(@json($row["blocked_add_stock_message"]))' class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-amber-800 transition hover:bg-amber-100">
                                                    Tambah Stok
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Tidak ada produk APAR yang cocok dengan filter saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section x-show="tab === 'refill'" x-cloak class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-8 py-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Stok Refill</h3>
                        <p class="text-xs font-semibold text-gray-500">Pantau stok berdasarkan Jenis Refill. Penambahan stok dilakukan lewat transaksi pembelian di menu Pengeluaran.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.pengeluaran.index', ['open' => 1, 'jenis_pengeluaran' => \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL]) }}" class="text-xs font-black uppercase tracking-widest text-red-600 hover:text-red-700">Catat Pembelian</a>
                        <a href="{{ route('admin.refill.index') }}" class="text-xs font-black uppercase tracking-widest text-gray-500 hover:text-gray-700">Riwayat Refill</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis Refill</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Saat Ini</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Standar</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($jenisRefills as $jenisRefill)
                                @php
                                    $stok = (float) ($jenisRefill->stok ?? 0);
                                    $satuan = $jenisRefill->satuan_label;
                                    $tersedia = $stok > 0;
                                @endphp
                                <tr class="transition hover:bg-gray-50/40">
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $jenisRefill->nama }}</p>
                                        <p class="mt-1 text-xs font-semibold text-gray-500">Monitoring stok {{ $formatQty($stok) }} {{ $satuan }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $formatQty($stok) }} {{ $satuan }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">Rp {{ number_format((float) ($jenisRefill->harga ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $tersedia ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $tersedia ? 'Tersedia' : 'Kosong' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data jenis refill.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section x-show="tab === 'peralatan'" x-cloak class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-8 py-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Stok Peralatan</h3>
                        <p class="text-xs font-semibold text-gray-500">Pantau stok peralatan hasil pembelian. Data layanan dan stok dikelola di menu yang berbeda.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.peralatan.index') }}" class="rounded-xl bg-gray-100 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-gray-700 transition hover:bg-gray-200">
                            Kelola Peralatan
                        </a>
                        <a href="{{ route('admin.pengeluaran.index', ['open' => 1, 'jenis_pengeluaran' => \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN]) }}" class="rounded-xl bg-red-700 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800">
                            Catat Pembelian
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Nama Peralatan</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Stok</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Standar</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($peralatans as $peralatan)
                                @php
                                    $stok = (int) ($peralatan->stok ?? 0);
                                    $tersedia = $stok > 0;
                                @endphp
                                <tr class="transition hover:bg-gray-50/40">
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $peralatan->nama }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $stok }} unit</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">Rp {{ number_format((float) ($peralatan->harga_standar ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $tersedia ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $tersedia ? 'Tersedia' : 'Kosong' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-semibold text-gray-500">
                                        Stok peralatan dikelola lewat pembelian di menu Pengeluaran.
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data peralatan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-8 py-5">
                    <h3 class="text-lg font-black text-gray-900">Riwayat Transaksi Stok</h3>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Riwayat ini menampilkan stok masuk dari pembelian admin dan stok keluar dari pesanan pelanggan, refill, serta service yang benar-benar memengaruhi stok.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Tanggal</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Item</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Sumber</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Perubahan</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($stockHistories as $movement)
                                @php
                                    $isIn = $movement->movement_type === \App\Models\StockMovement::MOVE_IN;
                                @endphp
                                <tr class="transition hover:bg-gray-50/40">
                                    <td class="px-8 py-5 text-sm font-bold text-gray-900">{{ optional($movement->tanggal)->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $movement->item_type_label }}</p>
                                        @if(!empty($movement->flow_label))
                                            <p class="mt-1 text-[11px] font-semibold {{ $isIn ? 'text-emerald-600' : 'text-red-600' }}">{{ $movement->flow_label }}</p>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $movement->item_nama }}</p>
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">{{ $movement->satuan }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-bold text-gray-700">{{ $movement->source_label }}</p>
                                        @if(!empty($movement->source_detail))
                                            <p class="mt-1 text-[11px] font-semibold text-gray-500">{{ $movement->source_detail }}</p>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $isIn ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $isIn ? '+' : '-' }}{{ $formatQty($movement->qty) }} {{ $movement->satuan }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-semibold leading-6 text-gray-600">{{ $movement->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada riwayat transaksi stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div x-show="openExpiryModal" x-cloak x-on:keydown.escape.window="closeExpiryUpdate()" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="closeExpiryUpdate()"></div>
            <div
                x-show="openExpiryModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                class="relative w-full max-w-2xl overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl shadow-gray-900/20"
            >
                <div class="flex items-center justify-between border-b border-gray-100 bg-white px-6 py-5 sm:px-8">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Perbarui Masa Berlaku Stok APAR</h3>
                        <p class="mt-1 text-xs font-semibold text-gray-500">Perbarui masa berlaku stok lama tanpa menambah atau mengurangi jumlah unit.</p>
                    </div>
                    <button type="button" @click="closeExpiryUpdate()" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 transition hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="selectedExpiryItem ? '{{ url('/admin/stok/batch') }}/' + selectedExpiryItem.primary_batch_id + '/refill' : '#'" method="POST" class="space-y-5 p-6 sm:p-8">
                    @csrf
                    <input type="hidden" name="batch_id_refill" :value="selectedExpiryItem?.primary_batch_id || ''">

                    <div class="rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-sm font-semibold text-amber-900">
                        Tindakan ini memperbarui masa berlaku stok APAR. Jumlah stok tetap sama.
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Produk APAR</p>
                            <p class="mt-1 text-sm font-black text-gray-900" x-text="selectedExpiryItem?.product_name || '-'"></p>
                            <p class="mt-1 text-xs font-semibold text-gray-500" x-text="selectedExpiryItem ? [selectedExpiryItem.brand, selectedExpiryItem.jenis_apar, selectedExpiryItem.kapasitas].filter(Boolean).join(' | ') : '-'"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Saat Ini</p>
                            <p class="mt-1 text-sm font-black text-gray-900" x-text="selectedExpiryItem?.stock_label || '-'"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Masa Berlaku Lama</p>
                            <p class="mt-1 text-sm font-black text-gray-900" x-text="selectedExpiryItem?.old_expiry_label || '-'"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Status Lama</p>
                            <p class="mt-1 text-sm font-black text-gray-900" x-text="selectedExpiryItem?.status_label || '-'"></p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="tanggal_refill" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Tanggal Refill / Isi Ulang</label>
                            <input id="tanggal_refill" type="date" name="tanggal_refill" x-model="refillDateInput" @input="refreshExpiryPreview()" required class="w-full rounded-xl border-none bg-gray-50 px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700">Masa Berlaku Baru</p>
                            <p class="mt-1 text-sm font-black text-emerald-900" x-text="expiryPreviewLabel"></p>
                            <p class="mt-1 text-xs font-semibold text-emerald-700">1 kg otomatis +6 bulan, di atas 1 kg otomatis +1 tahun.</p>
                        </div>
                    </div>

                    <div>
                        <label for="keterangan_refill" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Catatan</label>
                        <textarea id="keterangan_refill" name="keterangan" rows="3" class="w-full rounded-xl border-none bg-gray-50 px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Opsional: catatan pembaruan masa berlaku...">{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeExpiryUpdate()" class="px-5 py-3 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900">Batal</button>
                        <button type="submit" class="rounded-xl bg-amber-600 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-amber-600/20 transition hover:bg-amber-700">Simpan Perbaruan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    [x-cloak] { display: none !important; }
</style>
