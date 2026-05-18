<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Data Pesanan</h2>
                <p class="text-sm text-gray-500 font-medium">Pencatatan pesanan manual dan offline agar proses operasional tetap rapi dan terkontrol.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-pesanan-modal'))" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Input Pesanan
            </button>
        </div>
    </x-slot>

    @php
        $pesananBulanIni = $pesanans->filter(fn ($pesanan) => $pesanan->tanggal->isSameMonth(now()))->count();
        $nilaiPesanan = $pesanans->sum('total');
        $totalItem = $pesanans->sum(fn ($pesanan) => $pesanan->details->sum('jumlah'));
        $antrianNego = $pesanans->filter(fn ($pesanan) =>
            in_array($pesanan->status, ['menunggu', 'menunggu persetujuan']) ||
            (!empty($pesanan->harga_usulan) && $pesanan->status === 'diproses' && empty($pesanan->kode_nego))
        )->count();
        $sudahPunyaKode = $pesanans->filter(fn ($pesanan) => !empty($pesanan->kode_nego))->count();
        $kodeTerpakai = $pesanans->filter(fn ($pesanan) => !empty($pesanan->kode_nego) && (!empty($pesanan->kode_nego_terpakai_at) || !empty($pesanan->bukti_pembayaran)))->count();
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
        x-init="
            init();
            @if(!empty($autoOpenNegoId))
                setTimeout(() => openNegoModal({{ (int) $autoOpenNegoId }}), 180);
            @endif
            @if(old('nego_modal_id'))
                setTimeout(() => openNegoModal({{ (int) old('nego_modal_id') }}), 220);
            @endif
        "
        @open-pesanan-modal.window="openModal = true"
    >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pesanan</p>
                <p class="text-4xl font-black text-gray-900">{{ $pesanans->count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Item Terjual</p>
                <p class="text-4xl font-black text-emerald-600">{{ $totalItem }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nilai Pesanan</p>
                <p class="text-3xl font-black text-red-700">Rp {{ number_format($nilaiPesanan, 0, ',', '.') }}</p>
                <p class="text-xs font-semibold text-gray-500 mt-3">{{ $pesananBulanIni }} transaksi bulan ini</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pesanan Produk</p>
                <p class="text-4xl font-black text-gray-900">{{ $pesanans->count() }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Varian Produk</p>
                <p class="text-4xl font-black text-blue-700">{{ $pesanans->sum(fn ($pesanan) => $pesanan->details->count()) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-amber-50 p-6 rounded-2xl shadow-sm border border-amber-200">
                <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest mb-1">Antrian Negosiasi</p>
                <p class="text-4xl font-black text-amber-700">{{ $antrianNego }}</p>
                <p class="text-xs font-semibold text-amber-800 mt-2">Perlu ditinjau admin (ACC/Tolak)</p>
            </div>
            <div class="bg-blue-50 p-6 rounded-2xl shadow-sm border border-blue-200">
                <p class="text-[10px] font-black text-blue-700 uppercase tracking-widest mb-1">Pesanan Berkode Nego</p>
                <p class="text-4xl font-black text-blue-700">{{ $sudahPunyaKode }}</p>
                <p class="text-xs font-semibold text-blue-800 mt-2">{{ $kodeTerpakai }} kode sudah terpakai</p>
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
            <div class="p-8 space-y-4 bg-gray-50/30 border-b border-gray-50">
                <div class="flex-grow flex items-center px-6 py-4 bg-white rounded-2xl border border-gray-100 gap-4 shadow-sm">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" x-model="search" placeholder="Cari pelanggan atau produk..." class="w-full border-none focus:ring-0 text-sm font-medium placeholder:text-gray-300">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="viewMode = 'all'" :class="viewMode === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">Semua</button>
                    <button type="button" @click="viewMode = 'queue'" :class="viewMode === 'queue' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-amber-700 border-amber-200'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">Antrian Negosiasi</button>
                    <button type="button" @click="viewMode = 'with_code'" :class="viewMode === 'with_code' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-700 border-blue-200'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">Butuh Kode / Sudah Kode</button>
                    <button type="button" @click="viewMode = 'rejected'" :class="viewMode === 'rejected' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-red-700 border-red-200'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">Ditolak</button>
                </div>
                <p class="text-[11px] text-gray-500 font-semibold">
                    `Antrian Negosiasi` = menunggu ACC. `Butuh Kode / Sudah Kode` = khusus pesanan yang memang menggunakan alur kode negosiasi.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipe</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Item Pesanan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Unit</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total / Nego</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pesanans as $pesanan)
                            <tr class="hover:bg-gray-50/30 transition-colors group"
                                x-show="shouldShowOrder(
                                    '{{ strtolower($pesanan->pelanggan->nama.' '.$pesanan->details->pluck('produk.nama')->filter()->implode(' ')) }}',
                                    '{{ $pesanan->status }}',
                                    {{ $pesanan->harga_usulan ? 'true' : 'false' }},
                                    {{ $pesanan->kode_nego ? 'true' : 'false' }},
                                    {{ in_array((string) $pesanan->sumber_pesanan, ['whatsapp', 'telepon', 'datang_langsung', 'input_admin'], true) ? 'false' : 'true' }}
                                )">
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-gray-900">{{ $pesanan->tanggal->format('d M Y') }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $pesanan->pelanggan->nama }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $pesanan->pelanggan->no_wa }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700">
                                        Produk
                                    </span>
                                    @php
                                        $sumberLabel = match((string) $pesanan->sumber_pesanan) {
                                            'website' => 'Website',
                                            'whatsapp' => 'WhatsApp',
                                            'telepon' => 'Telepon',
                                            'datang_langsung' => 'Datang langsung',
                                            'data_lama' => 'Data lama',
                                            default => 'Input admin',
                                        };
                                    @endphp
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-2">Sumber: {{ $sumberLabel }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $pesanan->details->count() }} item</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                        {{ $pesanan->details->pluck('produk.nama')->filter()->take(2)->implode(', ') ?: 'Pesanan WhatsApp' }}
                                    </p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-gray-900">{{ $pesanan->details->sum('jumlah') }} unit</p>
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $customerOffer = $pesanan->harga_penawaran_pelanggan ?? $pesanan->harga_usulan;
                                        $isNego = $customerOffer && in_array($pesanan->status, ['menunggu', 'menunggu persetujuan']);
                                        $isDealPendingPayment = $pesanan->harga_usulan && $pesanan->status === 'pending';
                                        $isAcc  = $pesanan->harga_usulan && in_array($pesanan->status, ['diproses', 'selesai']);
                                    @endphp
                                    @if($isNego)
                                        <p class="text-sm font-black text-amber-700">Rp {{ number_format($customerOffer, 0, ',', '.') }}</p>
                                        <p class="text-[9px] text-gray-400 line-through mt-0.5">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                                        <p class="text-[9px] font-bold text-amber-600 mt-0.5">Harga Usulan (Menunggu ACC)</p>
                                    @elseif($isDealPendingPayment)
                                        <p class="text-sm font-black text-blue-700">Rp {{ number_format($pesanan->harga_usulan, 0, ',', '.') }}</p>
                                        <p class="text-[9px] text-gray-400 line-through mt-0.5">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                                        <p class="text-[9px] font-bold text-blue-600 mt-0.5">Harga Deal (Menunggu Pembayaran)</p>
                                    @elseif($isAcc)
                                        <p class="text-sm font-black text-emerald-700">Rp {{ number_format($pesanan->harga_usulan, 0, ',', '.') }}</p>
                                        <p class="text-[9px] text-gray-400 line-through mt-0.5">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                                        <p class="text-[9px] font-bold text-emerald-600 mt-0.5">Harga Deal</p>
                                    @else
                                        <p class="text-sm font-black text-gray-900">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                                        <p class="text-[9px] font-bold text-gray-500 mt-0.5">Harga Normal</p>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $s = $pesanan->status;
                                        $isManualOrder = in_array((string) $pesanan->sumber_pesanan, ['whatsapp', 'telepon', 'datang_langsung', 'input_admin'], true);
                                        $requiresNegoCode = !$isManualOrder;
                                        $hasProof = !empty($pesanan->bukti_pembayaran);
                                        $manualStage = null;
                                        if ($isManualOrder) {
                                            if (!empty($pesanan->pembayaran_terkonfirmasi_at) || in_array($s, ['ditugaskan ke teknisi', 'dikerjakan teknisi', 'selesai oleh teknisi', 'dikonfirmasi admin', 'selesai final', 'selesai'], true)) {
                                                $manualStage = 'paid';
                                            } elseif ($hasProof) {
                                                $manualStage = 'waiting_verification';
                                            } elseif (!empty($pesanan->link_pembayaran_terkirim_at)) {
                                                $manualStage = 'payment_detail_sent';
                                            } else {
                                                $manualStage = 'waiting_payment';
                                            }
                                        }

                                        if ($s === 'ditugaskan ke teknisi') { $badge = ['bg-purple-50 text-purple-700', 'ASSIGNED']; }
                                        elseif ($s === 'dikerjakan teknisi') { $badge = ['bg-indigo-50 text-indigo-700', 'DIKERJAKAN TEKNISI']; }
                                        elseif ($s === 'selesai oleh teknisi') { $badge = ['bg-emerald-50 text-emerald-700', 'SELESAI OLEH TEKNISI']; }
                                        elseif ($s === 'dikonfirmasi admin') { $badge = ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI ADMIN']; }
                                        elseif ($s === 'selesai final') { $badge = ['bg-green-50 text-green-700', 'SELESAI FINAL']; }
                                        elseif ($isManualOrder && $manualStage === 'waiting_payment') { $badge = ['bg-blue-50 text-blue-700', 'MENUNGGU PEMBAYARAN']; }
                                        elseif ($isManualOrder && $manualStage === 'payment_detail_sent') { $badge = ['bg-indigo-50 text-indigo-700', 'DETAIL PEMBAYARAN TERKIRIM']; }
                                        elseif ($isManualOrder && $manualStage === 'waiting_verification') { $badge = ['bg-amber-50 text-amber-700', 'MENUNGGU VERIFIKASI PEMBAYARAN']; }
                                        elseif ($isManualOrder && $manualStage === 'paid') { $badge = ['bg-emerald-50 text-emerald-700', 'LUNAS']; }
                                        elseif ($s === 'pending' && $hasProof) { $badge = ['bg-emerald-50 text-emerald-700', 'SUDAH BAYAR / DIPROSES']; }
                                        elseif ($s === 'pending') { $badge = ['bg-blue-50 text-blue-700', 'MENUNGGU PEMBAYARAN']; }
                                        elseif ($s === 'diproses' && !$hasProof) { $badge = ['bg-blue-50 text-blue-700', 'MENUNGGU PEMBAYARAN']; }
                                        elseif ($s === 'diproses') { $badge = ['bg-emerald-50 text-emerald-700', 'SUDAH BAYAR / DIPROSES']; }
                                        elseif ($s === 'selesai') { $badge = ['bg-emerald-50 text-emerald-700', 'SELESAI']; }
                                        elseif ($s === 'menunggu persetujuan') { $badge = ['bg-amber-50 text-amber-700', 'MENUNGGU ACC']; }
                                        elseif ($s === 'ditolak') { $badge = ['bg-red-50 text-red-700', 'DITOLAK']; }
                                        else { $badge = ['bg-blue-50 text-blue-700', 'MENUNGGU (NORMAL)']; }
                                        
                                        $butuhKodeNego = ($requiresNegoCode && !empty($pesanan->harga_usulan) && $s === 'diproses' && empty($pesanan->kode_nego));
                                        $kodeTerpakai = !empty($pesanan->kode_nego_terpakai_at);
                                    @endphp
                                    <div class="space-y-2">
                                        @if($butuhKodeNego)
                                            <span class="inline-flex px-3 py-1 bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                                BUTUH KODE NEGO
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 {{ $badge[0] }} text-[9px] font-black uppercase tracking-widest rounded-lg">
                                                {{ $badge[1] }}
                                            </span>
                                        @endif
                                        @if($kodeTerpakai)
                                            <span class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-700 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                                KODE TERPAKAI
                                            </span>
                                        @endif
                                        @if($hasProof)
                                            <span class="inline-flex px-3 py-1 bg-sky-50 text-sky-700 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                                BUKTI TF MASUK
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    @php
                                        $isManualOrder = in_array((string) $pesanan->sumber_pesanan, ['whatsapp', 'telepon', 'datang_langsung', 'input_admin'], true);
                                        $isPaidManual = $isManualOrder && (
                                            !empty($pesanan->pembayaran_terkonfirmasi_at)
                                            || in_array($pesanan->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi', 'selesai oleh teknisi', 'dikonfirmasi admin', 'selesai final', 'selesai'], true)
                                        );
                                        $manualStage = null;
                                        if ($isManualOrder) {
                                            if ($isPaidManual) {
                                                $manualStage = 'paid';
                                            } elseif (!empty($pesanan->bukti_pembayaran)) {
                                                $manualStage = 'waiting_verification';
                                            } elseif (!empty($pesanan->link_pembayaran_terkirim_at)) {
                                                $manualStage = 'payment_detail_sent';
                                            } else {
                                                $manualStage = 'waiting_payment';
                                            }
                                        }
                                        $assignStatusEligible = in_array($pesanan->status, ['diproses', 'disetujui', 'menunggu diproses admin'], true);
                                        $canAssignNow = $assignStatusEligible && !$pesanan->teknisi_id && (!$isManualOrder || $manualStage === 'paid');
                                    @endphp
                                    <div class="flex justify-end gap-2 items-center" x-data="{ showAssign: false }">
                                        @if($isManualOrder && !$pesanan->teknisi_id && $manualStage === 'waiting_payment')
                                            <form action="{{ route('admin.pesanan.kirim-link-pembayaran', $pesanan) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-2 font-black text-[10px] uppercase tracking-widest rounded-xl transition-all shadow-sm border border-blue-800" style="background-color:#1d4ed8;color:#ffffff;">
                                                    Kirim Detail Pembayaran
                                                </button>
                                            </form>
                                        @endif

                                        @if($isManualOrder && !$pesanan->teknisi_id && $manualStage === 'payment_detail_sent')
                                            <form action="{{ route('admin.pesanan.kirim-link-pembayaran', $pesanan) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-2 font-black text-[10px] uppercase tracking-widest rounded-xl transition-all shadow-sm border border-blue-800" style="background-color:#1d4ed8;color:#ffffff;">
                                                    Kirim Ulang Detail
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.pesanan.input-bukti-pembayaran-manual', $pesanan) }}" method="POST" enctype="multipart/form-data" class="inline">
                                                @csrf
                                                <input type="file" name="bukti_pembayaran" id="proof-upload-{{ $pesanan->id }}" class="hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="if(this.files.length){this.form.submit();}">
                                                <button type="button" onclick="document.getElementById('proof-upload-{{ $pesanan->id }}').click()" class="px-3 py-2 bg-amber-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-amber-700 transition-all">
                                                    Input Bukti Pembayaran
                                                </button>
                                            </form>
                                        @endif

                                        @if($isManualOrder && !$pesanan->teknisi_id && $manualStage === 'waiting_verification')
                                            @if(!empty($pesanan->bukti_pembayaran))
                                                <a href="{{ asset('storage/' . ltrim($pesanan->bukti_pembayaran, '/')) }}" target="_blank" class="px-3 py-2 bg-sky-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-sky-700 transition-all">
                                                    Verifikasi Bukti
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.pesanan.konfirmasi-pembayaran-manual', $pesanan) }}" method="POST" class="inline" onsubmit="return confirm('Tandai lunas setelah bukti pembayaran diverifikasi?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-2 bg-emerald-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all">
                                                    Tandai Lunas
                                                </button>
                                            </form>
                                        @endif

                                        @if($canAssignNow)
                                            <div class="relative">
                                                <button type="button" @click="showAssign = !showAssign" class="px-3 py-2 bg-red-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all flex items-center gap-1 shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    Assign
                                                </button>
                                                <div x-show="showAssign" @click.away="showAssign = false" x-cloak
                                                    class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 p-4">
                                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tugaskan Teknisi</p>
                                                    @if(isset($teknisis) && $teknisis->count() > 0)
                                                        <form action="{{ route('admin.pesanan.assign-teknisi', $pesanan) }}" method="POST" class="space-y-3">
                                                            @csrf
                                                            <select name="teknisi_id" required class="w-full px-3 py-2 bg-gray-50 border-none rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-red-600/20">
                                                                <option value="">-- Pilih Teknisi --</option>
                                                                @foreach($teknisis as $tek)
                                                                    <option value="{{ $tek->id }}">{{ $tek->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="w-full py-2 bg-red-600 text-white font-black text-xs rounded-xl hover:bg-red-700 transition uppercase tracking-widest">
                                                                Kirim
                                                            </button>
                                                        </form>
                                                    @else
                                                        <p class="text-xs text-gray-500">Tidak ada teknisi.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($pesanan->teknisi_id)
                                            <div class="px-3 py-1.5 bg-red-50 rounded-xl border border-red-100 flex flex-col items-end mr-1" title="Selesai pada: {{ $pesanan->teknisi_selesai_at ? $pesanan->teknisi_selesai_at->format('d M Y H:i') : 'Belum selesai' }}">
                                                <span class="text-[8px] font-black uppercase tracking-widest text-red-400">Assigned</span>
                                                <span class="text-[10px] font-bold text-red-700 whitespace-nowrap">{{ $pesanan->teknisi->name }}</span>
                                            </div>
                                        @endif

                                        @if(in_array($pesanan->status, ['selesai oleh teknisi', 'dikonfirmasi admin'], true))
                                            <div class="flex flex-col items-end gap-2">
                                                <p class="text-[10px] font-bold text-emerald-600 mb-1">Teknisi sudah selesai</p>
                                                <form action="{{ route('admin.pesanan.selesai-final', $pesanan) }}" method="POST" class="inline" onsubmit="return confirm('Selesaikan final pesanan ini?')">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2.5 bg-emerald-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Final
                                                    </button>
                                                </form>
                                            </div>
                                        @endif

                                        @php
                                            $requiresNegoCode = !$isManualOrder;
                                            $canManageRowNego = in_array($pesanan->status, ['menunggu', 'menunggu persetujuan'], true)
                                                || ($requiresNegoCode && !empty($pesanan->harga_usulan) && empty($pesanan->kode_nego) && $pesanan->status !== 'selesai');
                                        @endphp
                                        <button type="button" onclick="openNegoModal({{ $pesanan->id }})" class="px-3 py-2 bg-white {{ $canManageRowNego ? 'text-amber-700 border-amber-200 hover:bg-amber-50' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }} rounded-xl border hover:shadow-lg transition-all text-[10px] font-black uppercase tracking-widest" title="Kelola Negosiasi">
                                            {{ $canManageRowNego ? 'Kelola Nego' : 'Detail' }}
                                        </button>
                                        <a href="{{ route('admin.pesanan.invoice.pdf', $pesanan) }}" class="p-2.5 bg-white text-gray-400 hover:text-emerald-600 rounded-xl border border-gray-100 hover:border-emerald-100 hover:shadow-lg transition-all" title="Cetak PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A2 2 0 0114 3.586L18.414 8A2 2 0 0119 9.414V18a2 2 0 01-2 2z" /></svg>
                                        </a>
                                        <form action="{{ route('admin.pesanan.destroy', $pesanan) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2.5 bg-white text-gray-400 hover:text-red-600 rounded-xl border border-gray-100 hover:border-red-100 hover:shadow-lg transition-all" onclick="return confirm('Yakin ingin menghapus pesanan ini?')" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @if($isManualOrder && !$pesanan->teknisi_id && in_array($manualStage, ['waiting_payment', 'payment_detail_sent', 'waiting_verification'], true))
                                        <p class="text-[10px] font-semibold text-gray-500 mt-2">Assign muncul setelah status pembayaran sudah lunas.</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-sm font-medium text-gray-500">Belum ada pesanan yang dicatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══ FLASH TOAST ═══ --}}
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

        {{-- ═══ NEGO DETAIL MODALS (per pesanan) ═══ --}}
        @foreach($pesanans as $p)
        <div id="modal-nego-{{ $p->id }}" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" onclick="closeNegoModal({{ $p->id }})"></div>
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-gray-100 z-10">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Detail Pesanan #{{ $p->id }}</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                            {{ $p->tanggal->format('d M Y') }}
                            @if($p->is_nego)
                                <span class="ml-2 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full">NEGOSIASI</span>
                            @endif
                            @if($p->kode_nego)
                                <span class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">{{ $p->kode_nego }}</span>
                            @endif
                        </p>
                    </div>
                    <button onclick="closeNegoModal({{ $p->id }})" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Data Pelanggan --}}
                    <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pelanggan</p>
                            <p class="font-bold text-gray-900">{{ $p->pelanggan->nama }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">WhatsApp</p>
                            <p class="font-bold text-gray-900">{{ $p->pelanggan->no_wa }}</p>
                        </div>
                        @if($p->pelanggan->alamat)
                        <div class="col-span-2">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Alamat</p>
                            <p class="font-semibold text-gray-700">{{ $p->pelanggan->alamat }}</p>
                        </div>
                        @endif
                        @if($p->keterangan)
                        <div class="col-span-2">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Keterangan</p>
                            <p class="font-medium text-gray-600 text-xs">{{ $p->keterangan }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Detail Produk --}}
                    @if($p->details->count())
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Detail Produk</p>
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk</th>
                                        <th class="px-4 py-2 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Qty</th>
                                        <th class="px-4 py-2 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($p->details as $det)
                                    <tr>
                                        <td class="px-4 py-2 font-semibold text-gray-900">
                                            {{ $det->produk?->nama ?? 'Produk Dihapus' }}
                                            @if($det->kapasitas)<span class="text-gray-400 text-xs"> — {{ $det->kapasitas }}</span>@endif
                                        </td>
                                        <td class="px-4 py-2 text-center font-bold">{{ $det->jumlah }}</td>
                                        <td class="px-4 py-2 text-right font-bold text-gray-900">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @php
                        $subtotalBarangNormal = (float) $p->details->sum('subtotal');
                        $ongkirNormal = (float) ($p->ongkir ?? 0);
                        $totalNormalAkhir = (float) (($p->total ?? 0) > 0 ? $p->total : ($subtotalBarangNormal + $ongkirNormal));
                        $customerOffer = $p->harga_penawaran_pelanggan ?? $p->harga_usulan;
                        $approvedDeal = $p->harga_usulan;
                    @endphp

                    {{-- Harga Summary --}}
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                        <div class="flex justify-between font-semibold text-gray-500">
                            <span>Subtotal Barang</span>
                            <span>Rp {{ number_format($subtotalBarangNormal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-500">
                            <span>Ongkir</span>
                            <span>Rp {{ number_format($ongkirNormal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-500">
                            <span>Total Normal (Akhir)</span>
                            <span>Rp {{ number_format($totalNormalAkhir, 0, ',', '.') }}</span>
                        </div>
                        @if($customerOffer)
                        <div class="flex justify-between font-bold text-amber-700">
                            <span>Penawaran Pelanggan</span>
                            <span>Rp {{ number_format($customerOffer, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($approvedDeal && (!$customerOffer || (float) $approvedDeal !== (float) $customerOffer || $p->kode_nego))
                        <div class="flex justify-between font-bold text-blue-700">
                            <span>Harga Deal Admin</span>
                            <span>Rp {{ number_format($approvedDeal, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($p->kode_nego)
                        <div class="flex justify-between text-xs font-bold text-blue-600">
                            <span>Kode Nego di-ACC</span>
                            <span>{{ $p->kode_nego }}</span>
                        </div>
                        @endif
                        @if($p->kode_nego_terpakai_at)
                        <div class="flex justify-between text-xs font-bold text-emerald-600">
                            <span>Kode Sudah Dipakai</span>
                            <span>{{ $p->kode_nego_terpakai_at->format('d M Y H:i') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Bukti Pembayaran</p>
                        @if($p->bukti_pembayaran)
                            <a href="{{ asset('storage/' . ltrim($p->bukti_pembayaran, '/')) }}" target="_blank" class="block">
                                <img src="{{ asset('storage/' . ltrim($p->bukti_pembayaran, '/')) }}" alt="Bukti pembayaran pesanan #{{ $p->id }}" class="w-full max-h-64 object-contain rounded-xl border border-gray-200 bg-white">
                            </a>
                            <p class="text-xs text-emerald-700 font-bold mt-2">Klik gambar untuk buka ukuran penuh.</p>
                        @else
                            <p class="text-sm text-gray-500 font-semibold">Belum ada bukti transfer dari pelanggan.</p>
                        @endif
                    </div>

                    {{-- FORM ACC / TOLAK / GENERATE KODE --}}
                    @php
                        $isManualNegoFlow = in_array((string) $p->sumber_pesanan, ['whatsapp', 'telepon', 'datang_langsung', 'input_admin'], true);
                        $canManageNego = !empty($customerOffer) && empty($p->kode_nego) && $p->tipe === 'produk' && $p->status !== 'selesai' && !$isManualNegoFlow;
                        $needAction = in_array($p->status, ['menunggu', 'menunggu persetujuan']) || $canManageNego;
                    @endphp
                    @if($needAction)
                    <div class="border-2 border-dashed border-amber-200 rounded-xl p-5 bg-amber-50">
                        <p class="text-xs font-black text-amber-800 uppercase tracking-widest mb-4">
                            {{ $isManualNegoFlow ? 'Tindakan Admin - ACC / Tolak Harga Deal Manual' : 'Tindakan Admin - ACC / Tolak / Generate Kode' }}
                        </p>

                        <form action="{{ route('admin.pesanan.nego-action', $p) }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="nego_modal_id" value="{{ $p->id }}">
                            @if((int) old('nego_modal_id') === (int) $p->id && $errors->any())
                                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                                    <p class="text-sm font-bold text-red-700">{{ $errors->first() }}</p>
                                </div>
                            @endif
                            <div class="space-y-3">
                                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
                                    @if($isManualNegoFlow)
                                        <p class="text-[11px] font-bold text-blue-800">
                                            Ini pesanan manual admin. Saat ACC, harga deal langsung dipakai tanpa kode negosiasi.
                                        </p>
                                    @else
                                        <p class="text-[11px] font-bold text-blue-800">
                                            Kode negosiasi akan digenerate otomatis saat klik
                                            <span class="font-black">ACC + Generate Kode</span> (format: ANUTA-xxx).
                                        </p>
                                    @endif
                                    <p class="text-[11px] font-semibold text-blue-700 mt-1">
                                        Harga deal final tidak boleh lebih besar dari total normal akhir (subtotal + ongkir).
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Harga Deal Final <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs font-bold text-gray-400">Rp</span>
                                        <input type="text" inputmode="numeric" data-money-input="1" name="harga_final" value="{{ (int) old('nego_modal_id') === (int) $p->id ? old('harga_final', $customerOffer ?? $approvedDeal ?? $totalNormalAkhir) : ($customerOffer ?? $approvedDeal ?? $totalNormalAkhir) }}"
                                            class="w-full pl-10 pr-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-900 focus:outline-none focus:border-emerald-400 transition">
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-3 pt-1">
                                <button type="submit" name="action" value="acc"
                                    class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl transition text-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ $isManualNegoFlow ? 'ACC Harga Deal' : 'ACC + Generate Kode' }}
                                </button>
                                <button type="submit" name="action" value="tolak"
                                    onclick="return confirm('Yakin ingin MENOLAK negosiasi ini?')"
                                    class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl transition text-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    TOLAK
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="text-center py-3">
                        @php
                            $hasProofInModal = !empty($p->bukti_pembayaran);
                            $isManualInModal = in_array((string) $p->sumber_pesanan, ['whatsapp', 'telepon', 'datang_langsung', 'input_admin'], true);
                            $isPaidManualInModal = $isManualInModal && (
                                !empty($p->pembayaran_terkonfirmasi_at)
                                || in_array($p->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi', 'selesai oleh teknisi', 'dikonfirmasi admin', 'selesai final', 'selesai'], true)
                            );
                            $manualStageInModal = null;
                            if ($isManualInModal) {
                                if ($isPaidManualInModal) {
                                    $manualStageInModal = 'paid';
                                } elseif ($hasProofInModal) {
                                    $manualStageInModal = 'waiting_verification';
                                } elseif (!empty($p->link_pembayaran_terkirim_at)) {
                                    $manualStageInModal = 'payment_detail_sent';
                                } else {
                                    $manualStageInModal = 'waiting_payment';
                                }
                            }

                            if ($p->status === 'ditugaskan ke teknisi') {
                                $stLabel = ['bg-purple-100 text-purple-700', 'Assigned'];
                            } elseif ($isManualInModal && $manualStageInModal === 'waiting_payment') {
                                $stLabel = ['bg-blue-100 text-blue-700', 'Menunggu Pembayaran'];
                            } elseif ($isManualInModal && $manualStageInModal === 'payment_detail_sent') {
                                $stLabel = ['bg-indigo-100 text-indigo-700', 'Detail Pembayaran Terkirim'];
                            } elseif ($isManualInModal && $manualStageInModal === 'waiting_verification') {
                                $stLabel = ['bg-amber-100 text-amber-700', 'Menunggu Verifikasi Pembayaran'];
                            } elseif ($isManualInModal && $manualStageInModal === 'paid') {
                                $stLabel = ['bg-emerald-100 text-emerald-700', 'Lunas'];
                            } elseif ($p->status === 'pending') {
                                $stLabel = $hasProofInModal
                                    ? ['bg-emerald-100 text-emerald-700', 'Sudah Bayar / Diproses']
                                    : ['bg-blue-100 text-blue-700', 'Menunggu Pembayaran'];
                            } elseif ($p->status === 'diproses') {
                                $stLabel = $hasProofInModal
                                    ? ['bg-emerald-100 text-emerald-700', 'Sudah Bayar / Diproses']
                                    : ['bg-blue-100 text-blue-700', 'Menunggu Pembayaran'];
                            } elseif ($p->status === 'selesai') {
                                $stLabel = ['bg-blue-100 text-blue-700', 'Selesai'];
                            } elseif ($p->status === 'ditolak') {
                                $stLabel = ['bg-red-100 text-red-700', 'Ditolak'];
                            } else {
                                $stLabel = ['bg-gray-100 text-gray-600', ucfirst($p->status)];
                            }
                        @endphp
                        <span class="px-4 py-2 {{ $stLabel[0] }} rounded-xl text-sm font-black">{{ $stLabel[1] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        <div x-show="openModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openModal = false"></div>
            <div
                x-show="openModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between px-8 py-6 bg-white/95 backdrop-blur border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Input Pesanan Manual</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Catat pesanan dari WhatsApp, telepon, datang langsung, atau input admin dengan alur operasional yang lengkap.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-8 sm:p-10">
                    <form action="{{ route('admin.pesanan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipe" value="produk">
                        @if($errors->any())
                            <div class="mb-8 rounded-2xl border border-red-100 bg-red-50 px-6 py-5">
                                <p class="text-sm font-black text-red-700">{{ $errors->first() }}</p>
                            </div>
                        @endif

                        <div class="mb-8 rounded-[1.75rem] border border-red-100 bg-red-50 px-6 py-5">
                            <p class="text-sm font-black text-red-700">Pesanan manual tetap dianggap transaksi baru dan mengikuti alur pembayaran bertahap.</p>
                            <p class="text-sm font-semibold text-red-700/80 mt-2">Assign teknisi baru tersedia setelah pembayaran terverifikasi dan status lunas.</p>
                        </div>
                        @if(!empty($targetNegoPesanan))
                            <div class="mb-8 rounded-[1.75rem] border border-amber-200 bg-amber-50 px-6 py-5">
                                <p class="text-sm font-black text-amber-800">Data pelanggan & item sudah diprefill dari inquiry WhatsApp pesanan #{{ $targetNegoPesanan->id }}.</p>
                                <p class="text-sm font-semibold text-amber-700 mt-2">Anda bisa langsung input harga deal manual, simpan, lalu ACC + generate kode negosiasi.</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
                            <div class="xl:col-span-2 space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <div class="flex items-center justify-between gap-4 mb-2">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Data Pelanggan</label>
                                            <div class="flex gap-2">
                                                <button type="button" @click="customerMode = 'existing'" :class="customerMode === 'existing' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200'" class="px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition">Pilih Pelanggan</button>
                                                <button type="button" @click="customerMode = 'new'" :class="customerMode === 'new' ? 'bg-red-700 text-white border-red-700' : 'bg-white text-red-700 border-red-200'" class="px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition">Tambah Pelanggan Baru</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="pelanggan_mode" :value="customerMode">
                                        <x-input-error :messages="$errors->get('pelanggan_mode')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2" x-show="customerMode === 'existing'" x-transition>
                                        <label for="pelanggan_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pilih Pelanggan</label>
                                        <select name="pelanggan_id" id="pelanggan_id" x-model="selectedPelangganId" :required="customerMode === 'existing'" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                            <option value="">Pilih pelanggan</option>
                                            @foreach($pelanggans as $pelanggan)
                                                <option value="{{ $pelanggan->id }}" @selected(old('pelanggan_id') == $pelanggan->id)>{{ $pelanggan->nama }} - {{ $pelanggan->no_wa }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('pelanggan_id')" class="mt-2" />
                                        
                                        <div x-show="selectedPelangganId && !pelangganDict[selectedPelangganId]?.alamat_lat" class="mt-4" x-cloak>
                                            <div class="mb-3 px-4 py-3 bg-amber-50 rounded-xl border border-amber-200">
                                                <p class="text-xs font-bold text-amber-800">Pelanggan ini belum memiliki data koordinat lokasi.</p>
                                                <p class="text-[10px] font-semibold text-amber-700 mt-1">Silakan cari alamatnya segera via peta agar ongkir "Diantar" bisa dikalkulasi.</p>
                                            </div>
                                            <div class="relative">
                                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Ketik Alamat (OpenStreetMap)</label>
                                                <input type="text" name="fallback_alamat_maps" x-model="fallbackCustomerMaps" @input="scheduleFallbackSuggest()" @focus="scheduleFallbackSuggest()" class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="Ketik area atau alamat rinci...">
                                                <div x-show="fallbackAddressSuggestions.length > 0" x-cloak class="absolute z-20 mt-2 w-full max-h-56 overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                                                    <template x-for="(item, idx) in fallbackAddressSuggestions" :key="idx">
                                                        <button type="button" @click="selectFallbackAddress(item)" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-red-50 transition border-b last:border-b-0 border-gray-100" x-text="item.display_name"></button>
                                                    </template>
                                                </div>
                                                <p class="text-[10px] font-semibold text-gray-500 mt-2" x-text="fallbackAddressHelper"></p>
                                                <input type="hidden" name="fallback_alamat_lat" x-model="fallbackCustomerLat">
                                                <input type="hidden" name="fallback_alamat_lng" x-model="fallbackCustomerLng">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2 space-y-4" x-show="customerMode === 'new'" x-transition>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="new_pelanggan_nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                                <input type="text" name="new_pelanggan_nama" id="new_pelanggan_nama" x-model="newCustomer.nama" :required="customerMode === 'new'" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="Contoh: Budi Santoso">
                                                <x-input-error :messages="$errors->get('new_pelanggan_nama')" class="mt-2" />
                                            </div>
                                            <div>
                                                <label for="new_pelanggan_perusahaan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Perusahaan <span class="text-gray-300">(Opsional)</span></label>
                                                <input type="text" name="new_pelanggan_perusahaan" id="new_pelanggan_perusahaan" x-model="newCustomer.perusahaan" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="PT / CV / Instansi">
                                                <x-input-error :messages="$errors->get('new_pelanggan_perusahaan')" class="mt-2" />
                                            </div>
                                        </div>
                                        <div>
                                            <label for="new_pelanggan_no_wa" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                            <input type="text" name="new_pelanggan_no_wa" id="new_pelanggan_no_wa" x-model="newCustomer.no_wa" :required="customerMode === 'new'" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="081234567890">
                                            <x-input-error :messages="$errors->get('new_pelanggan_no_wa')" class="mt-2" />
                                        </div>
                                        <div class="relative">
                                            <label for="new_pelanggan_alamat_maps" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alamat via OpenStreetMap <span class="text-red-500">*</span></label>
                                            <input type="text" name="new_pelanggan_alamat_maps" id="new_pelanggan_alamat_maps" x-model="newCustomer.alamat_maps" @input="scheduleCustomerAddressSuggest()" @focus="scheduleCustomerAddressSuggest()" :required="customerMode === 'new'" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="Cari alamat (OpenStreetMap)...">
                                            <div x-show="customerAddressSuggestions.length > 0" x-cloak class="absolute z-20 mt-2 w-full max-h-56 overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                                                <template x-for="(item, idx) in customerAddressSuggestions" :key="idx">
                                                    <button type="button" @click="selectCustomerAddress(item)" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-red-50 transition border-b last:border-b-0 border-gray-100" x-text="item.display_name"></button>
                                                </template>
                                            </div>
                                            <p class="text-[10px] font-semibold text-gray-500 mt-2" x-text="customerAddressHelper">Ketik minimal 3 huruf, lalu pilih saran alamat OpenStreetMap agar titik koordinat terkunci.</p>
                                            <x-input-error :messages="$errors->get('new_pelanggan_alamat_maps')" class="mt-2" />
                                        </div>
                                        <div>
                                            <label for="new_pelanggan_alamat_detail" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Detail Alamat <span class="text-red-500">*</span></label>
                                            <textarea name="new_pelanggan_alamat_detail" id="new_pelanggan_alamat_detail" x-model="newCustomer.alamat_detail" :required="customerMode === 'new'" rows="2" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="Contoh: Blok A2 No.10, patokan dekat minimarket, lantai 2."></textarea>
                                            <p class="text-[10px] font-semibold text-gray-500 mt-2">Isi patokan lokasi, blok, lantai, atau keterangan tambahan.</p>
                                            <x-input-error :messages="$errors->get('new_pelanggan_alamat_detail')" class="mt-2" />
                                        </div>
                                        <input type="hidden" name="new_pelanggan_alamat_lat" x-model="newCustomer.alamat_lat">
                                        <input type="hidden" name="new_pelanggan_alamat_lng" x-model="newCustomer.alamat_lng">
                                        <x-input-error :messages="$errors->get('new_pelanggan_alamat_lat')" class="mt-2" />
                                        <x-input-error :messages="$errors->get('new_pelanggan_alamat_lng')" class="mt-2" />
                                    </div>

                                    <div>
                                        <label for="tanggal" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Pesanan</label>
                                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                        <p class="text-[10px] font-semibold text-gray-500 mt-2">Gunakan tanggal saat pesanan diterima admin.</p>
                                        <x-input-error :messages="$errors->get('tanggal')" class="mt-2" />
                                    </div>

                                    <div>
                                        <label for="sumber_pesanan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Sumber Pesanan</label>
                                        <select name="sumber_pesanan" id="sumber_pesanan" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                            <option value="whatsapp" @selected(old('sumber_pesanan') === 'whatsapp')>WhatsApp</option>
                                            <option value="telepon" @selected(old('sumber_pesanan') === 'telepon')>Telepon</option>
                                            <option value="datang_langsung" @selected(old('sumber_pesanan') === 'datang_langsung')>Datang langsung</option>
                                            <option value="input_admin" @selected(old('sumber_pesanan', 'input_admin') === 'input_admin')>Input admin</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('sumber_pesanan')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <label for="harga_deal_manual" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Harga Deal Manual <span class="text-gray-300">(Opsional)</span></label>
                                    <div class="relative">
                                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400">Rp</span>
                                        <input type="text" inputmode="numeric" data-money-input="1" name="harga_deal_manual" id="harga_deal_manual" value="{{ old('harga_deal_manual') }}"
                                            placeholder="Kosongkan jika pesanan normal"
                                            class="w-full pl-12 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                    </div>
                                    <p class="text-[10px] font-semibold text-gray-500 mt-2">Jika diisi, pesanan masuk antrian persetujuan harga (negosiasi).</p>
                                    <x-input-error :messages="$errors->get('harga_deal_manual')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="catatan_admin" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Catatan Admin <span class="text-gray-300">(Opsional)</span></label>
                                    <textarea name="catatan_admin" id="catatan_admin" rows="3"
                                        class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                        placeholder="Contoh: pesanan dipindahkan dari catatan manual toko">{{ old('catatan_admin') }}</textarea>
                                    <p class="text-[10px] font-semibold text-gray-500 mt-2">Catatan ini untuk kebutuhan internal tim.</p>
                                    <x-input-error :messages="$errors->get('catatan_admin')" class="mt-2" />
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Metode Pengiriman</label>
                                        <div class="grid grid-cols-2 gap-3 p-1.5 bg-gray-50 rounded-2xl">
                                            <button type="button" @click="shippingMethod = 'pickup'; updateOngkirFromDistance()" 
                                                :class="shippingMethod === 'pickup' ? 'bg-white text-gray-900 shadow-sm border border-gray-100' : 'text-gray-500 hover:text-gray-700'"
                                                class="px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition duration-200">
                                                Ambil Sendiri
                                            </button>
                                            <button type="button" @click="shippingMethod = 'diantar_internal'; updateOngkirFromDistance()" 
                                                :class="shippingMethod === 'diantar_internal' ? 'bg-white text-emerald-700 shadow-sm border border-emerald-100' : 'text-gray-500 hover:text-gray-700'"
                                                class="px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition duration-200">
                                                Diantar Internal
                                            </button>
                                        </div>
                                        <input type="hidden" name="metode_pengiriman" :value="shippingMethod">
                                        <x-input-error :messages="$errors->get('metode_pengiriman')" class="mt-2" />
                                    </div>

                                    <div x-show="shippingMethod === 'diantar_internal'" x-transition>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">
                                            Estimasi Ongkir 
                                            <span x-show="distanceKm > 0" class="text-emerald-600 font-bold ml-1" x-text="'- Jarak: ' + distanceKm.toFixed(1) + ' km'"></span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400">Rp</span>
                                            <input type="text" name="ongkir" :value="ongkir" readonly class="w-full pl-12 pr-6 py-4 bg-gray-100 border-none rounded-2xl font-bold text-gray-900 focus:ring-0 cursor-not-allowed">
                                        </div>
                                        <div x-show="distanceKm === 0" class="mt-2 px-4 py-3 bg-red-50 border border-red-100 rounded-xl">
                                            <p class="text-[10px] font-bold text-red-700">Titik lokasi pelanggan belum ditemukan. Mohon lengkapi Data Pelanggan via OpenStreetMap, atau pastikan data Existing Pelanggan memiliki lokasi valid.</p>
                                        </div>
                                        <div x-show="distanceKm > 0" class="mt-2">
                                            <p class="text-[10px] font-semibold text-emerald-700">Dihitung otomatis dari jarak Maps toko ke pelanggan (<span x-text="currency(shippingRate)"></span> / km dengan minimum <span x-text="currency(shippingMin)"></span>).</p>
                                        </div>
                                        <x-input-error :messages="$errors->get('ongkir')" class="mt-2" />
                                    </div>
                                    <div x-show="shippingMethod === 'pickup'" x-cloak>
                                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <div>
                                                <p class="text-sm font-bold text-blue-900">Pelanggan mengambil sendiri</p>
                                                <p class="text-xs font-semibold text-blue-700 mt-0.5">Ongkir Rp 0 (gratis)</p>
                                            </div>
                                        </div>
                                        <input type="hidden" name="ongkir" value="0">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Item Produk</p>
                                            <h4 class="text-xl font-black text-gray-900 mt-2">Multi Produk</h4>
                                        </div>
                                        <button type="button" @click="addRow()" class="px-5 py-3 bg-white border border-gray-100 rounded-2xl text-xs font-black text-red-700 uppercase tracking-widest hover:shadow-lg transition">
                                            Tambah Produk
                                        </button>
                                    </div>

                                    <div class="space-y-4 mt-6">
                                        <template x-for="(row, index) in rows" :key="row.uid">
                                            <div x-transition class="rounded-[2rem] border border-gray-100 bg-gray-50/70 p-6">
                                                <div class="flex items-center justify-between gap-4">
                                                    <div>
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest" x-text="'Produk ' + (index + 1)"></p>
                                                        <p class="text-sm font-semibold text-gray-500 mt-2">Pilih produk, kapasitas, merek, lalu jumlah item.</p>
                                                    </div>
                                                    <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="w-11 h-11 rounded-2xl bg-white text-gray-400 hover:text-red-700 border border-gray-100 transition flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>

                                                <input type="hidden" :name="'items[' + index + '][produk_id]'" x-model="row.produk_id">

                                                <div class="mt-6">
                                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pilih Produk</label>
                                                    <select x-model="row.nama" @change="syncRow(index)" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                                                        <option value="">Pilih produk</option>
                                                        <template x-for="nama in productNames()" :key="nama">
                                                            <option :value="nama" x-text="nama"></option>
                                                        </template>
                                                    </select>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kapasitas</label>
                                                        <select :name="'items[' + index + '][kapasitas]'" x-model="row.kapasitas" @change="syncRow(index)" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                                                            <option value="">Pilih kapasitas</option>
                                                            <template x-for="kapasitas in capacityOptions(row.nama)" :key="kapasitas">
                                                                <option :value="kapasitas" x-text="kapasitas"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Merek</label>
                                                        <select :name="'items[' + index + '][merek]'" x-model="row.merek" @change="syncRow(index)" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                                                            <option value="">Pilih merek</option>
                                                            <template x-for="merek in brandOptions(row.nama, row.kapasitas)" :key="merek">
                                                                <option :value="merek" x-text="merek"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                                    <div>
                                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah</label>
                                                        <input :name="'items[' + index + '][jumlah]'" type="number" min="1" x-model="row.jumlah" @input="syncTotals()" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                                                    </div>
                                                    <div class="bg-white border border-gray-100 rounded-2xl p-5">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Harga</p>
                                                        <p class="text-lg font-black text-gray-900 mt-2" x-text="currency(row.harga)"></p>
                                                    </div>
                                                    <div class="bg-red-700 border border-red-700 rounded-2xl p-5 text-white">
                                                        <p class="text-[10px] font-black uppercase tracking-widest text-red-100">Subtotal</p>
                                                        <p class="text-lg font-black mt-2" x-text="currency(row.subtotal)"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="rounded-[2rem] border border-gray-100 bg-gray-50 p-6">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Invoice</p>
                                    <div class="mt-6 space-y-4">
                                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                                            <span>Tipe</span>
                                            <span>Pesanan Produk</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                                            <span>Total Varian</span>
                                            <span x-text="rows.length + ' item'"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                                            <span>Total Unit</span>
                                            <span x-text="totalUnit + ' unit'"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                                            <span>Subtotal</span>
                                            <span x-text="currency(grandTotal)"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                                            <span>Ongkir</span>
                                            <span x-text="currency(ongkir)"></span>
                                        </div>
                                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                                            <span class="text-sm font-black text-gray-900 uppercase tracking-widest">Total Akhir</span>
                                            <span class="text-2xl font-black text-red-700" x-text="currency(invoiceTotal())"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-4">
                            <button type="button" @click="openModal = false" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</button>
                            <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                                Simpan Pesanan
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
                    customerMode: initialForm?.pelanggan_mode === 'new' ? 'new' : 'existing',
                    shippingMethod: (initialForm?.metode_pengiriman === 'diantar' ? 'diantar_internal' : (initialForm?.metode_pengiriman || 'pickup')),
                    ongkir: Number(String(initialForm?.ongkir ?? 0).replace(/\D+/g, '') || 0),
                    pelangganDict: @json($pelanggans->map->only(['id', 'alamat_lat', 'alamat_lng'])->keyBy('id')),
                    storeLat: Number('{{ config('app.store_lat') }}'),
                    storeLng: Number('{{ config('app.store_lng') }}'),
                    shippingRate: Number('{{ config('app.shipping_rate_per_km') }}'),
                    shippingMin: Number('{{ config('app.shipping_min_cost') }}'),
                    distanceKm: 0,
                    customerAddressSuggestions: [],
                    customerAddressTimer: null,
                    customerAddressHelper: 'Ketik minimal 3 huruf, lalu pilih saran alamat OpenStreetMap agar titik koordinat terkunci.',
                    newCustomer: {
                        nama: initialForm?.new_pelanggan_nama || '',
                        perusahaan: initialForm?.new_pelanggan_perusahaan || '',
                        no_wa: initialForm?.new_pelanggan_no_wa || '',
                        alamat_maps: initialForm?.new_pelanggan_alamat_maps || '',
                        alamat_detail: initialForm?.new_pelanggan_alamat_detail || '',
                        alamat_lat: initialForm?.new_pelanggan_alamat_lat || '',
                        alamat_lng: initialForm?.new_pelanggan_alamat_lng || '',
                    },
                    fallbackCustomerMaps: '',
                    fallbackCustomerLat: '',
                    fallbackCustomerLng: '',
                    fallbackAddressSuggestions: [],
                    fallbackAddressTimer: null,
                    fallbackAddressHelper: 'Ketik lalu pilih untuk mengkonfirmasi otomatis kelengkapan data alamat.',
                    rows: [],
                    grandTotal: 0,
                    totalUnit: 0,
                    init() {
                        if (this.customerMode === 'new') {
                            this.selectedPelangganId = ''
                        }
                        this.rows = (oldItems.length ? oldItems : [{}]).map((item, index) => this.makeRow(item, index))
                        this.rows.forEach((row, index) => this.syncRow(index))
                        this.syncTotals()
                        this.updateOngkirFromDistance()
                        
                        this.$watch('selectedPelangganId', () => {
                            this.fallbackCustomerMaps = '';
                            this.fallbackCustomerLat = '';
                            this.fallbackCustomerLng = '';
                            this.updateOngkirFromDistance()
                        })
                        this.$watch('customerMode', () => this.updateOngkirFromDistance())
                    },
                    calculateDistance(lat1, lon1, lat2, lon2) {
                        if (!lat1 || !lon1 || !lat2 || !lon2) return 0;
                        const R = 6371;
                        const dLat = (lat2 - lat1) * Math.PI / 180;
                        const dLon = (lon2 - lon1) * Math.PI / 180;
                        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                                Math.sin(dLon/2) * Math.sin(dLon/2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                        return R * c;
                    },
                    updateOngkirFromDistance() {
                        if (this.shippingMethod === 'pickup') {
                            this.ongkir = 0;
                            this.distanceKm = 0;
                            return;
                        }
                        let custLat = null;
                        let custLng = null;
                        if (this.customerMode === 'new') {
                            custLat = Number(this.newCustomer.alamat_lat);
                            custLng = Number(this.newCustomer.alamat_lng);
                        } else {
                            const pel = this.pelangganDict[this.selectedPelangganId];
                            if (pel && pel.alamat_lat) {
                                custLat = Number(pel.alamat_lat);
                                custLng = Number(pel.alamat_lng);
                            } else if (this.fallbackCustomerLat) {
                                custLat = Number(this.fallbackCustomerLat);
                                custLng = Number(this.fallbackCustomerLng);
                            }
                        }
                        if (custLat && custLng && this.storeLat && this.storeLng) {
                            this.distanceKm = this.calculateDistance(this.storeLat, this.storeLng, custLat, custLng);
                            let cost = Math.ceil(this.distanceKm * this.shippingRate);
                            this.ongkir = Math.max(this.shippingMin, cost);
                        } else {
                            this.ongkir = 0;
                            this.distanceKm = 0;
                        }
                    },
                    makeRow(item, index) {
                        return {
                            uid: Date.now() + index + Math.floor(Math.random() * 1000),
                            nama: this.findProductName(item.produk_id) ?? '',
                            produk_id: item.produk_id ?? '',
                            kapasitas: item.kapasitas ?? '',
                            merek: item.merek ?? '',
                            jumlah: Number(item.jumlah ?? 1),
                            harga: 0,
                            subtotal: 0,
                        }
                    },
                    findProductName(produkId) {
                        const variant = this.catalog.find((item) => Number(item.id) === Number(produkId))
                        return variant ? variant.nama : null
                    },
                    productNames() {
                        return [...new Set(this.catalog.map((item) => item.nama))]
                    },
                    capacityOptions(nama) {
                        return [...new Set(this.catalog.filter((item) => item.nama === nama).map((item) => item.kapasitas))]
                    },
                    brandOptions(nama, kapasitas) {
                        return [...new Set(this.catalog.filter((item) => item.nama === nama && item.kapasitas === kapasitas).map((item) => item.merek))]
                    },
                    syncRow(index) {
                        const row = this.rows[index]
                        const kapasitasList = this.capacityOptions(row.nama)
                        if (kapasitasList.length && !kapasitasList.includes(row.kapasitas)) {
                            row.kapasitas = kapasitasList[0]
                        }

                        const merekList = this.brandOptions(row.nama, row.kapasitas)
                        if (merekList.length && !merekList.includes(row.merek)) {
                            row.merek = merekList[0]
                        }

                        const variant = this.catalog.find((item) => item.nama === row.nama && item.kapasitas === row.kapasitas && item.merek === row.merek)
                        row.produk_id = variant ? variant.id : ''
                        row.harga = variant ? Number(variant.harga) : 0
                        row.subtotal = row.harga * Number(row.jumlah || 0)
                        this.syncTotals()
                    },
                    syncTotals() {
                        this.rows.forEach((row) => {
                            row.subtotal = Number(row.harga) * Number(row.jumlah || 0)
                        })
                        this.grandTotal = this.rows.reduce((total, row) => total + Number(row.subtotal || 0), 0)
                        this.totalUnit = this.rows.reduce((total, row) => total + Number(row.jumlah || 0), 0)
                    },
                    invoiceTotal() {
                        return Number(this.grandTotal || 0) + Number(this.ongkir || 0)
                    },
                    async fetchCustomerAddressSuggestions(query, isFallback = false) {
                        try {
                            const response = await fetch(`{{ route('order.address.suggest') }}?q=${encodeURIComponent(query)}`, {
                                method: 'GET',
                                headers: { Accept: 'application/json' },
                                credentials: 'same-origin',
                            })
                            const data = await response.json()
                            if (!response.ok || !data.success) {
                                throw new Error(data.message || 'Saran alamat tidak tersedia.')
                            }
                            
                            if (isFallback) {
                                this.fallbackAddressSuggestions = data.data || []
                                this.fallbackAddressHelper = this.fallbackAddressSuggestions.length
                                    ? 'Pilih salah satu saran alamat.'
                                    : 'Alamat tidak ditemukan. Coba kata kunci lain.'
                            } else {
                                this.customerAddressSuggestions = data.data || []
                                this.customerAddressHelper = this.customerAddressSuggestions.length
                                    ? 'Pilih salah satu saran alamat agar titik koordinat tersimpan.'
                                    : 'Alamat tidak ditemukan. Coba kata kunci lain.'
                            }
                        } catch (error) {
                            if (isFallback) {
                                this.fallbackAddressSuggestions = []
                                this.fallbackAddressHelper = error.message || 'Gagal mengambil saran alamat.'
                            } else {
                                this.customerAddressSuggestions = []
                                this.customerAddressHelper = error.message || 'Gagal mengambil saran alamat.'
                            }
                        }
                    },
                    scheduleCustomerAddressSuggest() {
                        const query = String(this.newCustomer.alamat_maps || '').trim()
                        this.newCustomer.alamat_lat = ''
                        this.newCustomer.alamat_lng = ''

                        if (this.customerAddressTimer) clearTimeout(this.customerAddressTimer)

                        if (query.length < 3) {
                            this.customerAddressSuggestions = []
                            this.customerAddressHelper = 'Ketik minimal 3 huruf, lalu pilih saran alamat OpenStreetMap agar titik koordinat terkunci.'
                            return
                        }

                        this.customerAddressTimer = setTimeout(() => {
                            this.fetchCustomerAddressSuggestions(query, false)
                        }, 350)
                    },
                    selectCustomerAddress(item) {
                        this.newCustomer.alamat_maps = String(item?.display_name || '')
                        this.newCustomer.alamat_lat = String(item?.lat ?? '')
                        this.newCustomer.alamat_lng = String(item?.lng ?? '')
                        this.customerAddressSuggestions = []
                        this.customerAddressHelper = 'Alamat terpilih. Koordinat lokasi sudah tersimpan.'
                        this.updateOngkirFromDistance()
                    },
                    scheduleFallbackSuggest() {
                        const query = String(this.fallbackCustomerMaps || '').trim()
                        this.fallbackCustomerLat = ''
                        this.fallbackCustomerLng = ''

                        if (this.fallbackAddressTimer) clearTimeout(this.fallbackAddressTimer)

                        if (query.length < 3) {
                            this.fallbackAddressSuggestions = []
                            this.fallbackAddressHelper = 'Ketik minimal 3 huruf, lalu pilih alamat.'
                            return
                        }

                        this.fallbackAddressTimer = setTimeout(() => {
                            this.fetchCustomerAddressSuggestions(query, true)
                        }, 350)
                    },
                    selectFallbackAddress(item) {
                        this.fallbackCustomerMaps = String(item?.display_name || '')
                        this.fallbackCustomerLat = String(item?.lat ?? '')
                        this.fallbackCustomerLng = String(item?.lng ?? '')
                        this.fallbackAddressSuggestions = []
                        this.fallbackAddressHelper = 'Update Alamat Pelanggan dikunci.'
                        this.updateOngkirFromDistance()
                    },
                    addRow() {
                        this.rows.push(this.makeRow({}, this.rows.length))
                    },
                    removeRow(index) {
                        if (this.rows.length === 1) {
                            return
                        }
                        this.rows.splice(index, 1)
                        this.syncTotals()
                    },
                    currency(value) {
                        return 'Rp ' + Number(value || 0).toLocaleString('id-ID')
                    },
                    shouldShowOrder(haystack, status, hasNego, hasCode, requiresCode) {
                        const searchOk = this.search === '' || String(haystack).includes(this.search.toLowerCase());
                        if (!searchOk) return false;

                        if (this.viewMode === 'queue') {
                            return ['menunggu', 'menunggu persetujuan'].includes(status)
                                || (hasNego && requiresCode && status === 'diproses' && !hasCode);
                        }
                        if (this.viewMode === 'with_code') {
                            return !!hasNego && !!requiresCode;
                        }
                        if (this.viewMode === 'rejected') {
                            return status === 'ditolak';
                        }
                        return true;
                    }
                }
            }
        </script>
        <script>
            function formatMoneyInputValue(value) {
                const digits = String(value || '').replace(/\D+/g, '');
                if (!digits) return '';
                return Number(digits).toLocaleString('id-ID');
            }

            function bindMoneyInputs() {
                document.querySelectorAll('input[data-money-input]').forEach((input) => {
                    if (input.dataset.moneyBound === '1') return;
                    input.dataset.moneyBound = '1';

                    const handler = () => {
                        const cursorAtEnd = input.selectionStart === input.value.length;
                        input.value = formatMoneyInputValue(input.value);
                        if (cursorAtEnd) {
                            input.setSelectionRange(input.value.length, input.value.length);
                        }
                    };

                    input.addEventListener('input', handler);
                    input.addEventListener('blur', handler);
                    handler();
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                bindMoneyInputs();
            });

            function openNegoModal(id) {
                document.getElementById('modal-nego-' + id).classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                setTimeout(() => bindMoneyInputs(), 10);
            }
            function closeNegoModal(id) {
                document.getElementById('modal-nego-' + id).classList.add('hidden');
                document.body.style.overflow = '';
            }

            // Gunakan query auto-open sekali saja agar modal tidak muncul berulang saat refresh.
            document.addEventListener('DOMContentLoaded', () => {
                const url = new URL(window.location.href);
                const hasAutoOpenQuery = url.searchParams.has('open_nego') || url.searchParams.has('process_pelanggan');
                if (!hasAutoOpenQuery) return;

                url.searchParams.delete('open_nego');
                url.searchParams.delete('process_pelanggan');
                const qs = url.searchParams.toString();
                const cleanUrl = url.pathname + (qs ? '?' + qs : '') + url.hash;
                window.history.replaceState({}, '', cleanUrl);
            });
        </script>
    @endonce
</x-app-layout>
