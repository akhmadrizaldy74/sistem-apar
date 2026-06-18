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
        $totalUnitApar = $produks->sum(fn ($produk) => (int) ($produk->stok_tersedia ?? 0));
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
        openViewBatchModal: false,
        openRefillModal: false,
        selectedProduct: '',
        selectedBatches: [],
        refillBatch: null,
        refillQty: 1
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
                        <p class="text-xs font-semibold text-gray-500">Halaman ini hanya untuk melihat stok. Penambahan stok APAR dicatat melalui menu Pengeluaran.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.pengeluaran.index', ['open' => 1, 'jenis_pengeluaran' => \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR]) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            Catat Pembelian APAR
                        </a>
                        <a href="{{ route('admin.produk.index') }}" class="text-xs font-black uppercase tracking-widest text-red-600 hover:text-red-700">Kelola Produk</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Produk</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Merek</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis APAR</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Ukuran</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Tersedia</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($produks as $produk)
                                @php
                                    $stok = (int) ($produk->stok_tersedia ?? 0);
                                @endphp
                                <tr class="transition hover:bg-gray-50/40">
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $produk->nama }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-700">{{ $produk->merek ?: '-' }}</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ $produk->jenisApar?->nama ?: '-' }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $produk->kapasitas ?: '-' }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $stok }} unit</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $stok > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $stok > 0 ? 'Tersedia' : 'Kosong' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.pengeluaran.index', ['open' => 1, 'jenis_pengeluaran' => \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR, 'produk_id' => $produk->id]) }}" class="rounded-xl bg-red-700 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-red-800">
                                                Tambah Stok
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data produk APAR.</td>
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
                    <p class="mt-1 text-xs font-semibold text-gray-500">Riwayat ini ditarik dari tabel transaksi yang sudah ada: pengeluaran, pesanan, service, dan tugas refill.</p>
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
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $movement->item_type_label }}</td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $movement->item_nama }}</p>
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">{{ $movement->satuan }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-semibold text-gray-600">{{ $movement->source_label }}</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $isIn ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $isIn ? '+' : '-' }}{{ $formatQty($movement->qty) }} {{ $movement->satuan }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-semibold text-gray-500">{{ $movement->keterangan ?: '-' }}</td>
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

        <div x-show="openViewBatchModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openViewBatchModal = false"></div>
            <div
                x-show="openViewBatchModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                class="relative w-full max-w-4xl overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl shadow-gray-900/20"
            >
                <div class="flex items-center justify-between border-b border-gray-100 bg-white px-8 py-6">
                    <div>
                        <h3 class="text-xl font-black text-gray-900" x-text="'Rincian Batch: ' + selectedProduct"></h3>
                        <p class="mt-1 text-xs font-semibold text-gray-500">Daftar stok yang diurutkan berdasarkan kedaluwarsa terdekat.</p>
                    </div>
                    <button type="button" @click="openViewBatchModal = false" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 transition hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto bg-gray-50/30 p-8">
                    <div class="grid grid-cols-1 gap-4">
                        <template x-for="batch in selectedBatches" :key="batch.id">
                            <div class="relative flex flex-col items-start justify-between gap-4 overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md sm:flex-row sm:items-center">
                                <div
                                    class="absolute left-0 top-0 bottom-0 w-1.5"
                                    :class="{
                                        'bg-emerald-500': batch.status === 'Aman',
                                        'bg-amber-500': batch.status === 'Hampir Expired',
                                        'bg-red-500': batch.status === 'Expired',
                                        'bg-blue-500': batch.status === 'Menunggu Refill'
                                    }"
                                ></div>

                                <div class="pl-3">
                                    <h4 class="text-sm font-black text-gray-900" x-text="'Batch ' + batch.tgl_produksi"></h4>
                                    <p class="mt-1 text-[13px] font-bold text-gray-600" x-text="batch.sisa_qty + ' unit tersisa'"></p>
                                    <div class="mt-2.5 flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>Expired: <span x-text="batch.tgl_expired"></span> <template x-if="batch.days_left >= 0"><span class="text-gray-400" x-text="'(' + batch.days_left + ' hari lagi)'"></span></template></span>
                                    </div>
                                </div>

                                <div class="flex w-full flex-col items-end gap-3 pl-3 sm:w-auto">
                                    <span class="inline-flex items-center rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest" :class="batch.class" x-text="batch.status"></span>

                                    <template x-if="batch.status === 'Expired' && batch.sisa_qty > 0">
                                        <button
                                            type="button"
                                            @click="
                                                refillBatch = batch;
                                                refillQty = batch.sisa_qty;
                                                openViewBatchModal = false;
                                                openRefillModal = true;
                                            "
                                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700 sm:w-auto"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                            Refill Sekarang
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedBatches.length === 0">
                            <div class="rounded-2xl border border-dashed border-gray-100 bg-white px-4 py-12 text-center">
                                <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p class="text-sm font-semibold text-gray-400">Belum ada rincian batch terdaftar untuk produk ini.</p>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="border-t border-gray-100 bg-gray-50/60 px-8 py-5 text-right">
                    <button type="button" @click="openViewBatchModal = false" class="rounded-xl border border-gray-100 bg-white px-6 py-3 text-xs font-black uppercase tracking-widest text-gray-700 transition hover:shadow">Tutup</button>
                </div>
            </div>
        </div>

        <div x-show="openRefillModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openRefillModal = false; refillBatch = null;"></div>
            <div
                x-show="openRefillModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                class="relative w-full max-w-lg overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl shadow-gray-900/20"
            >
                <div class="flex items-center justify-between border-b border-gray-100 bg-white px-8 py-6">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Isi Ulang (Refill) Batch</h3>
                        <p class="mt-1 text-xs font-semibold text-gray-500" x-text="refillBatch ? 'Refill dari batch produksi ' + refillBatch.tgl_produksi : ''"></p>
                    </div>
                    <button type="button" @click="openRefillModal = false; refillBatch = null;" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 transition hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="refillBatch ? '{{ url('/admin/stok/batch') }}/' + refillBatch.id + '/refill' : '#'" method="POST" class="space-y-5 p-8">
                    @csrf

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-gray-400">Produk APAR</p>
                        <p class="text-sm font-black text-gray-900" x-text="selectedProduct"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="refill_qty" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Jumlah Refill</label>
                            <input type="number" name="jumlah_masuk" id="refill_qty" min="1" x-model="refillQty" :max="refillBatch ? refillBatch.sisa_qty : 1" required class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                            <p class="mt-1 text-[10px] text-gray-400" x-text="refillBatch ? 'Maksimal: ' + refillBatch.sisa_qty + ' unit' : ''"></p>
                        </div>
                    </div>

                    <div>
                        <label for="keterangan_refill" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="keterangan_refill" rows="2" class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Opsional: Keterangan / instruksi untuk teknisi..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="openRefillModal = false; refillBatch = null;" class="px-5 py-3 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900">Batal</button>
                        <button type="submit" class="rounded-xl bg-red-700 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/30 transition hover:bg-red-800">Buat Tugas Refill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    [x-cloak] { display: none !important; }
</style>
