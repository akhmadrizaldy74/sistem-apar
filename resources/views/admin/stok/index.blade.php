<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen Stok</h2>
                <p class="text-sm text-gray-500 font-medium">Satu halaman untuk monitoring stok APAR, media refill, dan peralatan operasional beserta riwayat mutasinya.</p>
            </div>
        </div>
    </x-slot>

    @php
        $aparRendah = $produks->filter(fn ($produk) => (int) ($produk->stok_tersedia ?? 0) <= (int) ($produk->stok_minimum ?? 5))->count();
        $refillRendah = $jenisRefills->filter(fn ($jenisRefill) => (float) ($jenisRefill->stok ?? 0) <= (float) ($jenisRefill->stok_minimum ?? 0))->count();
        $peralatanRendah = $peralatans->filter(fn ($peralatan) => (int) ($peralatan->stok ?? 0) <= (int) ($peralatan->stok_minimum ?? 3))->count();
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
        openBatchModal: false,
        openViewBatchModal: false,
        openRefillModal: false,
        selectedProduct: '',
        selectedProductId: '',
        selectedBatches: [],
        refillBatch: null,
        refillQty: 1,
        openPeralatanModal: false,
        editPeralatan: null,
        deletingPeralatan: null
    }"
    @open-batch-modal.window="openBatchModal = true; selectedProductId = ''">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok APAR</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ $produks->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $aparRendah }} item perlu restok</p>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok Refill</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ $jenisRefills->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $refillRendah }} media di bawah minimum</p>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok Peralatan</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ $peralatans->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $peralatanRendah }} item perlu restok</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 p-1.5 bg-gray-100 rounded-2xl w-fit">
                <button
                    type="button"
                    @click="tab = 'apar'"
                    :class="tab === 'apar' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition"
                >
                    APAR
                </button>
                <button
                    type="button"
                    @click="tab = 'refill'"
                    :class="tab === 'refill' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition"
                >
                    Media Refill
                </button>
                <button
                    type="button"
                    @click="tab = 'peralatan'"
                    :class="tab === 'peralatan' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition"
                >
                    Peralatan
                </button>
            </div>

            <section x-show="tab === 'apar'" class="rounded-[2rem] border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Stok APAR</h3>
                        <p class="text-xs font-semibold text-gray-500">Pantau stok produk APAR yang siap dijual.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-batch-modal'))" class="px-5 py-2.5 bg-red-700 text-white font-black rounded-xl hover:bg-red-800 transition shadow-lg shadow-red-700/20 flex items-center gap-2 uppercase tracking-widest text-[10px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            Tambah Stok
                        </button>
                        <a href="{{ route('admin.produk.index') }}" class="text-xs font-black uppercase tracking-widest text-red-600 hover:text-red-700">Kelola Produk</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Merek</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Ukuran</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sisa Stok</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Min. Stok</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($produks as $produk)
                                @php
                                    $stok = (int) ($produk->stok_tersedia ?? 0);
                                    $stokBatchTotal = (int) ($produk->stok_batch_total ?? ($produk->stok ?? 0));
                                    $stokMinimum = (int) ($produk->stok_minimum ?? 5);
                                    $rendah = $stok <= $stokMinimum;

                                    $batchData = $produk->stokBatches->where('sisa_qty', '>', 0)->sortBy('tgl_expired')->values()->map(function($b) {
                                        $today = now()->startOfDay();
                                        $expiredDate = $b->tgl_expired ? $b->tgl_expired->copy()->startOfDay() : null;
                                        $isExpired = $expiredDate && $expiredDate->isPast();
                                        $daysLeft = $expiredDate ? (int) $today->diffInDays($expiredDate, false) : 0;
                                        $isNear = !$isExpired && $daysLeft <= 30;
                                        $pendingRefill = $b->tugasRefills->whereIn('status', ['menunggu', 'diproses'])->count() > 0;
                                        
                                        if ($pendingRefill) { $status = 'Menunggu Refill'; $class = 'bg-blue-50 text-blue-700 border border-blue-200'; }
                                        elseif ($isExpired) { $status = 'Expired'; $class = 'bg-red-50 text-red-700 border border-red-200'; }
                                        elseif ($isNear) { $status = 'Hampir Expired'; $class = 'bg-amber-50 text-amber-700 border border-amber-200'; }
                                        else { $status = 'Aman'; $class = 'bg-emerald-50 text-emerald-700 border border-emerald-200'; }

                                        return [
                                            'id' => $b->id,
                                            'jumlah_masuk' => $b->jumlah_masuk,
                                            'sisa_qty' => $b->sisa_qty,
                                            'tgl_produksi' => $b->tgl_produksi ? $b->tgl_produksi->format('d M Y') : '-',
                                            'tgl_expired' => $b->tgl_expired ? $b->tgl_expired->format('d M Y') : '-',
                                            'days_left' => $daysLeft,
                                            'status' => $status,
                                            'class' => $class,
                                            'keterangan' => $b->keterangan ?: '-'
                                        ];
                                    });
                                @endphp
                                <tr class="hover:bg-gray-50/40 transition">
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $produk->nama }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-700">{{ $produk->merek ?? '-' }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $produk->kapasitas ? $produk->kapasitas.' Kg/L' : '-' }}</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ $produk->jenisApar?->nama ?? '-' }}</td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $stok }} unit</p>
                                        <p class="text-[10px] font-semibold text-gray-400 mt-1">Siap jual dari total batch {{ $stokBatchTotal }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ $stokMinimum }} unit</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $rendah ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $stok <= 0 ? 'Habis' : ($rendah ? 'Stok Rendah' : 'Aman') }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right flex items-center justify-end gap-2">
                                        <button type="button" 
                                            @click="
                                                selectedProduct = '{{ $produk->nama }} ({{ $produk->merek }} - {{ $produk->kapasitas }})';
                                                selectedProductId = '{{ $produk->id }}';
                                                selectedBatches = {{ json_encode($batchData) }};
                                                openViewBatchModal = true;
                                            "
                                            class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition border border-gray-100">
                                            Lihat Batch
                                        </button>
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

            <section x-show="tab === 'refill'" x-cloak class="rounded-[2rem] border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Stok Refill</h3>
                        <p class="text-xs font-semibold text-gray-500">Pantau media isi ulang APAR. Penambahan stok dilakukan lewat transaksi pembelian di menu Pengeluaran.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.pengeluaran.index') }}" class="text-xs font-black uppercase tracking-widest text-red-600 hover:text-red-700">Catat Pembelian</a>
                        <a href="{{ route('admin.refill.index') }}" class="text-xs font-black uppercase tracking-widest text-gray-500 hover:text-gray-700">Riwayat Refill</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Media Refill</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok Saat Ini</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Min. Stok</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Harga Standar</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($jenisRefills as $jenisRefill)
                                @php
                                    $stok = (float) ($jenisRefill->stok ?? 0);
                                    $stokMinimum = (float) ($jenisRefill->stok_minimum ?? 0);
                                    $satuan = $jenisRefill->satuan_label;
                                    $rendah = $stok <= $stokMinimum;
                                @endphp
                                <tr class="hover:bg-gray-50/40 transition">
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $jenisRefill->nama }}</p>
                                        <p class="text-xs font-semibold text-gray-500 mt-1">Stok {{ $jenisRefill->nama }}: {{ $formatQty($stok) }} {{ $satuan }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $formatQty($stok) }} {{ $satuan }}</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ $formatQty($stokMinimum) }} {{ $satuan }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">Rp {{ number_format((float) ($jenisRefill->harga ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $rendah ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $rendah ? 'Stok Rendah' : 'Aman' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data media refill.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section x-show="tab === 'peralatan'" x-cloak class="rounded-[2rem] border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Stok Peralatan</h3>
                        <p class="text-xs font-semibold text-gray-500">Pantau stok alat bantu service dan operasional lapangan. Tambah stok dilakukan lewat transaksi pembelian di menu Pengeluaran.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.peralatan.index') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 font-black rounded-xl hover:bg-gray-200 transition uppercase tracking-widest text-[10px]">
                            Kelola Master
                        </a>
                        <a href="{{ route('admin.pengeluaran.index') }}" class="px-5 py-2.5 bg-red-700 text-white font-black rounded-xl hover:bg-red-800 transition shadow-lg shadow-red-700/20 uppercase tracking-widest text-[10px]">
                            Catat Pembelian
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Peralatan</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Min. Stok</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Harga Standar</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($peralatans as $peralatan)
                                @php
                                    $stok = (int) ($peralatan->stok ?? 0);
                                    $stokMinimum = (int) ($peralatan->stok_minimum ?? 3);
                                    $rendah = $stok <= $stokMinimum;
                                @endphp
                                <tr class="hover:bg-gray-50/40 transition">
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $peralatan->nama }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $stok }} unit</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ $stokMinimum }} unit</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">Rp {{ number_format((float) ($peralatan->harga_standar ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $rendah ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $rendah ? 'Stok Rendah' : 'Aman' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-semibold text-gray-500">
                                        {{ $rendah ? 'Segera lakukan pembelian agar stok tidak kosong.' : 'Monitoring stok berjalan normal.' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data peralatan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-[2rem] border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-black text-gray-900">Riwayat Perubahan Stok</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-1">Mutasi stok terbaru dari pembelian, penjualan produk, refill pelanggan, service, dan proses batch APAR.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kategori</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Item</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sumber</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Perubahan</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok Sebelum</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok Sesudah</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($stockMovements as $movement)
                                @php
                                    $isIn = $movement->movement_type === \App\Models\StockMovement::MOVE_IN;
                                @endphp
                                <tr class="hover:bg-gray-50/40 transition">
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
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ $formatQty($movement->stok_sebelum) }} {{ $movement->satuan }}</td>
                                    <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $formatQty($movement->stok_sesudah) }} {{ $movement->satuan }}</td>
                                    <td class="px-8 py-5 text-sm font-semibold text-gray-500">{{ $movement->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada riwayat perubahan stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- MODAL TAMBAH/EDIT PERALATAN --}}
            <div x-show="openPeralatanModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openPeralatanModal = false; editPeralatan = null;"></div>
                <div
                    x-show="openPeralatanModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                    class="relative w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
                >
                    <div class="flex items-center justify-between px-8 py-6 bg-white border-b border-gray-100">
                        <div>
                            <h3 class="text-xl font-black text-gray-900" x-text="editPeralatan ? 'Edit Peralatan' : 'Tambah Peralatan'"></h3>
                            <p class="text-xs font-semibold text-gray-500 mt-1" x-text="editPeralatan ? 'Perbarui data peralatan.' : 'Tambah alat bantu service.'"></p>
                        </div>
                        <button type="button" @click="openPeralatanModal = false; editPeralatan = null;" class="w-10 h-10 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form :action="editPeralatan ? '{{ url('/admin/stok/peralatan') }}/' + editPeralatan.id : '{{ url('/admin/stok/peralatan') }}'" :method="editPeralatan ? 'PUT' : 'POST'" class="p-8 space-y-5">
                        <template x-if="editPeralatan">
                            @method('PUT')
                        </template>
                        @csrf

                        <div>
                            <label for="peralatan_nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Peralatan</label>
                            <input type="text" name="nama" id="peralatan_nama" x-model="editPeralatan ? editPeralatan.nama : ''" required placeholder="cth: Kunci Pas 10mm" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="peralatan_stok" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Stok</label>
                                <input type="number" name="stok" id="peralatan_stok" x-model="editPeralatan ? editPeralatan.stok : ''" min="0" required class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                            </div>
                            <div>
                                <label for="peralatan_stok_minimum" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Min. Stok</label>
                                <input type="number" name="stok_minimum" id="peralatan_stok_minimum" x-model="editPeralatan ? editPeralatan.stok_minimum : ''" min="0" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="peralatan_harga" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Harga Standar</label>
                                <input type="number" name="harga_standar" id="peralatan_harga" x-model="editPeralatan ? editPeralatan.harga_standar : ''" min="0" required class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                            </div>
                            <div>
                                <label for="peralatan_biaya_beli" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Biaya Beli (Restok)</label>
                                <input type="number" name="biaya_beli" id="peralatan_biaya_beli" min="0" placeholder="0" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="openPeralatanModal = false; editPeralatan = null;" class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</button>
                            <button type="submit" class="px-6 py-3.5 bg-red-700 text-white font-black rounded-xl hover:bg-red-800 transition shadow-lg shadow-red-700/30 uppercase tracking-widest text-xs" x-text="editPeralatan ? 'Simpan Perubahan' : 'Tambah'"></button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL KONFIRMASI HAPUS PERALATAN --}}
            <div x-show="deletingPeralatan" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="deletingPeralatan = null;"></div>
                <div
                    x-show="deletingPeralatan"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                    class="relative w-full max-w-sm overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
                >
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 mb-2">Hapus Peralatan?</h3>
                        <p class="text-sm font-semibold text-gray-500 mb-6">Yakin ingin menghapus <span class="font-black text-gray-900" x-text="deletingPeralatan ? deletingPeralatan.nama : ''"></span>? Data yang dihapus tidak bisa dikembalikan.</p>
                        <div class="flex gap-3">
                            <button type="button" @click="deletingPeralatan = null;" class="flex-1 px-5 py-3 bg-gray-100 text-gray-700 font-black rounded-xl hover:bg-gray-200 transition text-sm">Batal</button>
                            <form :action="'{{ url('/admin/stok/peralatan') }}/' + (deletingPeralatan ? deletingPeralatan.id : '')" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-5 py-3 bg-red-600 text-white font-black rounded-xl hover:bg-red-700 transition text-sm">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL TAMBAH BATCH --}}
        <div x-show="openBatchModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openBatchModal = false"></div>
            <div
                x-show="openBatchModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="flex items-center justify-between px-8 py-6 bg-white border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Tambah Batch Stok</h3>
                        <p class="text-xs font-semibold text-gray-500 mt-1">Gunakan tanggal produksi material sebagai filter expired.</p>
                    </div>
                    <button type="button" @click="openBatchModal = false" class="w-10 h-10 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.stok.batch.store') }}" method="POST" class="p-8 space-y-5">
                    @csrf
                    <div>
                        <label for="produk_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Produk APAR</label>
                        <select name="produk_id" id="produk_id" required class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                            <option value="">Pilih Produk</option>
                            @foreach($produks as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->merek }} - {{ $p->kapasitas }} Kg/L)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="jumlah_masuk" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah Masuk</label>
                            <input type="number" name="jumlah_masuk" id="jumlah_masuk" min="1" required class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                        </div>
                        <div>
                            <label for="tgl_produksi" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tgl Produksi</label>
                            <input type="date" name="tgl_produksi" id="tgl_produksi" required class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Keterangan Batch</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition" placeholder="Opsional: Keterangan pengiriman/suplier..."></textarea>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 text-[10px] text-gray-400 font-bold tracking-wide italic">
                        * Aturan Expired Otomatis:<br>
                        - APAR 1 kg = Tgl Produksi + 6 Bulan<br>
                        - APAR lainnya = Tgl Produksi + 1 Tahun
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="openBatchModal = false" class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</button>
                        <button type="submit" class="px-6 py-3.5 bg-red-700 text-white font-black rounded-xl hover:bg-red-800 transition shadow-lg shadow-red-700/30 uppercase tracking-widest text-xs">Simpan Batch</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL LIHAT BATCH --}}
        <div x-show="openViewBatchModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openViewBatchModal = false"></div>
            <div
                x-show="openViewBatchModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-4xl overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="flex items-center justify-between px-8 py-6 bg-white border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-black text-gray-900" x-text="'Rincian Batch: ' + selectedProduct"></h3>
                        <p class="text-xs font-semibold text-gray-500 mt-1">Daftar stok yang diurutkan berdasarkan kedaluwarsa terdekat.</p>
                    </div>
                    <button type="button" @click="openViewBatchModal = false" class="w-10 h-10 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-8 max-h-[60vh] overflow-y-auto bg-gray-50/30">
                    <div class="grid grid-cols-1 gap-4">
                        <template x-for="batch in selectedBatches" :key="batch.id">
                            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                                <!-- Indikator warna -->
                                <div class="absolute left-0 top-0 bottom-0 w-1.5" 
                                     :class="{
                                         'bg-emerald-500': batch.status === 'Aman',
                                         'bg-amber-500': batch.status === 'Hampir Expired',
                                         'bg-red-500': batch.status === 'Expired',
                                         'bg-blue-500': batch.status === 'Menunggu Refill'
                                     }">
                                </div>
                                
                                <div class="pl-3">
                                    <h4 class="text-sm font-black text-gray-900" x-text="'Batch ' + batch.tgl_produksi"></h4>
                                    <p class="text-[13px] font-bold text-gray-600 mt-1" x-text="batch.sisa_qty + ' unit tersisa'"></p>
                                    
                                    <div class="mt-2.5 flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>Expired: <span x-text="batch.tgl_expired"></span> <template x-if="batch.days_left >= 0"><span class="text-gray-400" x-text="'(' + batch.days_left + ' hari lagi)'"></span></template></span>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-end gap-3 pl-3 w-full sm:w-auto">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest"
                                        :class="batch.class"
                                        x-text="batch.status">
                                    </span>
                                    
                                    <template x-if="batch.status === 'Expired' && batch.sisa_qty > 0">
                                        <button type="button" 
                                            @click="
                                                refillBatch = batch;
                                                refillQty = batch.sisa_qty;
                                                openViewBatchModal = false;
                                                openRefillModal = true;
                                            "
                                            class="w-full sm:w-auto px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-600/20 transition flex items-center justify-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                            Refill Sekarang
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <template x-if="selectedBatches.length === 0">
                            <div class="text-center py-12 px-4 bg-white rounded-2xl border border-gray-100 border-dashed">
                                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p class="text-sm font-semibold text-gray-400">Belum ada rincian batch terdaftar untuk produk ini.</p>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="px-8 py-5 bg-gray-50/60 border-t border-gray-100 text-right">
                    <button type="button" @click="openViewBatchModal = false" class="px-6 py-3 bg-white text-gray-700 border border-gray-100 font-black text-xs uppercase tracking-widest rounded-xl hover:shadow transition">Tutup</button>
                </div>
            </div>
        </div>

        {{-- MODAL REFILL BATCH --}}
        <div x-show="openRefillModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openRefillModal = false; refillBatch = null;"></div>
            <div
                x-show="openRefillModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="flex items-center justify-between px-8 py-6 bg-white border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Isi Ulang (Refill) Batch</h3>
                        <p class="text-xs font-semibold text-gray-500 mt-1" x-text="refillBatch ? 'Refill dari batch produksi ' + refillBatch.tgl_produksi : ''"></p>
                    </div>
                    <button type="button" @click="openRefillModal = false; refillBatch = null;" class="w-10 h-10 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="refillBatch ? '{{ url('/admin/stok/batch') }}/' + refillBatch.id + '/refill' : '#'" method="POST" class="p-8 space-y-5">
                    @csrf
                    
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Produk APAR</p>
                        <p class="text-sm font-black text-gray-900" x-text="selectedProduct"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="refill_qty" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah Refill</label>
                            <input type="number" name="jumlah_masuk" id="refill_qty" min="1" x-model="refillQty" :max="refillBatch ? refillBatch.sisa_qty : 1" required class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition">
                            <p class="text-[10px] text-gray-400 mt-1" x-text="refillBatch ? 'Maksimal: ' + refillBatch.sisa_qty + ' unit' : ''"></p>
                        </div>
                    </div>

                    <div>
                        <label for="keterangan_refill" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="keterangan_refill" rows="2" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm transition" placeholder="Opsional: Keterangan / instruksi untuk teknisi..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="openRefillModal = false; refillBatch = null;" class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</button>
                        <button type="submit" class="px-6 py-3.5 bg-red-700 text-white font-black rounded-xl hover:bg-red-800 transition shadow-lg shadow-red-700/30 uppercase tracking-widest text-xs">Buat Tugas Refill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    [x-cloak] { display: none !important; }
</style>
