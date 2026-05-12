<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900">Pengeluaran</h2>
                <p class="text-sm font-medium text-gray-500">Khusus untuk pembelian refill dan peralatan/perlengkapan yang otomatis menambah stok.</p>
            </div>
            <button
                type="button"
                onclick="openPengeluaranModal()"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-700 px-6 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/20 transition hover:bg-red-800"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                Catat Pembelian
            </button>
        </div>
    </x-slot>

    @php
        $totalPengeluaran = $pengeluarans->sum(fn ($item) => (float) ($item->nominal ?? 0));
        $totalRefill = $pengeluarans->where('jenis_pengeluaran', \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL)->sum('qty');
        $totalPeralatan = $pengeluarans->where('jenis_pengeluaran', \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN)->sum('qty');
        $refillOptions = $jenisRefills->map(fn ($item) => [
            'id' => $item->id,
            'nama' => $item->nama,
            'label' => $item->nama_label,
            'satuan' => $item->satuan_label,
            'stok' => (float) $item->stok,
            'harga' => (float) $item->harga,
        ])->values();
        $peralatanOptions = $peralatans->map(fn ($item) => [
            'id' => $item->id,
            'nama' => $item->nama,
            'stok' => (int) $item->stok,
            'harga' => (float) $item->harga_standar,
            'stok_minimum' => (int) $item->stok_minimum,
        ])->values();
        $formatQty = static function ($value) {
            $number = (float) $value;
            if ((int) $number === $number) {
                return number_format($number, 0, ',', '.');
            }

            return rtrim(rtrim(number_format($number, 2, ',', '.'), '0'), ',');
        };
    @endphp

    <div class="space-y-6" x-data="pengeluaranForm(@js($refillOptions), @js($peralatanOptions))">
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

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Pembelian</p>
                <p class="mt-3 text-3xl font-black text-gray-900">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-500">{{ $pengeluarans->count() }} transaksi tercatat</p>
            </div>
            <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pembelian Refill</p>
                <p class="mt-3 text-3xl font-black text-gray-900">{{ $formatQty($totalRefill) }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-500">Akumulasi qty refill yang masuk ke stok</p>
            </div>
            <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pembelian Peralatan</p>
                <p class="mt-3 text-3xl font-black text-gray-900">{{ number_format((float) $totalPeralatan, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold text-gray-500">Total unit peralatan/perlengkapan yang dibeli</p>
            </div>
        </div>

        <div class="rounded-[2rem] border border-blue-100 bg-blue-50/70 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">Alur Sistem</p>
            <p class="mt-2 text-sm font-semibold leading-relaxed text-blue-900">Setelah transaksi disimpan, stok refill atau peralatan langsung bertambah otomatis. Menu stok hanya dipakai untuk monitoring kondisi stok dan riwayat mutasinya.</p>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-8 py-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Riwayat Transaksi Pengeluaran</h3>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Daftar pembelian yang sudah menambah stok secara otomatis.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/70">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Tanggal</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis Pengeluaran</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Item</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Qty</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Beli</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Total</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pengeluarans as $item)
                            <tr class="transition hover:bg-gray-50/40">
                                <td class="px-8 py-5 text-sm font-bold text-gray-900">{{ optional($item->tanggal)->format('d M Y') ?? '-' }}</td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-gray-700">
                                        {{ $item->jenis_pengeluaran_label }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $item->nama_item ?: '-' }}</p>
                                    @if($item->jenisRefill)
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">Stok refill otomatis bertambah</p>
                                    @elseif($item->peralatan)
                                        <p class="mt-1 text-[11px] font-semibold text-gray-500">Stok peralatan otomatis bertambah</p>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-sm font-black text-gray-900">
                                    {{ $item->qty !== null ? $formatQty($item->qty) . ' ' . ($item->satuan ?: '') : '-' }}
                                </td>
                                <td class="px-8 py-5 text-sm font-bold text-gray-700">
                                    {{ $item->harga_beli !== null ? 'Rp ' . number_format((float) $item->harga_beli, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-8 py-5 text-sm font-black text-red-700">
                                    Rp {{ number_format((float) ($item->total ?? $item->nominal ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-5 text-sm font-semibold text-gray-600">{{ $item->keterangan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada transaksi pembelian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="pengeluaranModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-gray-950/55 p-4 backdrop-blur-sm">
            <div class="w-full max-w-4xl overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl shadow-gray-900/20">
                <div class="flex flex-col gap-4 border-b border-gray-100 px-8 py-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Catat Pengeluaran Pembelian</h3>
                        <p class="mt-1 text-xs font-semibold text-gray-500">Pilih jenis pengeluaran, lengkapi item, lalu stok akan bertambah otomatis setelah disimpan.</p>
                    </div>
                    <button type="button" onclick="closePengeluaranModal()" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 transition hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.pengeluaran.store') }}" method="POST" class="space-y-6 p-8">
                    @csrf

                    <div class="grid gap-4 lg:grid-cols-3">
                        <div class="lg:col-span-1">
                            <label for="tanggal" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Tanggal Transaksi</label>
                            <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                        </div>
                        <div class="lg:col-span-2">
                            <label for="jenis_pengeluaran" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis Pengeluaran</label>
                            <select id="jenis_pengeluaran" name="jenis_pengeluaran" x-model="jenisPengeluaran" required class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                                <option value="">Pilih Jenis Pengeluaran</option>
                                <option value="{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}">Pembelian Refill</option>
                                <option value="{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}">Pembelian Peralatan / Perlengkapan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_340px]">
                        <div class="space-y-5">
                            <div x-show="jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'" x-cloak class="space-y-5 rounded-[1.5rem] border border-gray-100 bg-gray-50/60 p-5">
                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <label for="jenis_refill_id" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis Refill</label>
                                        <select id="jenis_refill_id" name="jenis_refill_id" x-model="selectedRefillId" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'" class="w-full rounded-xl border-none bg-white px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                                            <option value="">Pilih Jenis Refill</option>
                                            <template x-for="item in refillOptions" :key="item.id">
                                                <option :value="String(item.id)" x-text="item.label"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Satuan</label>
                                        <div class="rounded-xl bg-white px-5 py-3.5 text-sm font-black text-gray-900" x-text="currentUnit || '-'"></div>
                                    </div>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <label for="qty_refill" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Qty</label>
                                        <input id="qty_refill" type="number" step="0.01" min="0.01" name="qty" x-model.number="qty" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'" class="w-full rounded-xl border-none bg-white px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Contoh: 50">
                                    </div>
                                    <div>
                                        <label for="harga_beli_refill" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Beli</label>
                                        <input id="harga_beli_refill" type="number" min="0" name="harga_beli" x-model.number="hargaBeli" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'" class="w-full rounded-xl border-none bg-white px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <div x-show="jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'" x-cloak class="space-y-5 rounded-[1.5rem] border border-gray-100 bg-gray-50/60 p-5">
                                <div>
                                    <label for="peralatan_id" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Nama Peralatan / Perlengkapan</label>
                                    <select id="peralatan_id" name="peralatan_id" x-model="selectedPeralatanId" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'" class="w-full rounded-xl border-none bg-white px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                                        <option value="">Pilih Item Master</option>
                                        <template x-for="item in peralatanOptions" :key="item.id">
                                            <option :value="String(item.id)" x-text="item.nama"></option>
                                        </template>
                                    </select>
                                    <p class="mt-2 text-[11px] font-semibold text-gray-500">Daftar item diambil langsung dari master peralatan/perlengkapan sistem.</p>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <label for="qty_peralatan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Qty</label>
                                        <input id="qty_peralatan" type="number" step="1" min="1" name="qty" x-model.number="qty" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'" class="w-full rounded-xl border-none bg-white px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Contoh: 10">
                                    </div>
                                    <div>
                                        <label for="harga_beli_peralatan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Beli</label>
                                        <input id="harga_beli_peralatan" type="number" min="0" name="harga_beli" x-model.number="hargaBeli" :disabled="jenisPengeluaran !== '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}'" class="w-full rounded-xl border-none bg-white px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="keterangan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" rows="4" class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Opsional: nama supplier, nomor invoice, atau catatan pembelian.">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-[1.5rem] border border-gray-100 bg-gray-50/60 p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Ringkasan Stok</p>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500">Item dipilih</p>
                                        <p class="mt-1 text-sm font-black text-gray-900" x-text="currentItemName || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500">Stok saat ini</p>
                                        <p class="mt-1 text-sm font-black text-gray-900" x-text="currentStockText"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500">Stok setelah pembelian</p>
                                        <p class="mt-1 text-sm font-black text-emerald-700" x-text="stockAfterText"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.5rem] border border-red-100 bg-red-50/70 p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-600">Total Otomatis</p>
                                <p class="mt-3 text-3xl font-black text-red-700" x-text="currency(total)"></p>
                                <p class="mt-2 text-xs font-semibold text-red-900">Total = Qty x Harga beli. Nilai ini juga masuk ke laporan keuangan sebagai pengeluaran pembelian.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                        <button type="button" onclick="closePengeluaranModal()" class="px-5 py-3 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900">Batal</button>
                        <button type="submit" class="rounded-xl bg-gray-900 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white transition hover:bg-black">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPengeluaranModal() {
            document.getElementById('pengeluaranModal').style.display = 'flex';
        }

        function closePengeluaranModal() {
            document.getElementById('pengeluaranModal').style.display = 'none';
        }

        function pengeluaranForm(refillOptions, peralatanOptions) {
            return {
                refillOptions,
                peralatanOptions,
                jenisPengeluaran: @js(old('jenis_pengeluaran', '')),
                selectedRefillId: @js(old('jenis_refill_id', '')),
                selectedPeralatanId: @js(old('peralatan_id', '')),
                qty: Number(@js(old('qty', 0))) || 0,
                hargaBeli: Number(@js(old('harga_beli', 0))) || 0,
                jenisWatcherReady: false,
                init() {
                    if (@js($errors->any())) {
                        openPengeluaranModal();
                    }

                    this.$watch('selectedRefillId', () => {
                        if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}' && this.currentItem) {
                            this.hargaBeli = this.currentItem.harga ?? this.hargaBeli;
                        }
                    });

                    this.$watch('selectedPeralatanId', () => {
                        if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}' && this.currentItem) {
                            this.hargaBeli = this.currentItem.harga ?? this.hargaBeli;
                        }
                    });

                    this.$watch('jenisPengeluaran', (value) => {
                        if (!this.jenisWatcherReady) {
                            this.jenisWatcherReady = true;
                            return;
                        }

                        this.qty = 0;
                        this.hargaBeli = 0;
                        this.selectedRefillId = '';
                        this.selectedPeralatanId = '';
                        if (value === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}') {
                            this.qty = 1;
                        }
                    });
                },
                get currentItem() {
                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}') {
                        return this.refillOptions.find(item => String(item.id) === String(this.selectedRefillId)) ?? null;
                    }

                    if (this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_PERALATAN }}') {
                        return this.peralatanOptions.find(item => String(item.id) === String(this.selectedPeralatanId)) ?? null;
                    }

                    return null;
                },
                get currentItemName() {
                    return this.currentItem ? (this.currentItem.label ?? this.currentItem.nama) : '';
                },
                get currentUnit() {
                    if (!this.currentItem) {
                        return '';
                    }

                    return this.jenisPengeluaran === '{{ \App\Models\Pengeluaran::JENIS_PEMBELIAN_REFILL }}'
                        ? this.currentItem.satuan
                        : 'Unit';
                },
                get total() {
                    return (Number(this.qty) || 0) * (Number(this.hargaBeli) || 0);
                },
                get currentStockText() {
                    if (!this.currentItem) {
                        return '-';
                    }

                    return `${this.formatQty(this.currentItem.stok)} ${this.currentUnit}`;
                },
                get stockAfterText() {
                    if (!this.currentItem) {
                        return '-';
                    }

                    const hasil = (Number(this.currentItem.stok) || 0) + (Number(this.qty) || 0);
                    return `${this.formatQty(hasil)} ${this.currentUnit}`;
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
