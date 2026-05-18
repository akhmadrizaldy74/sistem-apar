<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900">Manajemen Pengeluaran</h2>
                <p class="text-sm font-medium text-gray-500">Kelola data pengeluaran pembelian APAR, refil, dan peralatan.</p>
            </div>
            <button
                type="button"
                onclick="openPengeluaranModal()"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-700 px-6 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/20 transition hover:bg-red-800"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                Tambah Pengeluaran
            </button>
        </div>
    </x-slot>

    @php
        $totalPengeluaran = $pengeluarans->sum(fn ($item) => (float) ($item->nominal ?? 0));
        $totalApar = $pengeluarans->where('jenis_pengeluaran', \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR)->sum('qty');
        $totalRefill = $pengeluarans->where('jenis_pengeluaran', \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL)->sum('qty');
        $totalPeralatan = $pengeluarans->where('jenis_pengeluaran', \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN)->sum('qty');
        $productOptions = $produks->map(fn ($item) => [
            'id' => $item->id,
            'nama' => $item->nama,
            'merek' => $item->merek,
            'jenis_apar' => $item->jenisApar?->nama,
            'kapasitas' => $item->kapasitas,
            'stok' => (int) ($item->stok_tersedia ?? 0),
        ])->values();
        $refillOptions = $jenisRefills->map(fn ($item) => [
            'id' => $item->id,
            'nama' => $item->nama,
            'satuan' => $item->satuan_label,
            'stok' => (float) ($item->stok ?? 0),
            'harga' => (float) ($item->harga ?? 0),
        ])->values();
        $peralatanOptions = $peralatans->map(fn ($item) => [
            'id' => $item->id,
            'nama' => $item->nama,
            'stok' => (int) ($item->stok ?? 0),
            'harga' => (float) ($item->harga_standar ?? 0),
        ])->values();
        $prefillJenisPengeluaran = old('jenis_pengeluaran', request('jenis_pengeluaran', ''));
        $prefillProdukId = old('produk_id', request('produk_id', ''));
        $prefillJenisRefillId = old('jenis_refill_id', request('jenis_refill_id', ''));
        $prefillPeralatanId = old('peralatan_id', request('peralatan_id', ''));
        $prefillQty = old('qty', '');
        $prefillHargaBeli = old('harga_beli', '');
        $shouldOpenModal = $errors->any() || request()->boolean('open') || request()->filled('jenis_pengeluaran');
        $formatQty = static function ($value) {
            $number = (float) $value;
            if ((int) $number === $number) {
                return number_format($number, 0, ',', '.');
            }

            return rtrim(rtrim(number_format($number, 2, ',', '.'), '0'), ',');
        };
    @endphp

    <div id="pengeluaranPage" class="space-y-6" x-data="pengeluaranForm(
        @js($productOptions),
        @js($refillOptions),
        @js($peralatanOptions),
        {
            jenisPengeluaran: @js($prefillJenisPengeluaran),
            produkId: @js($prefillProdukId),
            jenisRefillId: @js($prefillJenisRefillId),
            peralatanId: @js($prefillPeralatanId),
            qty: @js($prefillQty),
            hargaBeli: @js($prefillHargaBeli),
            shouldOpenModal: @js($shouldOpenModal),
        }
    )">
        @if($errors->any())
            <div class="rounded-[2rem] border border-red-100 bg-red-50/70 p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-600">Validasi Form</p>
                <ul class="mt-2 space-y-1 text-sm font-semibold text-red-900">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[2rem] border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Pembelian</p>
                <p class="mt-3 text-3xl font-black text-gray-900">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-500">{{ $pengeluarans->count() }} transaksi tercatat</p>
            </div>
            <div class="rounded-[2rem] border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pembelian APAR</p>
                <p class="mt-3 text-3xl font-black text-gray-900">{{ number_format((float) $totalApar, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-500">Unit APAR yang masuk ke stok</p>
            </div>
            <div class="rounded-[2rem] border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pembelian Refil</p>
                <p class="mt-3 text-3xl font-black text-gray-900">{{ $formatQty($totalRefill) }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-500">Akumulasi kuantitas media refil</p>
            </div>
            <div class="rounded-[2rem] border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pembelian Peralatan</p>
                <p class="mt-3 text-3xl font-black text-gray-900">{{ number_format((float) $totalPeralatan, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-500">Akumulasi unit peralatan</p>
            </div>
        </div>

        <div class="rounded-[2rem] border border-blue-100 bg-blue-50/70 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">Alur Sistem</p>
            <p class="mt-2 text-sm font-semibold leading-relaxed text-blue-900">Tambah stok APAR, refil, dan peralatan sekarang dicatat dari menu Pengeluaran. Stok tidak diubah manual, tetapi bertambah otomatis setelah transaksi pembelian disimpan.</p>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8 lg:py-6">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Riwayat Transaksi Pengeluaran</h3>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Semua pembelian stok tersimpan dengan harga saat transaksi dibuat.</p>
                </div>
            </div>
            <div class="responsive-table-wrap">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/70">
                        <tr>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Tanggal</th>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Jenis Pembelian</th>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Item</th>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Kuantitas</th>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Harga / Unit</th>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Total</th>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Keterangan</th>
                            <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6 lg:px-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pengeluarans as $item)
                            <tr class="transition hover:bg-gray-50/40">
                                <td class="px-4 py-5 text-sm font-bold text-gray-900 sm:px-6 lg:px-8">{{ optional($item->tanggal)->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-5 sm:px-6 lg:px-8">
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-gray-700">
                                        {{ $item->jenis_pengeluaran_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-5 sm:px-6 lg:px-8">
                                    <p class="text-sm font-black text-gray-900">{{ $item->display_item_name }}</p>
                                    @if($item->produk)
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">{{ $item->produk->merek ?: '-' }} • {{ $item->produk->jenisApar?->nama ?: '-' }} • {{ $item->produk->kapasitas ?: '-' }}</p>
                                    @elseif($item->jenisRefill)
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">Jenis Refil dari master data</p>
                                    @elseif($item->peralatan)
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">Peralatan dari master data</p>
                                    @elseif($item->isLegacyOtherExpense())
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">Data lama non-stok</p>
                                    @endif
                                </td>
                                <td class="px-4 py-5 text-sm font-black text-gray-900 sm:px-6 lg:px-8">
                                    {{ $item->qty !== null ? $formatQty($item->qty) . ' ' . ($item->satuan ?: '') : '-' }}
                                </td>
                                <td class="px-4 py-5 text-sm font-bold text-gray-700 sm:px-6 lg:px-8">
                                    {{ $item->harga_beli !== null ? 'Rp ' . number_format((float) $item->harga_beli, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-5 text-sm font-black text-red-700 sm:px-6 lg:px-8">
                                    Rp {{ number_format((float) ($item->total ?? $item->nominal ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-5 text-sm font-semibold text-gray-600 sm:px-6 lg:px-8">{{ $item->keterangan ?: '-' }}</td>
                                <td class="px-4 py-5 sm:px-6 lg:px-8">
                                    @if($item->isStockAffecting())
                                        <div class="max-w-[220px] rounded-xl bg-amber-50 px-3 py-2 text-[11px] font-semibold leading-relaxed text-amber-800">
                                            Data pengeluaran tersimpan dan stok sudah diperbarui otomatis.
                                        </div>
                                    @else
                                        <form action="{{ route('admin.pengeluaran.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus transaksi pengeluaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-sm font-semibold text-gray-500 sm:px-6 lg:px-8">Belum ada transaksi pembelian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="pengeluaranModal" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-gray-950/55 p-3 backdrop-blur-sm sm:p-4 lg:p-6" onclick="if (event.target === this) closePengeluaranModal()">
            <div class="app-modal-shell mx-auto my-3 flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl sm:my-6">
                <div class="app-modal-header shrink-0 flex flex-col gap-4 border-b border-gray-100 bg-white/95 px-5 py-4 backdrop-blur sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8 lg:py-6">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Tambah Data Pengeluaran</h3>
                        <p class="mt-1 text-xs font-semibold text-gray-500">Isi data pengeluaran, lalu klik Simpan.</p>
                    </div>
                    <button type="button" onclick="closePengeluaranModal()" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 transition hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form id="pengeluaranForm" action="{{ route('admin.pengeluaran.store') }}" method="POST" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <input type="hidden" name="qty" id="hidden_qty">
                    <input type="hidden" name="harga_beli" id="hidden_harga_beli">

                    <div class="app-modal-body min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-4 sm:px-6 lg:px-8">
                    <div class="grid gap-3 lg:grid-cols-2">
                        <div>
                            <label for="tanggal" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Tanggal</label>
                            <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full rounded-xl border-none bg-gray-50 px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                        </div>
                        <div>
                            <label for="jenis_pengeluaran" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis Pembelian</label>
                            <select id="jenis_pengeluaran" name="jenis_pengeluaran" x-model="jenisPengeluaran" required class="w-full rounded-xl border-none bg-gray-50 px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                                <option value="">Pilih Jenis Pembelian</option>
                                <option value="{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}">Pembelian APAR</option>
                                <option value="{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}">Pembelian Refil</option>
                                <option value="{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}">Pembelian Peralatan</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-5">
                            <div x-show="!jenisPengeluaran" x-cloak class="rounded-[1.5rem] border border-dashed border-gray-200 bg-gray-50/50 p-5 sm:p-6">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Detail Pembelian</p>
                                <h4 class="mt-2 text-base font-black text-gray-900">Pilih jenis pembelian terlebih dahulu</h4>
                                <p class="mt-2 text-sm font-semibold leading-relaxed text-gray-500">Setelah Anda memilih `Pembelian APAR`, `Pembelian Refill`, atau `Pembelian Peralatan`, section detail akan tampil sesuai jenisnya tanpa membuat modal berantakan.</p>
                            </div>

                            <template x-if="jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}'">
                                <div class="space-y-4 rounded-[1.5rem] border border-gray-100 bg-gray-50/60 p-4 sm:p-5">
                                    <div class="border-b border-gray-200 pb-3">
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Detail Pembelian APAR</p>
                                        <p class="mt-1 text-xs font-semibold text-gray-500">Pilih produk APAR, isi kuantitas dan harga modal, lalu total dihitung otomatis.</p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                        <div class="lg:col-span-2">
                                            <label for="produk_id" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Pilih Produk APAR</label>
                                            <select id="produk_id" name="produk_id" x-model="selectedProdukId" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}'" class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                                                <option value="">Pilih Produk APAR</option>
                                                @foreach($produks as $produk)
                                                    <option value="{{ $produk->id }}">
                                                        {{ $produk->nama }}{{ $produk->merek ? ' - ' . $produk->merek : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Nama Produk</p>
                                            <p class="mt-1 truncate text-sm font-black text-gray-900" x-text="currentProduct?.nama || '-'"></p>
                                        </div>
                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Merek</p>
                                            <p class="mt-1 truncate text-sm font-black text-gray-900" x-text="currentProduct?.merek || '-'"></p>
                                        </div>
                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis APAR</p>
                                            <p class="mt-1 truncate text-sm font-black text-gray-900" x-text="currentProduct?.jenis_apar || '-'"></p>
                                        </div>
                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Ukuran</p>
                                            <p class="mt-1 truncate text-sm font-black text-gray-900" x-text="currentProduct?.kapasitas || '-'"></p>
                                        </div>
                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Saat Ini</p>
                                            <p class="mt-1 text-sm font-bold text-gray-900" x-text="currentProduct ? `${formatQty(currentProduct.stok || 0)} Unit` : '-'"></p>
                                        </div>
                                        <div>
                                            <label for="qty_apar" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Kuantitas</label>
                                            <input id="qty_apar" name="qty_apar" type="number" step="1" min="1" x-model.number="qty" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}'" class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Contoh: 5">
                                        </div>
                                        <div>
                                            <label for="harga_beli_display" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Modal per Unit</label>
                                            <input id="harga_beli_display" name="harga_beli_display" type="text" inputmode="numeric" :value="formattedHargaBeliInput" @input="setHargaBeliInput($event.target.value)" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}'" class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Rp 0">
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Ringkasan Stok APAR</p>
                                            <p class="text-xs font-black text-emerald-700" x-text="'Setelah pembelian: ' + stockAfterText"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'">
                                <div class="space-y-4 rounded-[1.5rem] border border-gray-100 bg-gray-50/60 p-4 sm:p-5">
                                    <div class="border-b border-gray-200 pb-3">
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Detail Pembelian Refill</p>
                                        <p class="mt-1 text-xs font-semibold text-gray-500">Isi jenis, kuantitas, dan harga beli satuan.</p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                        <div class="lg:col-span-2">
                                            <label for="jenis_refill_id" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Pilih Jenis Refill</label>
                                            <select id="jenis_refill_id" name="jenis_refill_id" x-model="selectedRefillId" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'" class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                                                <option value="">Pilih Jenis Refill</option>
                                                <template x-for="item in refillOptions" :key="item.id">
                                                    <option :value="String(item.id)" x-text="item.nama"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Satuan Otomatis</p>
                                            <p class="mt-1 text-sm font-black text-gray-900" x-text="currentUnit || '-'"></p>
                                        </div>
                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Saat Ini</p>
                                            <p class="mt-1 text-sm font-bold text-gray-900" x-text="currentRefill ? `${formatQty(currentRefill.stok || 0)} ${currentUnit}` : '-'"></p>
                                        </div>
                                        <div>
                                            <label for="qty_refill" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Kuantitas</label>
                                            <input id="qty_refill" name="qty_refill" type="number" step="0.01" min="0.01" x-model.number="qty" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'" class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Contoh: 10">
                                        </div>
                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Beli Satuan</p>
                                            <p class="mt-1 text-sm font-bold text-gray-900" x-text="currency(displayPrice)"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'">
                                <div class="space-y-4 rounded-[1.5rem] border border-gray-100 bg-gray-50/60 p-4 sm:p-5">
                                    <div class="border-b border-gray-200 pb-3">
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Detail Pembelian Peralatan</p>
                                        <p class="mt-1 text-xs font-semibold text-gray-500">Pilih peralatan, isi kuantitas, lalu total dihitung otomatis.</p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                        <div class="lg:col-span-2">
                                            <label for="peralatan_id" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Pilih Peralatan</label>
                                            <select id="peralatan_id" name="peralatan_id" x-model="selectedPeralatanId" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'" class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                                                <option value="">Pilih Peralatan</option>
                                                <template x-for="item in peralatanOptions" :key="item.id">
                                                    <option :value="String(item.id)" x-text="item.nama"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Saat Ini</p>
                                            <p class="mt-1 text-sm font-bold text-gray-900" x-text="currentPeralatan ? `${formatQty(currentPeralatan.stok || 0)} Unit` : '-'"></p>
                                        </div>
                                        <div>
                                            <label for="qty_peralatan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Kuantitas</label>
                                            <input id="qty_peralatan" name="qty_peralatan" type="number" step="1" min="1" x-model.number="qty" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'" class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Contoh: 5">
                                        </div>
                                        <div class="rounded-xl bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Beli Satuan</p>
                                            <p class="mt-1 text-sm font-bold text-gray-900" x-text="currency(displayPrice)"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div>
                                <label for="keterangan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" rows="2" class="w-full rounded-xl border-none bg-gray-50 px-4 py-3 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Opsional: nama supplier, nomor invoice, atau catatan pembelian.">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>
                    </div>

                    </div>

                    <div class="app-modal-footer shrink-0 flex flex-col gap-3 border-t border-gray-100 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                        <div x-show="jenisPengeluaran" x-cloak class="min-w-0 rounded-xl bg-red-50 px-4 py-2.5 text-red-700">
                            <p class="text-[9px] font-black uppercase tracking-[0.18em]" x-text="priceSummaryTitle"></p>
                            <p class="mt-1 text-xl font-black leading-none" x-text="currency(total)"></p>
                        </div>
                        <div class="flex flex-col-reverse gap-2 sm:ml-auto sm:flex-row sm:items-center">
                            <button type="button" onclick="closePengeluaranModal()" class="w-full px-5 py-3 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900 sm:w-auto">Batal</button>
                            <button type="submit" class="w-full rounded-xl bg-gray-900 px-6 py-3 text-xs font-black uppercase tracking-widest text-white transition hover:bg-black sm:w-auto">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.syncPengeluaranForm = function() {
            const page = document.getElementById('pengeluaranPage');
            const alpineData = page?._x_dataStack?.[0];

            if (!alpineData) {
                return true;
            }

            document.getElementById('hidden_qty').value = alpineData.submittedQty || '';
            document.getElementById('hidden_harga_beli').value = alpineData.submittedHargaBeli || '';
            return true;
        };

        function openPengeluaranModal() {
            document.getElementById('pengeluaranModal').style.display = 'flex';
            document.body.classList.add('overflow-hidden');
            setTimeout(syncPengeluaranForm, 50);
        }

        function closePengeluaranModal() {
            document.getElementById('pengeluaranModal').style.display = 'none';
            document.body.classList.remove('overflow-hidden');
        }

        function pengeluaranForm(productOptions, refillOptions, peralatanOptions, prefill) {
            return {
                productOptions,
                refillOptions,
                peralatanOptions,
                jenisPengeluaran: prefill.jenisPengeluaran || '',
                selectedProdukId: prefill.produkId || '',
                selectedRefillId: prefill.jenisRefillId || '',
                selectedPeralatanId: prefill.peralatanId || '',
                qty: Number(prefill.qty) || 0,
                hargaBeliInput: prefill.hargaBeli ? String(prefill.hargaBeli).replace(/[^\d]/g, '') : '',
                initialized: false,
                init() {
                    const form = document.getElementById('pengeluaranForm');
                    if (form) {
                        form.addEventListener('submit', () => {
                            syncPengeluaranForm();
                        });
                    }

                    if (prefill.shouldOpenModal) {
                        openPengeluaranModal();
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}' && !this.qty) {
                        this.qty = 1;
                    }

                    this.$nextTick(() => {
                        if (prefill.produkId) {
                            this.selectedProdukId = String(prefill.produkId);
                        }
                        if (prefill.jenisRefillId) {
                            this.selectedRefillId = String(prefill.jenisRefillId);
                        }
                        if (prefill.peralatanId) {
                            this.selectedPeralatanId = String(prefill.peralatanId);
                        }
                        syncPengeluaranForm();
                    });

                    this.$watch('jenisPengeluaran', (value) => {
                        if (!this.initialized) {
                            this.initialized = true;
                            syncPengeluaranForm();
                            return;
                        }

                        this.selectedProdukId = '';
                        this.selectedRefillId = '';
                        this.selectedPeralatanId = '';
                        this.hargaBeliInput = '';
                        this.qty = value === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}' ? 1 : 0;
                        syncPengeluaranForm();
                    });

                    this.$watch('selectedProdukId', () => syncPengeluaranForm());
                    this.$watch('selectedRefillId', () => syncPengeluaranForm());
                    this.$watch('selectedPeralatanId', () => syncPengeluaranForm());
                    this.$watch('qty', () => syncPengeluaranForm());
                    this.$watch('hargaBeliInput', () => syncPengeluaranForm());
                },
                get currentProduct() {
                    return this.productOptions.find(item => String(item.id) === String(this.selectedProdukId)) ?? null;
                },
                get currentRefill() {
                    return this.refillOptions.find(item => String(item.id) === String(this.selectedRefillId)) ?? null;
                },
                get currentPeralatan() {
                    return this.peralatanOptions.find(item => String(item.id) === String(this.selectedPeralatanId)) ?? null;
                },
                get currentItem() {
                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}') {
                        return this.currentProduct;
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}') {
                        return this.currentRefill;
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}') {
                        return this.currentPeralatan;
                    }

                    return null;
                },
                get currentItemName() {
                    return this.currentItem ? this.currentItem.nama : '';
                },
                get currentUnit() {
                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}') {
                        return this.currentRefill?.satuan || '';
                    }

                    if (
                        this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}'
                        || this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'
                    ) {
                        return 'Unit';
                    }

                    return '';
                },
                get displayPrice() {
                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}') {
                        return Number(this.normalizedHargaBeliInput) || 0;
                    }

                    return Number(this.currentItem?.harga || 0);
                },
                get normalizedHargaBeliInput() {
                    return String(this.hargaBeliInput || '').replace(/[^\d]/g, '');
                },
                get submittedHargaBeli() {
                    if (this.jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}') {
                        return '';
                    }

                    return this.normalizedHargaBeliInput;
                },
                get hargaBeli() {
                    if (this.jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}') {
                        return null;
                    }

                    return Number(this.normalizedHargaBeliInput) || null;
                },
                get submittedQty() {
                    if (!this.qty) {
                        return '';
                    }

                    return this.qty;
                },
                get formattedHargaBeliInput() {
                    if (this.normalizedHargaBeliInput === '') {
                        return '';
                    }

                    return this.currency(this.normalizedHargaBeliInput);
                },
                get total() {
                    return (Number(this.qty) || 0) * this.displayPrice;
                },
                get currentStockText() {
                    if (!this.currentItem) {
                        return '-';
                    }

                    return `${this.formatQty(this.currentItem.stok || 0)} ${this.currentUnit}`;
                },
                get stockAfterText() {
                    if (!this.currentItem) {
                        return '-';
                    }

                    const hasil = (Number(this.currentItem.stok) || 0) + (Number(this.qty) || 0);
                    return `${this.formatQty(hasil)} ${this.currentUnit}`;
                },
                get priceSummaryTitle() {
                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}') {
                        return 'Total Pengeluaran Pembelian APAR';
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}') {
                        return 'Total Pengeluaran Pembelian Refil';
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}') {
                        return 'Total Pengeluaran Pembelian Peralatan';
                    }

                    return 'Total Pengeluaran';
                },
                get priceSummaryText() {
                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR }}') {
                        return 'Harga modal APAR diisi manual per transaksi pembelian. Harga jual produk tetap berasal dari menu Produk.';
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}') {
                        return 'Harga standar refil diambil otomatis dari master data dan total dihitung langsung dari kuantitas pembelian.';
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}') {
                        return 'Harga standar peralatan diambil otomatis dari master data dan total dihitung langsung dari kuantitas pembelian.';
                    }

                    return 'Nominal pengeluaran akan tampil otomatis dalam format Rupiah Indonesia.';
                },
                setHargaBeliInput(value) {
                    this.hargaBeliInput = String(value || '').replace(/[^\d]/g, '');
                },
                formatQty(value) {
                    const number = Number(value) || 0;
                    if (Number.isInteger(number)) {
                        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(number);
                    }

                    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(number);
                },
                currency(value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(Number(value) || 0);
                },
            };
        }
    </script>
</x-app-layout>
