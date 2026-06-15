<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Data Pesanan</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola pesanan produk APAR dari pelanggan online maupun offline.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-pesanan-modal'))" class="px-5 py-3 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-bold rounded-xl transition shadow-sm text-xs flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Input Pesanan Offline
            </button>
        </div>
    </x-slot>

    @php
        $pesananBulanIni = $pesanans->filter(fn ($pesanan) => $pesanan->tanggal->isSameMonth(now()))->count();
        $nilaiPesanan = $pesanans->sum('total');
        $totalItem = $pesanans->sum(fn ($pesanan) => $pesanan->details->sum('jumlah'));
        $pesananOnline = $pesanans->filter(fn ($p) => $p->sumber_pesanan === 'website')->count();
        $pesananOffline = $pesanans->filter(fn ($p) => in_array((string) $p->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin'], true))->count();
        $finishedStatuses = ['selesai', 'selesai final', 'ditolak'];
        $pesananRiwayat = $pesanans->getCollection()->filter(fn ($pesanan) => in_array((string) $pesanan->status, $finishedStatuses, true))->values();
        $pesananAktif = $pesanans->getCollection()->reject(fn ($pesanan) => in_array((string) $pesanan->status, $finishedStatuses, true))->values();
        $summary = [
            'totalPesanan' => $pesanans->count(),
            'totalItem' => $totalItem,
            'nilaiPesanan' => $nilaiPesanan,
            'pesananOnline' => $pesananOnline,
            'pesananOffline' => $pesananOffline,
        ];
        $actionButtonBase = 'inline-flex items-center justify-center px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition shadow-sm';
        $actionButtonNeutral = $actionButtonBase . ' border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
        $actionButtonPrimary = $actionButtonBase . ' border-red-600 bg-red-600 text-white hover:bg-red-700 hover:border-red-700';
        $actionButtonProof = $actionButtonBase . ' min-w-[92px] border-transparent bg-blue-600 text-white hover:bg-blue-700';
        $actionButtonProofStyle = 'background-color:#2563eb;border-color:#2563eb;color:#fff;';
        $actionButtonDanger = $actionButtonBase . ' border-red-200 bg-white text-red-600 hover:bg-red-50';
        $actionButtonDisabled = $actionButtonBase . ' border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed';
        $purchasePriceModalState = [
            'order_id' => (int) old('purchase_price_order_id', 0),
            'harga_final' => (string) old('harga_final', ''),
            'catatan_admin' => (string) old('catatan_admin', ''),
            'errors' => [
                'harga_final' => $errors->get('harga_final'),
                'catatan_admin' => $errors->get('catatan_admin'),
            ],
        ];
    @endphp

    <div
        class="space-y-8"
        x-data="pesananForm(
            @js($produkCatalog),
            @js($prefillItems),
            @js(old('pelanggan_id', $processPelangganId)),
            {{ (((old('tipe') === 'produk') && $errors->any()) || (!empty($processPelangganId) && empty($autoOpenNegoId))) ? 'true' : 'false' }},
            @js([
                'pelanggan_mode' => old('pelanggan_mode', 'existing'),
                'metode_pengiriman' => old('metode_pengiriman', 'pickup'),
                'ongkir' => old('ongkir', 0),
            ]),
            @js($pelanggans->map(fn($p) => [
                'id' => (string) $p->id,
                'no_wa' => $p->no_wa,
                'email' => $p->user?->email,
                'alamat_lengkap' => $p->alamat
            ])->values())
        )"
        @open-pesanan-modal.window="openModal = true"
    >
        <div id="pesanan-summary-cards">
            @include('admin.pesanan.partials.summary-cards', ['summary' => $summary])
        </div>

        @if(session('wa_url'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="text-sm font-black text-emerald-800">{{ session('wa_title', 'Pesan WhatsApp siap dikirim.') }}</p>
                    <p class="text-xs font-semibold text-emerald-700 mt-1">{{ session('wa_description', 'Kirim pesan ini ke pelanggan untuk tindak lanjut pesanan.') }}</p>
                </div>
                <a href="{{ session('wa_url') }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition">
                    {{ session('wa_button', 'Buka WhatsApp') }}
                </a>
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Data Pesanan dari Pelanggan</h3>
                <p class="mt-1 text-xs font-semibold text-gray-500">Permintaan pembelian APAR dari pelanggan online maupun input offline admin.</p>
            </div>
            <div class="responsive-table-wrap">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Item Pesanan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pesanan-active-rows" class="divide-y divide-gray-50">
                        @include('admin.pesanan.partials.active-rows', ['pesananAktif' => $pesananAktif])
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Riwayat Data Pesanan</h3>
                <p class="mt-1 text-xs font-semibold text-gray-500">Log pesanan produk APAR yang sudah selesai atau ditutup.</p>
            </div>
            <div class="responsive-table-wrap">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Item Pesanan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pesanan-history-rows" class="divide-y divide-gray-50">
                        @include('admin.pesanan.partials.history-rows', ['pesananRiwayat' => $pesananRiwayat])
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL DETAIL PESANAN --}}
        <div id="pesanan-detail-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" onclick="closePesananDetailModal()"></div>
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-gray-100 z-10">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Detail Data Pesanan</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5" id="pesanan-detail-subtitle"></p>
                    </div>
                    <button onclick="closePesananDetailModal()" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5" id="pesanan-detail-content"></div>
            </div>
        </div>

        <div id="pesanan-proof-modal" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/70 backdrop-blur-sm" onclick="closePesananProofModal()"></div>
            <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900" id="pesanan-proof-title">Bukti Transfer</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">Preview bukti pembayaran pelanggan</p>
                    </div>
                    <button type="button" onclick="closePesananProofModal()" class="rounded-xl bg-gray-50 p-2 text-gray-400 transition hover:text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="pesanan-proof-body" class="max-h-[78vh] overflow-auto bg-gray-50 p-6"></div>
            </div>
        </div>

        <div x-show="openModal" x-cloak class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto p-3 sm:items-center sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openModal = false"></div>
            <div
                x-show="openModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="app-modal-shell relative my-3 max-w-5xl sm:my-6"
            >
                <div class="app-modal-header flex items-start justify-between gap-4 bg-gradient-to-r from-slate-800 to-slate-700 px-5 py-4 sm:items-center sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-600/30 border border-red-500/30 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tight leading-tight">Input Pesanan Offline</h3>
                            <p class="text-sm text-white/70 font-medium mt-0.5">Form ini digunakan untuk mencatat pembelian APAR dari pelanggan yang datang langsung ke toko.</p>
                        </div>
                    </div>
                    <button type="button" @click="openModal = false" class="w-10 h-10 rounded-2xl bg-white/10 text-white/60 hover:text-white hover:bg-white/20 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="app-modal-body flex-1 p-5 sm:p-6 lg:p-8">
                    <form action="{{ route('admin.pesanan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipe" value="produk">
                        @if($errors->any())
                            <div class="mb-8 rounded-2xl border border-red-100 bg-red-50 px-6 py-5">
                                <p class="text-sm font-black text-red-700">{{ $errors->first() }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
                            <div class="xl:col-span-2 space-y-8">
                                <div class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                                        <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 font-black text-sm flex items-center justify-center shrink-0">1</span>
                                        <h4 class="font-black text-gray-900 uppercase tracking-wider text-xs">Data Pelanggan</h4>
                                    </div>
                                    <div class="space-y-4">
                                        <label for="pelanggan_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pilih Pelanggan <span class="text-red-500">*</span></label>
                                        <select name="pelanggan_id" id="pelanggan_id" required x-model="selectedPelangganId" @change="syncPelangganProfile" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm">
                                            <option value="">-- Pilih Pelanggan Terdaftar --</option>
                                            @foreach($pelanggans as $pelanggan)
                                                <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }} ({{ $pelanggan->no_wa }})</option>
                                            @endforeach
                                        </select>
                                        <p class="text-[10px] font-semibold leading-relaxed text-slate-500">
                                            Pelanggan offline wajib dipilih dari akun role pelanggan. Jika belum ada, buat akun pelanggan terlebih dahulu melalui
                                            <a href="{{ route('admin.akun.index') }}" class="font-black text-red-700 hover:underline">Manajemen Akun</a>.
                                        </p>
                                        <x-input-error :messages="$errors->get('pelanggan_id')" class="mt-2" />
                                    </div>

                                    <div x-show="selectedPelangganId" x-cloak class="mt-4 rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 p-5 space-y-3">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-200/50 pb-2">Profil Pelanggan</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="font-bold text-gray-500 text-xs mb-1">WhatsApp</p>
                                                <p class="font-black text-gray-900" x-text="selectedPelangganInfo.no_wa || '-'"></p>
                                            </div>

                                            <div class="md:col-span-2">
                                                <p class="font-bold text-gray-500 text-xs mb-1">Alamat Lengkap</p>
                                                <p class="font-bold text-gray-900 leading-relaxed" x-text="selectedPelangganInfo.alamat_lengkap || '-'"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 font-black text-sm flex items-center justify-center shrink-0">2</span>
                                            <h4 class="font-black text-gray-900 uppercase tracking-wider text-xs">Pilih Produk &amp; Jumlah Unit</h4>
                                        </div>
                                        <button type="button" @click="addRow()" class="px-4 py-2.5 bg-red-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-800 transition">
                                            Tambah Item Produk
                                        </button>
                                    </div>

                                    <div class="space-y-6">
                                        <template x-for="(row, index) in rows" :key="row.uid">
                                            <div x-transition class="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 space-y-4">
                                                <div class="flex items-center justify-between gap-4 border-b border-gray-200/50 pb-2">
                                                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-wider" x-text="'Item #' + (index + 1)"></span>
                                                    <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="w-8 h-8 rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>

                                                <input type="hidden" :name="'items[' + index + '][produk_id]'" x-model="row.produk_id">

                                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis APAR <span class="text-red-500">*</span></label>
                                                        <select x-model="row.jenis" @change="syncRow(index)" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20 text-sm">
                                                            <option value="">-- Pilih Jenis --</option>
                                                            <template x-for="jenis in jenisOptions()" :key="jenis">
                                                                <option :value="jenis" x-text="jenis"></option>
                                                            </template>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kapasitas <span class="text-red-500">*</span></label>
                                                        <select :name="'items[' + index + '][kapasitas]'" x-model="row.kapasitas" @change="syncRow(index)" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20 text-sm">
                                                            <option value="">-- Pilih Kapasitas --</option>
                                                            <template x-for="kapasitas in capacityOptions(row.jenis)" :key="kapasitas">
                                                                <option :value="kapasitas" x-text="kapasitas"></option>
                                                            </template>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Merek <span class="text-red-500">*</span></label>
                                                        <select :name="'items[' + index + '][merek]'" x-model="row.merek" @change="syncRow(index)" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20 text-sm">
                                                            <option value="">-- Pilih Merek --</option>
                                                            <template x-for="merek in brandOptions(row.jenis, row.kapasitas)" :key="merek">
                                                                <option :value="merek" x-text="merek"></option>
                                                            </template>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Produk <span class="text-red-500">*</span></label>
                                                        <select x-model="row.produk_id" @change="syncRowFromProduct(index)" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20 text-sm">
                                                            <option value="">-- Pilih Produk --</option>
                                                            <template x-for="product in productOptions(row.jenis, row.kapasitas, row.merek)" :key="product.id">
                                                                <option :value="String(product.id)" x-text="productLabel(product)"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah <span class="text-red-500">*</span></label>
                                                        <input :name="'items[' + index + '][jumlah]'" type="number" min="1" :max="row.stok || null" x-model.number="row.jumlah" @input="syncTotals()" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20 text-sm">
                                                        <p x-show="hasStockIssue(row)" x-cloak class="mt-2 text-[10px] font-bold text-red-600">Jumlah melebihi stok tersedia.</p>
                                                    </div>
                                                    <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-5 py-4">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk Terpilih</p>
                                                        <p class="mt-2 text-sm font-black text-gray-900" x-text="row.nama || 'Belum ada produk yang dipilih'"></p>
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-widest" x-text="row.jenis || 'Jenis -'"></span>
                                                            <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-widest" x-text="row.kapasitas || 'Ukuran -'"></span>
                                                            <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-widest" x-text="row.merek || 'Merek -'"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-between pt-3 border-t border-gray-200/50 text-xs">
                                                    <span class="font-bold text-gray-400">Harga Satuan: <span class="text-gray-700 ml-1" x-text="currency(row.harga)"></span></span>
                                                    <span class="font-bold text-gray-400">Stok Tersedia: <span class="text-gray-700 ml-1" x-text="(row.stok ?? 0) + ' unit'"></span></span>
                                                    <span class="font-black text-red-700">Subtotal: <span class="ml-1" x-text="currency(row.subtotal)"></span></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                                        <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 font-black text-sm flex items-center justify-center shrink-0">3</span>
                                        <h4 class="font-black text-gray-900 uppercase tracking-wider text-xs">Tanggal & Catatan</h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="tanggal" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Pesanan</label>
                                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-900 text-sm">
                                        </div>
                                        <div>
                                            <label for="catatan_admin" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Catatan <span class="text-gray-300">(Opsional)</span></label>
                                            <input type="text" name="catatan_admin" id="catatan_admin" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl font-bold text-gray-900 text-sm" placeholder="Catatan tambahan..." value="{{ old('catatan_admin') }}">
                                        </div>
                                    </div>
                                    <div class="mt-2 px-4 py-3 bg-emerald-50 rounded-xl border border-emerald-200">
                                        <p class="text-xs font-bold text-emerald-800">Pesanan offline langsung dianggap <span class="font-black">LUNAS</span> dan stok otomatis berkurang.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="sticky-summary-xl rounded-3xl border border-gray-100 bg-gray-50 p-5 shadow-sm sm:p-6">
                                    <div class="flex items-center gap-2 border-b border-gray-200/60 pb-3">
                                        <span class="w-6 h-6 rounded-md bg-red-50 text-red-700 font-black text-xs flex items-center justify-center shrink-0">3</span>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Invoice</p>
                                    </div>
                                    <div class="mt-6 space-y-4">
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Tipe Pesanan</span>
                                            <span class="font-bold text-red-700">Pesanan Offline</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Total Varian</span>
                                            <span class="font-bold text-gray-900" x-text="rows.length + ' item'"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Total Unit</span>
                                            <span class="font-bold text-gray-900" x-text="totalUnit + ' unit'"></span>
                                        </div>
                                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                                            <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Total Akhir</span>
                                            <span class="text-xl font-black text-red-700" x-text="currency(grandTotal)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="app-modal-footer mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                            <button type="button" @click="openModal = false" class="w-full px-8 py-4 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900 sm:w-auto">Batal</button>
                            <button type="submit" class="w-full rounded-2xl bg-red-700 px-10 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/30 transition hover:bg-red-800 sm:w-auto">
                                Simpan Pesanan Offline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @once
        <script>
            function pesananForm(catalog, oldItems, initialPelangganId, initialOpen, initialForm, pelanggansData) {
                return {
                    search: '',
                    viewMode: 'all',
                    openModal: initialOpen,
                    catalog: catalog,
                    pelanggansData: pelanggansData || [],
                    selectedPelangganId: initialPelangganId ? String(initialPelangganId) : '',
                    selectedPelangganInfo: {},
                    rows: [],
                    grandTotal: 0,
                    totalUnit: 0,
                    init() {
                        this.rows = (oldItems.length ? oldItems : [{}]).map((item, index) => this.makeRow(item, index));
                        this.rows.forEach((row, index) => this.syncRow(index, true));
                        if (this.selectedPelangganId) this.syncPelangganProfile();
                        this.syncTotals();
                    },
                    syncPelangganProfile() {
                        const info = this.pelanggansData.find(p => p.id === this.selectedPelangganId);
                        this.selectedPelangganInfo = info || {};
                    },
                    makeRow(item, index) {
                        const variant = this.findProductVariant(item.produk_id);
                        return {
                            uid: Date.now() + index + Math.floor(Math.random() * 1000),
                            jenis: variant?.jenis ?? '',
                            nama: variant?.nama ?? '',
                            produk_id: item.produk_id ? String(item.produk_id) : '',
                            kapasitas: item.kapasitas ?? variant?.kapasitas ?? '',
                            merek: item.merek ?? variant?.merek ?? '',
                            jumlah: Number(item.jumlah ?? 1),
                            harga: Number(variant?.harga ?? 0),
                            stok: Number(variant?.stok ?? 0),
                            subtotal: 0,
                        }
                    },
                    findProductVariant(produkId) {
                        return this.catalog.find((item) => Number(item.id) === Number(produkId)) || null;
                    },
                    jenisOptions() {
                        return [...new Set(this.catalog.map((item) => item.jenis).filter(Boolean))];
                    },
                    capacityOptions(jenis) {
                        return [...new Set(this.catalog.filter((item) => item.jenis === jenis).map((item) => item.kapasitas))];
                    },
                    brandOptions(jenis, kapasitas) {
                        return [...new Set(this.catalog.filter((item) => item.jenis === jenis && item.kapasitas === kapasitas).map((item) => item.merek))];
                    },
                    productOptions(jenis, kapasitas, merek) {
                        return this.catalog.filter((item) => item.jenis === jenis && item.kapasitas === kapasitas && item.merek === merek);
                    },
                    productLabel(product) {
                        return `${product.jenis || '-'} - ${product.kapasitas || '-'} - ${product.merek || '-'} - ${this.currency(product.harga)} - Stok ${Number(product.stok || 0)}`;
                    },
                    syncRow(index, preserveProduct = false) {
                        const row = this.rows[index];
                        const kapasitasList = this.capacityOptions(row.jenis);
                        if (kapasitasList.length && !kapasitasList.includes(row.kapasitas)) {
                            row.kapasitas = kapasitasList[0];
                        } else if (!kapasitasList.length) {
                            row.kapasitas = '';
                        }
                        const merekList = this.brandOptions(row.jenis, row.kapasitas);
                        if (merekList.length && !merekList.includes(row.merek)) {
                            row.merek = merekList[0];
                        } else if (!merekList.length) {
                            row.merek = '';
                        }
                        const productList = this.productOptions(row.jenis, row.kapasitas, row.merek);
                        if (!preserveProduct || !productList.some((item) => String(item.id) === String(row.produk_id))) {
                            row.produk_id = productList.length ? String(productList[0].id) : '';
                        }
                        this.syncRowFromProduct(index);
                    },
                    syncRowFromProduct(index) {
                        const row = this.rows[index];
                        const variant = this.findProductVariant(row.produk_id);
                        row.nama = variant ? variant.nama : '';
                        row.harga = variant ? Number(variant.harga) : 0;
                        row.stok = variant ? Number(variant.stok || 0) : 0;
                        row.subtotal = row.harga * Number(row.jumlah || 0);
                        this.syncTotals();
                    },
                    hasStockIssue(row) {
                        return !!row.produk_id && Number(row.jumlah || 0) > Number(row.stok || 0);
                    },
                    syncTotals() {
                        this.rows.forEach((row) => {
                            row.subtotal = Number(row.harga) * Number(row.jumlah || 0);
                        });
                        this.grandTotal = this.rows.reduce((total, row) => total + Number(row.subtotal || 0), 0);
                        this.totalUnit = this.rows.reduce((total, row) => total + Number(row.jumlah || 0), 0);
                    },
                    addRow() {
                        this.rows.push(this.makeRow({}, this.rows.length));
                    },
                    removeRow(index) {
                        if (this.rows.length === 1) return;
                        this.rows.splice(index, 1);
                        this.syncTotals();
                    },
                    currency(value) {
                        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
                    }
                }
            }

            window.pesananDetailData = @json($pesananDetailData);
            const purchasePriceModalState = @json($purchasePriceModalState);
            const purchasePriceModalToken = @json(csrf_token());

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function nl2brHtml(value) {
                return escapeHtml(value).replace(/\r\n|\r|\n/g, '<br>');
            }

            function formatRupiahInput(value) {
                const digits = String(value ?? '').replace(/\D+/g, '');
                return digits ? 'Rp ' + Number(digits).toLocaleString('id-ID') : '';
            }

            function purchasePriceStateForOrder(orderId) {
                return Number(purchasePriceModalState.order_id || 0) === Number(orderId)
                    ? purchasePriceModalState
                    : null;
            }

            function attachPurchasePriceInputMask() {
                const input = document.getElementById('purchase-price-final-input');
                if (!input) return;

                const applyFormat = () => {
                    input.value = formatRupiahInput(input.value);
                };

                input.addEventListener('input', applyFormat);
                applyFormat();
            }

            function buildPurchasePriceCardHtml(data) {
                const purchase = data.purchase_price || {};
                if (!purchase.has_request) {
                    return '';
                }

                const modalState = purchasePriceStateForOrder(data.id);
                const hargaFinalValue = modalState
                    ? formatRupiahInput(modalState.harga_final || '')
                    : formatRupiahInput(purchase.final_price || '');
                const catatanAdminValue = modalState
                    ? (modalState.catatan_admin || '')
                    : (purchase.admin_note || '');
                const hargaFinalError = modalState?.errors?.harga_final?.[0] || '';
                const catatanAdminError = modalState?.errors?.catatan_admin?.[0] || '';
                const badgeHtml = purchase.label
                    ? `<span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black ${purchase.badge_classes || 'bg-slate-100 text-slate-700 border border-slate-200'}">${escapeHtml(purchase.label)}</span>`
                    : '';
                const customerNoteHtml = purchase.customer_note
                    ? `
                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-700">Catatan Pelanggan</p>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-amber-900">${nl2brHtml(purchase.customer_note)}</p>
                        </div>
                    `
                    : '';
                const adminNoteHtml = (purchase.admin_note || catatanAdminValue)
                    ? `
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Catatan Admin</p>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-gray-700">${nl2brHtml(purchase.admin_note || catatanAdminValue)}</p>
                        </div>
                    `
                    : '';

                if (purchase.is_pending) {
                    return `
                        <div class="rounded-xl border border-amber-200 bg-white p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-amber-700">Tindak Lanjut Admin</p>
                                    <h4 class="mt-1 text-base font-black text-gray-900">Pengajuan Harga Pembelian</h4>
                                    <p class="mt-2 text-xs font-semibold leading-relaxed text-gray-500">Harga pengajuan pelanggan ditampilkan sebagai referensi. Sampai disetujui, total pesanan tetap mengikuti harga normal atau promo otomatis yang berjalan.</p>
                                </div>
                                ${badgeHtml}
                            </div>
                            <div class="mt-4 space-y-4">
                                <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Pengajuan Pelanggan</p>
                                    <p class="mt-2 text-lg font-black text-gray-900">${purchase.requested_price ? `Rp ${escapeHtml(purchase.requested_price)}` : '-'}</p>
                                </div>
                                ${customerNoteHtml}
                                <form method="POST" action="${escapeHtml(purchase.acc_url || '#')}" class="space-y-4">
                                    <input type="hidden" name="_token" value="${escapeHtml(purchasePriceModalToken)}">
                                    <input type="hidden" name="purchase_price_order_id" value="${escapeHtml(data.id)}">
                                    <div>
                                        <label for="purchase-price-final-input" class="block text-sm font-bold text-gray-700 mb-2">Harga Final Admin</label>
                                        <input
                                            type="text"
                                            id="purchase-price-final-input"
                                            name="harga_final"
                                            value="${escapeHtml(hargaFinalValue)}"
                                            placeholder="Rp 0"
                                            inputmode="numeric"
                                            autocomplete="off"
                                            class="w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 text-sm font-semibold"
                                        >
                                        ${hargaFinalError
                                            ? `<p class="mt-2 text-sm font-semibold text-red-600">${escapeHtml(hargaFinalError)}</p>`
                                            : ''
                                        }
                                    </div>
                                    <div>
                                        <label for="purchase-price-admin-note" class="block text-sm font-bold text-gray-700 mb-2">Catatan Admin</label>
                                        <textarea
                                            id="purchase-price-admin-note"
                                            name="catatan_admin"
                                            rows="3"
                                            class="w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 text-sm"
                                            placeholder="Opsional. Tambahkan alasan singkat jika diperlukan."
                                        >${escapeHtml(catatanAdminValue)}</textarea>
                                        ${catatanAdminError
                                            ? `<p class="mt-2 text-sm font-semibold text-red-600">${escapeHtml(catatanAdminError)}</p>`
                                            : ''
                                        }
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-600">
                                        <div class="flex items-center justify-between gap-3">
                                            <span>Total pesanan saat ini</span>
                                            <span class="font-black text-gray-900">Rp ${escapeHtml(purchase.normal_total || data.total || '0')}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <button
                                            type="submit"
                                            class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-700 shadow-lg shadow-emerald-600/20"
                                        >
                                            ACC
                                        </button>
                                        <button
                                            type="submit"
                                            formaction="${escapeHtml(purchase.reject_url || '#')}"
                                            formnovalidate
                                            class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-black text-white transition hover:bg-red-700 shadow-lg shadow-red-600/20"
                                        >
                                            Tolak
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;
                }

                const statusSummaryHtml = purchase.is_approved
                    ? `
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800">
                            <p>Total akhir pesanan ini memakai harga final admin. Promo otomatis tetap menjadi pembanding informasi dan tidak dipotong lagi dari harga final.</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                <span>Total pembanding sistem</span>
                                <span class="font-black text-gray-900">Rp ${escapeHtml(purchase.normal_total || data.total || '0')}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-semibold text-emerald-700">
                                <span>Total akhir yang dipakai</span>
                                <span class="font-black">Rp ${escapeHtml(purchase.current_total || data.total || '0')}</span>
                            </div>
                        </div>
                    `
                    : `
                        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700">
                            Pengajuan harga tidak digunakan. Pesanan tetap mengikuti harga normal atau promo otomatis sesuai alur lama.
                        </div>
                    `;

                return `
                    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Riwayat Persetujuan</p>
                                <h4 class="mt-1 text-base font-black text-gray-900">Pengajuan Harga Pembelian</h4>
                            </div>
                            ${badgeHtml}
                        </div>
                        <div class="mt-4 space-y-4">
                            <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                                <div class="flex items-center justify-between gap-3 text-sm font-semibold text-gray-700">
                                    <span>Harga Pengajuan Pelanggan</span>
                                    <span class="font-black text-gray-900">${purchase.requested_price ? `Rp ${escapeHtml(purchase.requested_price)}` : '-'}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-sm font-semibold text-gray-700">
                                    <span>Harga Final Admin</span>
                                    <span class="font-black ${purchase.is_approved ? 'text-emerald-700' : 'text-gray-900'}">${purchase.final_price ? `Rp ${escapeHtml(purchase.final_price)}` : '-'}</span>
                                </div>
                            </div>
                            ${customerNoteHtml}
                            ${adminNoteHtml}
                            ${statusSummaryHtml}
                        </div>
                    </div>
                `;
            }

            function openPesananDetailModal(id) {
                const data = (window.pesananDetailData || []).find((item) => item.id === id);
                if (!data) return;

                document.getElementById('pesanan-detail-subtitle').textContent = data.label + ' - ' + data.tanggal;
                const purchasePriceHtml = buildPurchasePriceCardHtml(data);
                const shouldHidePaymentBadge = data.hide_payment_badge === true;

                const paidBadge = data.is_paid
                    ? '<span class="inline-flex px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase">LUNAS</span>'
                    : '<span class="inline-flex px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black uppercase">BELUM BAYAR</span>';
                const paymentStatusHtml = shouldHidePaymentBadge
                    ? ''
                    : `
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Bayar</p>
                            <div class="mt-1">${paidBadge}</div>
                        </div>
                    `;

                const itemsHtml = data.items.map((item) => `
                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 sm:flex sm:items-start sm:justify-between sm:gap-4">
                        <div>
                            <p class="font-bold text-gray-900 text-sm">${escapeHtml(item.nama)}</p>
                            <p class="mt-1 text-xs font-semibold text-gray-500">${escapeHtml(item.jenis)} - ${escapeHtml(item.kapasitas)} - ${escapeHtml(item.merek)}</p>
                        </div>
                        <div class="mt-3 text-left sm:mt-0 sm:text-right">
                            <p class="text-xs font-semibold text-gray-500">${escapeHtml(item.jumlah)} unit</p>
                            <p class="mt-1 font-black text-gray-900 text-sm">Rp ${escapeHtml(item.subtotal)}</p>
                        </div>
                    </div>
                `).join('');

                const buktiHtml = data.bukti_pembayaran
                    ? `<a href="/storage/${data.bukti_pembayaran.replace('storage/', '')}" target="_blank" class="block">
                        <img src="/storage/${data.bukti_pembayaran.replace('storage/', '')}" alt="Bukti pembayaran" class="w-full max-h-40 object-contain rounded-xl border border-gray-200 bg-white">
                       </a>
                       <p class="text-[10px] text-emerald-700 font-bold mt-2 text-center">Klik untuk melihat ukuran penuh</p>`
                    : `<p class="text-sm text-gray-500 font-medium text-center py-4">Belum ada bukti pembayaran</p>`;

                document.getElementById('pesanan-detail-content').innerHTML = `
                    <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pelanggan</p>
                            <p class="font-bold text-gray-900">${escapeHtml(data.pelanggan)}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nomor Telepon</p>
                            <p class="font-bold text-gray-900">${escapeHtml(data.no_wa)}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Alamat</p>
                            <p class="font-semibold text-gray-700 text-sm">${escapeHtml(data.alamat)}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Sumber</p>
                            <p class="font-black text-slate-900">${escapeHtml(data.sumber)}</p>
                        </div>
                        ${paymentStatusHtml}
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Metode Pengiriman</p>
                            <p class="font-semibold text-gray-900">${escapeHtml(data.metode)}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Bank Tujuan</p>
                            <p class="font-semibold text-gray-900">${escapeHtml(data.bank)}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Pesanan</p>
                            <p class="font-semibold text-gray-900">${escapeHtml(data.status_label)}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Unit</p>
                            <p class="font-bold text-gray-900">${escapeHtml(data.total_unit)} unit</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Teknisi</p>
                            <p class="font-semibold text-gray-900">${escapeHtml(data.teknisi || 'Belum ditugaskan')}</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Daftar Produk</p>
                        <div class="space-y-3">${itemsHtml}</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Ringkasan Pembayaran</p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                <span>Subtotal Produk</span>
                                <span>Rp ${data.subtotal}</span>
                            </div>
                            ${data.diskon ? `
                            <div class="flex items-center justify-between text-xs font-semibold text-green-700">
                                <span>Diskon ${data.diskon_persen}%</span>
                                <span>-Rp ${data.diskon}</span>
                            </div>
                            ` : ''}
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                <span>Ongkir</span>
                                <span>Rp ${data.ongkir}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                                <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Total</span>
                                <span class="text-lg font-black text-red-700">Rp ${data.total}</span>
                            </div>
                        </div>
                    </div>
                    ${purchasePriceHtml}
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Bukti Pembayaran</p>
                        ${buktiHtml}
                    </div>
                    <div class="flex justify-center">
                        <button type="button" onclick="closePesananDetailModal()" class="px-8 py-3 bg-gray-200 text-gray-700 font-black text-xs uppercase rounded-xl hover:bg-gray-300 transition">Tutup</button>
                    </div>
                `;

                attachPurchasePriceInputMask();
                document.getElementById('pesanan-detail-modal').classList.remove('hidden');
            }

            function closePesananDetailModal() {
                document.getElementById('pesanan-detail-modal').classList.add('hidden');
                document.getElementById('pesanan-detail-content').innerHTML = '';
            }

            function openPesananProofModal(url, meta = {}) {
                const modal = document.getElementById('pesanan-proof-modal');
                const body = document.getElementById('pesanan-proof-body');
                const heading = document.getElementById('pesanan-proof-title');
                const isPdf = /\.pdf($|\?)/i.test(String(url || ''));
                const infoHtml = `
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4">
                        <h4 class="text-sm font-black text-gray-900">Bukti Transfer</h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pelanggan</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">${meta.customer || '-'}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Tanggal Transaksi</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">${meta.date || '-'}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Jenis Transaksi</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">${meta.type || 'Pesanan'}</p>
                            </div>
                        </div>
                    </div>
                `;

                heading.textContent = 'Bukti Transfer';
                if (!url) {
                    body.innerHTML = `${infoHtml}
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-700">
                            Bukti transfer belum tersedia atau file tidak ditemukan.
                        </div>`;
                } else {
                    body.innerHTML = `${infoHtml}
                        ${isPdf
                            ? `<iframe src="${url}" class="h-[70vh] w-full rounded-2xl border border-gray-200 bg-white" title="Preview bukti pembayaran"></iframe>`
                            : `<img src="${url}" alt="Preview bukti pembayaran" class="mx-auto max-h-[70vh] rounded-2xl border border-gray-200 bg-white object-contain">`
                        }`;
                }

                modal.classList.remove('hidden');
            }

            function closePesananProofModal() {
                document.getElementById('pesanan-proof-modal').classList.add('hidden');
                document.getElementById('pesanan-proof-body').innerHTML = '';
            }

            if (Number(purchasePriceModalState.order_id || 0) > 0) {
                window.addEventListener('load', () => {
                    openPesananDetailModal(Number(purchasePriceModalState.order_id));
                });
            }
        </script>
    @endonce

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.createPollingUpdater({
                    url: @js(route('admin.realtime.pesanan')),
                    interval: 10000,
                    onSuccess(payload) {
                        const summary = document.getElementById('pesanan-summary-cards');
                        const activeRows = document.getElementById('pesanan-active-rows');
                        const historyRows = document.getElementById('pesanan-history-rows');

                        if (summary && typeof payload.summary_html === 'string') {
                            summary.innerHTML = payload.summary_html;
                        }
                        if (activeRows && typeof payload.active_rows_html === 'string') {
                            activeRows.innerHTML = payload.active_rows_html;
                        }
                        if (historyRows && typeof payload.history_rows_html === 'string') {
                            historyRows.innerHTML = payload.history_rows_html;
                        }
                        if (Array.isArray(payload.detail_data)) {
                            window.pesananDetailData = payload.detail_data;
                        }
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
