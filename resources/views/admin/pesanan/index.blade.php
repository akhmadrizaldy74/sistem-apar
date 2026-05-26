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
        $closedPesananStatuses = ['selesai', 'selesai final', 'ditolak'];
        $pesananRiwayat = $pesanans->getCollection()->filter(fn ($pesanan) => in_array((string) $pesanan->status, $closedPesananStatuses, true))->values();
        $pesananAktif = $pesanans->getCollection()->reject(fn ($pesanan) => in_array((string) $pesanan->status, $closedPesananStatuses, true))->values();
        $actionButtonBase = 'inline-flex items-center justify-center px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition shadow-sm';
        $actionButtonNeutral = $actionButtonBase . ' border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
        $actionButtonPrimary = $actionButtonBase . ' border-red-600 bg-red-600 text-white hover:bg-red-700 hover:border-red-700';
        $actionButtonProof = $actionButtonBase . ' border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100';
        $actionButtonDanger = $actionButtonBase . ' border-red-200 bg-white text-red-600 hover:bg-red-50';
        $actionButtonDisabled = $actionButtonBase . ' border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed';
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
                'new_pelanggan_nama' => old('new_pelanggan_nama'),
                'new_pelanggan_perusahaan' => old('new_pelanggan_perusahaan'),
                'new_pelanggan_no_wa' => old('new_pelanggan_no_wa'),
                'new_pelanggan_alamat_maps' => old('new_pelanggan_alamat_maps'),
                'new_pelanggan_alamat_detail' => old('new_pelanggan_alamat_detail'),
                'new_pelanggan_alamat_lat' => old('new_pelanggan_alamat_lat'),
                'new_pelanggan_alamat_lng' => old('new_pelanggan_alamat_lng'),
            ])
        )"
        @open-pesanan-modal.window="openModal = true"
    >
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pesanan</p>
                <p class="text-4xl font-black text-gray-900">{{ $pesanans->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Item Terjual</p>
                <p class="text-4xl font-black text-emerald-700">{{ $totalItem }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nilai Pesanan</p>
                <p class="text-4xl font-black text-red-700">Rp {{ number_format($nilaiPesanan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Online / Offline</p>
                <div class="flex items-end gap-3">
                    <div>
                        <p class="text-4xl font-black text-amber-700 leading-none">{{ $pesananOnline }}</p>
                        <p class="mt-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Online</p>
                    </div>
                    <span class="pb-1 text-2xl font-black text-gray-300">/</span>
                    <div>
                        <p class="text-4xl font-black text-slate-700 leading-none">{{ $pesananOffline }}</p>
                        <p class="mt-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Offline</p>
                    </div>
                </div>
            </div>
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
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pesananAktif as $pesanan)
                            @php
                                $pricingSummary = $pesanan->pricingSummary();
                                $s = $pesanan->status;
                                $isOffline = in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin'], true);
                                $hasProof = !empty($pesanan->bukti_pembayaran);
                                $canAssign = $pesanan->isPaymentConfirmed() && !$pesanan->teknisi_id;

                                $statusBadge = match(true) {
                                    $s === 'selesai final' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
                                    $s === 'dikonfirmasi admin' => ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI'],
                                    $s === 'selesai oleh teknisi' => ['bg-emerald-50 text-emerald-700', 'SELESAI OLEH TEKNISI'],
                                    $s === 'dikerjakan teknisi' => ['bg-indigo-50 text-indigo-700', 'DIPROSES'],
                                    $s === 'ditugaskan ke teknisi' => ['bg-purple-50 text-purple-700', 'DITUGASKAN'],
                                    $s === 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI'],
                                    $s === 'ditolak' => ['bg-red-50 text-red-700', 'DITOLAK'],
                                    $s === 'diproses' && $hasProof => ['bg-emerald-50 text-emerald-700', 'DIPROSES'],
                                    $s === 'diproses' => ['bg-blue-50 text-blue-700', 'DIPROSES'],
                                    $s === 'pending' && $hasProof => ['bg-emerald-50 text-emerald-700', 'DIPROSES'],
                                    $s === 'pending' => ['bg-amber-50 text-amber-700', 'MENUNGGU'],
                                    default => ['bg-gray-50 text-gray-700', strtoupper($s)],
                                };

                                $firstProduk = $pesanan->details->first();
                                $firstProdukNama = $firstProduk?->produk?->nama ?? 'Pesanan Produk';
                                if ($pesanan->details->count() > 1) {
                                    $firstProdukNama .= ' +' . ($pesanan->details->count() - 1) . ' lainnya';
                                }
                                $itemCount = $pesanan->details->count();
                                $unitCount = $pesanan->details->sum('jumlah');
                            @endphp
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <p class="text-xs font-bold text-gray-900">{{ $pesanan->displayTransactionDateTime() }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $pesanan->transactionDisplayName() }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900">{{ $pesanan->pelanggan?->nama ?? '-' }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $pesanan->pelanggan?->no_wa ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest">Pesanan Produk</span>
                                        <span class="inline-flex px-3 py-1 rounded-full {{ $isOffline ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600' }} text-[10px] font-black uppercase tracking-widest">{{ $isOffline ? 'Offline' : 'Online' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-black text-gray-900 max-w-[220px] leading-6">{{ $firstProdukNama }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $itemCount }} item - {{ $unitCount }} unit</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 leading-none">Rp</p>
                                    <p class="mt-2 text-[1.75rem] font-black text-gray-900 leading-none">{{ number_format((float) $pricingSummary['totalPembayaran'], 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusBadge[0] }}">
                                        {{ $statusBadge[1] }}
                                    </span>
                                    <p class="mt-2 text-[10px] font-bold {{ $pesanan->isPaymentConfirmed() ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ $pesanan->isPaymentConfirmed() ? 'Lunas' : 'Belum Bayar' }}
                                    </p>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!empty($pesanan->bukti_pembayaran))
                                            <button type="button" onclick='openPesananProofModal(@js(asset("storage/" . ltrim($pesanan->bukti_pembayaran, "/"))), @js("Bukti TF - " . $pesanan->transactionDisplayName()))' class="{{ $actionButtonProof }}">
                                                Bukti TF
                                            </button>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}">
                                                Bukti TF
                                            </button>
                                        @endif
                                        @if($canAssign)
                                            <form action="{{ route('admin.pesanan.assign-teknisi', $pesanan) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonPrimary }}">Assign Teknisi</button>
                                            </form>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}">
                                                Assign Teknisi
                                            </button>
                                        @endif
                                        <button type="button" onclick="openPesananDetailModal({{ $pesanan->id }})" class="{{ $actionButtonNeutral }}">
                                            Detail
                                        </button>
                                        <a href="{{ route('invoice.show', $pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                            Lihat Invoice
                                        </a>
                                        @if($pesanan->status !== 'selesai' && $pesanan->status !== 'selesai final')
                                            <form action="{{ route('admin.pesanan.destroy', $pesanan) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Yakin ingin menghapus pesanan ini?')" class="{{ $actionButtonDanger }}">Hapus</button>
                                            </form>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}" title="Hapus">Hapus</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data pesanan aktif dari pelanggan.</td>
                            </tr>
                        @endforelse
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
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pesananRiwayat as $pesanan)
                            @php
                                $pricingSummary = $pesanan->pricingSummary();
                                $s = $pesanan->status;
                                $isOffline = in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin'], true);
                                $statusBadge = match(true) {
                                    $s === 'selesai final' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
                                    $s === 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI'],
                                    $s === 'ditolak' => ['bg-red-50 text-red-700', 'DITOLAK'],
                                    default => ['bg-gray-50 text-gray-700', strtoupper($s)],
                                };
                                $firstProduk = $pesanan->details->first();
                                $firstProdukNama = $firstProduk?->produk?->nama ?? 'Pesanan Produk';
                                if ($pesanan->details->count() > 1) {
                                    $firstProdukNama .= ' +' . ($pesanan->details->count() - 1) . ' lainnya';
                                }
                                $itemCount = $pesanan->details->count();
                                $unitCount = $pesanan->details->sum('jumlah');
                            @endphp
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <p class="text-xs font-bold text-gray-900">{{ $pesanan->displayTransactionDateTime() }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $pesanan->transactionDisplayName() }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $pesanan->pelanggan?->nama ?? '-' }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $pesanan->pelanggan?->no_wa ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest">Pesanan Produk</span>
                                        <span class="inline-flex px-3 py-1 rounded-full {{ $isOffline ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600' }} text-[10px] font-black uppercase tracking-widest">{{ $isOffline ? 'Offline' : 'Online' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-black text-gray-900 max-w-[220px] leading-6">{{ $firstProdukNama }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $itemCount }} item - {{ $unitCount }} unit</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900 leading-none">Rp</p>
                                    <p class="mt-2 text-[1.75rem] font-black text-gray-900 leading-none">{{ number_format((float) $pricingSummary['totalPembayaran'], 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusBadge[0] }}">
                                        {{ $statusBadge[1] }}
                                    </span>
                                    <p class="mt-2 text-[10px] font-bold {{ $pesanan->isPaymentConfirmed() ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ $pesanan->isPaymentConfirmed() ? 'Lunas' : 'Belum Bayar' }}
                                    </p>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!empty($pesanan->bukti_pembayaran))
                                            <button type="button" onclick='openPesananProofModal(@js(asset("storage/" . ltrim($pesanan->bukti_pembayaran, "/"))), @js("Bukti TF - " . $pesanan->transactionDisplayName()))' class="{{ $actionButtonProof }}">
                                                Bukti TF
                                            </button>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}">
                                                Bukti TF
                                            </button>
                                        @endif
                                        <button type="button" onclick="openPesananDetailModal({{ $pesanan->id }})" class="{{ $actionButtonNeutral }}">
                                            Detail
                                        </button>
                                        <a href="{{ route('invoice.show', $pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                            Lihat Invoice
                                        </a>
                                        <form action="{{ route('admin.pesanan.destroy', $pesanan) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Yakin ingin menghapus riwayat pesanan ini?')" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada riwayat pesanan yang selesai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FLASH TOAST --}}
        @if(session('success'))
            <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4500)"
                class="fixed bottom-6 right-6 z-[200] px-6 py-4 bg-emerald-600 text-white font-bold rounded-2xl shadow-2xl flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)"
                class="fixed bottom-6 right-6 z-[200] px-6 py-4 bg-red-600 text-white font-bold rounded-2xl shadow-2xl flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

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
                        <h3 class="text-lg font-black text-gray-900" id="pesanan-proof-title">Bukti TF</h3>
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
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="new_pelanggan_nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Pelanggan <span class="text-red-500">*</span></label>
                                            <input type="text" name="new_pelanggan_nama" id="new_pelanggan_nama" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="Contoh: Budi Santoso" value="{{ old('new_pelanggan_nama') }}">
                                            <x-input-error :messages="$errors->get('new_pelanggan_nama')" class="mt-2" />
                                        </div>
                                        <div>
                                            <label for="new_pelanggan_no_wa" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor Telepon <span class="text-red-500">*</span></label>
                                            <input type="text" name="new_pelanggan_no_wa" id="new_pelanggan_no_wa" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="081234567890" value="{{ old('new_pelanggan_no_wa') }}">
                                            <p class="text-[9px] font-semibold text-gray-400 mt-1.5">Jika nomor sudah terdaftar, data pelanggan akan otomatis terhubung.</p>
                                            <x-input-error :messages="$errors->get('new_pelanggan_no_wa')" class="mt-2" />
                                        </div>
                                    </div>
                                    <div>
                                        <label for="new_pelanggan_alamat" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alamat <span class="text-gray-300">(Opsional)</span></label>
                                        <input type="text" name="new_pelanggan_alamat" id="new_pelanggan_alamat" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="Jl. Contoh No.10, Kota" value="{{ old('new_pelanggan_alamat') }}">
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
            function pesananForm(catalog, oldItems, initialPelangganId, initialOpen, initialForm) {
                return {
                    search: '',
                    viewMode: 'all',
                    openModal: initialOpen,
                    catalog: catalog,
                    selectedPelangganId: initialPelangganId ? String(initialPelangganId) : '',
                    rows: [],
                    grandTotal: 0,
                    totalUnit: 0,
                    init() {
                        this.rows = (oldItems.length ? oldItems : [{}]).map((item, index) => this.makeRow(item, index));
                        this.rows.forEach((row, index) => this.syncRow(index, true));
                        this.syncTotals();
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

            const pesananDetailData = @json($pesananDetailData);

            function openPesananDetailModal(id) {
                const data = pesananDetailData.find((item) => item.id === id);
                if (!data) return;

                document.getElementById('pesanan-detail-subtitle').textContent = data.label + ' - ' + data.tanggal;

                const paidBadge = data.is_paid
                    ? '<span class="inline-flex px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase">LUNAS</span>'
                    : '<span class="inline-flex px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black uppercase">BELUM BAYAR</span>';

                const itemsHtml = data.items.map((item) => `
                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                        <div>
                            <p class="font-bold text-gray-900 text-sm">${item.nama}</p>
                            <p class="mt-1 text-xs font-semibold text-gray-500">${item.jenis} - ${item.kapasitas} - ${item.merek}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-gray-500">${item.jumlah} unit</p>
                            <p class="mt-1 font-black text-gray-900 text-sm">Rp ${item.subtotal}</p>
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
                            <p class="font-bold text-gray-900">${data.pelanggan}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nomor Telepon</p>
                            <p class="font-bold text-gray-900">${data.no_wa}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Alamat</p>
                            <p class="font-semibold text-gray-700 text-sm">${data.alamat}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Sumber</p>
                            <p class="font-black text-slate-900">${data.sumber}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Bayar</p>
                            <div class="mt-1">${paidBadge}</div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Metode Pengiriman</p>
                            <p class="font-semibold text-gray-900">${data.metode}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Bank Tujuan</p>
                            <p class="font-semibold text-gray-900">${data.bank}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Pesanan</p>
                            <p class="font-semibold text-gray-900">${data.status_label}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Unit</p>
                            <p class="font-bold text-gray-900">${data.total_unit} unit</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Teknisi</p>
                            <p class="font-semibold text-gray-900">${data.teknisi || 'Belum ditugaskan'}</p>
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
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Bukti Pembayaran</p>
                        ${buktiHtml}
                    </div>
                    <div class="flex justify-center">
                        <button type="button" onclick="closePesananDetailModal()" class="px-8 py-3 bg-gray-200 text-gray-700 font-black text-xs uppercase rounded-xl hover:bg-gray-300 transition">Tutup</button>
                    </div>
                `;

                document.getElementById('pesanan-detail-modal').classList.remove('hidden');
            }

            function closePesananDetailModal() {
                document.getElementById('pesanan-detail-modal').classList.add('hidden');
            }

            function openPesananProofModal(url, title) {
                const modal = document.getElementById('pesanan-proof-modal');
                const body = document.getElementById('pesanan-proof-body');
                const heading = document.getElementById('pesanan-proof-title');
                const isPdf = /\.pdf($|\?)/i.test(String(url || ''));

                heading.textContent = title || 'Bukti TF';
                body.innerHTML = isPdf
                    ? `<iframe src="${url}" class="h-[70vh] w-full rounded-2xl border border-gray-200 bg-white" title="Preview bukti pembayaran"></iframe>`
                    : `<img src="${url}" alt="Preview bukti pembayaran" class="mx-auto max-h-[70vh] rounded-2xl border border-gray-200 bg-white object-contain">`;

                modal.classList.remove('hidden');
            }

            function closePesananProofModal() {
                document.getElementById('pesanan-proof-modal').classList.add('hidden');
                document.getElementById('pesanan-proof-body').innerHTML = '';
            }
        </script>
    @endonce
</x-app-layout>
