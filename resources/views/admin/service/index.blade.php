<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Layanan APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola service, perawatan, dan refill APAR dalam satu alur layanan.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-service-modal'))" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Input Layanan
            </button>
        </div>
    </x-slot>

    <div
        class="space-y-8"
        x-data="{
            search: '',
            openModal: {{ $errors->any() ? 'true' : 'false' }},
            requestModalOpen: false,
            selectedRequestId: null,
            openRequestModal(id) {
                this.selectedRequestId = id;
                this.requestModalOpen = true;
            },
            closeRequestModal() {
                this.requestModalOpen = false;
                this.selectedRequestId = null;
            },
        }"
        @open-service-modal.window="openModal = true"
    >
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Layanan</p>
                <p class="text-4xl font-black text-gray-900">{{ $serviceLogs->count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Biaya Bulan Ini</p>
                @php $monthlyCost = $serviceLogs->filter(fn ($s) => $s->tgl_service?->isSameMonth(now()))->sum('biaya'); @endphp
                <p class="text-4xl font-black text-blue-600">Rp {{ number_format($monthlyCost/1000, 0) }}k</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Unit Ditangani</p>
                <p class="text-4xl font-black text-green-600">{{ $serviceLogs->pluck('unit_apar_id')->filter()->unique()->count() }}</p>
            </div>
        </div>

        <div class="rounded-[2rem] border border-blue-100 bg-blue-50 px-6 py-5">
            <p class="text-sm font-black text-blue-800">Pusat layanan APAR.</p>
            <p class="text-sm font-semibold text-blue-700/80 mt-2">Service dan refill pelanggan dikelola di sini. Stok peralatan baru dikurangi setelah admin mengonfirmasi pekerjaan selesai.</p>
        </div>

        @if(session('wa_url'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 flex items-center justify-between gap-3">
                <p class="text-sm font-black text-emerald-800">Pesan WhatsApp konfirmasi pelanggan siap dikirim.</p>
                <a href="{{ session('wa_url') }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition">
                    Buka WhatsApp
                </a>
            </div>
        @endif

        @if($requestServices->count() > 0)
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-8 py-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gray-50/30">
                <div>
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        Request Layanan Aktif
                    </h3>
                    <p class="mt-1 text-sm font-semibold text-gray-500">Daftar request dibuat seperti menu pesanan: admin cukup cek pembayaran, lihat metode penanganan, lalu assign teknisi bila sudah siap.</p>
                </div>
                <span class="inline-flex w-fit px-4 py-2 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest">
                    {{ $requestServices->count() }} Request Aktif
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Layanan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Estimasi</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($requestServices as $rs)
                        @php
                            $requestLayananLabel = match ((string) ($rs->service_jenis_layanan ?? 'service')) {
                                'refill' => 'Refill APAR',
                                default => 'Service APAR',
                            };
                            $requestDetailLabel = $rs->service_jenis_layanan === 'refill'
                                ? ($rs->serviceJenisRefill?->nama_label ?? $rs->service_jenis_apar ?? 'Refill')
                                : ($rs->servicePaket?->nama ?? 'Paket Service');
                        @endphp
                        <tr class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <p class="text-xs font-bold text-gray-900">{{ optional($rs->tanggal)->format('d M Y') }}</p>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">REQ-{{ $rs->id }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-black text-gray-900">{{ $rs->pelanggan?->nama ?? '-' }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $rs->pelanggan?->no_wa ?? '-' }}</p>
                                <p class="text-xs font-semibold text-gray-500 mt-2 max-w-[260px] line-clamp-2">{{ $rs->pelanggan?->alamat ?? '-' }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest">
                                    {{ $requestLayananLabel }}
                                </span>
                                <p class="mt-3 text-sm font-black text-gray-900">{{ $requestDetailLabel }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500">{{ $rs->service_ukuran_apar ?? $rs->service_jenis_apar ?? '-' }} - {{ $rs->service_jumlah_unit ?? 0 }} unit</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500">Metode: {{ $rs->service_metode_penanganan ?? '-' }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-black text-gray-900">Rp {{ number_format((float) ($rs->service_estimasi_biaya ?? 0), 0, ',', '.') }}</p>
                                @if($rs->service_total_kg)
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ rtrim(rtrim(number_format((float) $rs->service_total_kg, 2, ',', '.'), '0'), ',') }} Kg kebutuhan</p>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $rs->publicStatusClasses() }}">
                                    {{ $rs->publicStatusLabel() }}
                                </span>
                                <p class="mt-2 text-xs font-semibold text-gray-500">{{ $rs->isPaymentConfirmed() ? 'Pembayaran sudah masuk' : 'Menunggu pembayaran pelanggan' }}</p>
                                @if($rs->teknisi)
                                    <p class="mt-2 text-xs font-semibold text-emerald-700">Teknisi: {{ $rs->teknisi->name }}</p>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($rs->service_foto)
                                        <a href="{{ asset('storage/' . ltrim($rs->service_foto, '/')) }}" target="_blank" class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 transition">
                                            Foto
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.pesanan.destroy', $rs) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin ingin menghapus request layanan ini?')" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition" title="Hapus Request">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                    <button type="button" @click="openRequestModal({{ $rs->id }})" class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-gray-200 bg-white text-gray-700 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition">
                                        Kelola
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @foreach($requestServices as $rs)
        @php
            $requestLayananLabel = match ((string) ($rs->service_jenis_layanan ?? 'service')) {
                'refill' => 'Refill APAR',
                default => 'Service APAR',
            };
            $requestDetailLabel = $rs->service_jenis_layanan === 'refill'
                ? ($rs->serviceJenisRefill?->nama_label ?? $rs->service_jenis_apar ?? 'Refill')
                : ($rs->servicePaket?->nama ?? 'Paket Service');
        @endphp
        <div
            x-show="requestModalOpen && selectedRequestId === {{ $rs->id }}"
            x-cloak
            class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
        >
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="closeRequestModal()"></div>
            <div
                x-show="requestModalOpen && selectedRequestId === {{ $rs->id }}"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-gray-200/60"
            >
                <div class="px-8 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Request Layanan</p>
                        <h3 class="mt-1 text-xl font-black text-gray-900">REQ-{{ $rs->id }} - {{ $rs->pelanggan?->nama ?? '-' }}</h3>
                        <p class="mt-1 text-sm font-semibold text-gray-500">{{ $requestLayananLabel }} - {{ $requestDetailLabel }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <form action="{{ route('admin.pesanan.destroy', $rs) }}" method="POST" class="inline-flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Yakin ingin menghapus request layanan ini?')" class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 hover:bg-red-100 transition flex items-center justify-center" title="Hapus Request">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </form>
                        <button type="button" @click="closeRequestModal()" class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-500 hover:text-gray-900 hover:bg-gray-200 transition flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-6 space-y-3">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Pelanggan</p>
                            <div>
                                <p class="text-sm font-black text-gray-900">{{ $rs->pelanggan?->nama ?? '-' }}</p>
                                <p class="text-xs font-semibold text-gray-500 mt-1">{{ $rs->pelanggan?->no_wa ?? '-' }}</p>
                            </div>
                            <p class="text-xs font-semibold text-gray-600 leading-relaxed">{{ $rs->pelanggan?->alamat ?? '-' }}</p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-6 space-y-3">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Layanan</p>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Kategori</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $requestLayananLabel }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Unit</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $rs->service_jumlah_unit ?? 0 }} unit</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Ukuran</p>
                                    <p class="mt-1 font-semibold text-gray-700">{{ $rs->service_ukuran_apar ?? $rs->service_jenis_apar ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Metode</p>
                                    <p class="mt-1 font-semibold text-gray-700">{{ $rs->service_metode_penanganan ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Estimasi</p>
                                    <p class="mt-1 font-black text-blue-700">Rp {{ number_format((float) ($rs->service_estimasi_biaya ?? 0), 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Status</p>
                                    <p class="mt-1 font-semibold text-gray-700">{{ $rs->publicStatusLabel() }}</p>
                                </div>
                            </div>
                            @if($rs->service_keluhan || $rs->keterangan)
                                <div class="pt-2">
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Catatan Pelanggan</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-600 leading-relaxed">{{ $rs->service_keluhan ?: $rs->keterangan }}</p>
                                </div>
                            @endif
                            @if($rs->service_foto)
                                <a href="{{ asset('storage/' . ltrim($rs->service_foto, '/')) }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-black text-blue-700 hover:text-blue-900">
                                    Lihat Foto Lampiran
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-6 space-y-4">
                            <div>
                                <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Status Pembayaran</p>
                                <p class="mt-1 text-sm font-semibold text-amber-800">Alur layanan sekarang mengikuti pesanan biasa: pembayaran dulu, baru diproses.</p>
                            </div>
                            <div class="rounded-xl bg-white/90 border border-amber-200 px-4 py-4">
                                <p class="text-xs font-black uppercase tracking-widest text-amber-700">Ringkasan</p>
                                <p class="mt-2 text-sm font-semibold text-gray-700">Status pelanggan: <span class="font-black">{{ $rs->publicStatusLabel() }}</span></p>
                                <p class="mt-2 text-sm font-semibold text-gray-700">Pembayaran: <span class="font-black">{{ $rs->isPaymentConfirmed() ? 'Sudah dikonfirmasi' : 'Belum ada bukti pembayaran' }}</span></p>
                                <p class="mt-2 text-sm font-semibold text-gray-700">Metode penanganan: <span class="font-black">{{ $rs->trackingMethodLabel() }}</span></p>
                                @if($rs->service_admin_catatan)
                                    <p class="mt-3 text-sm font-semibold text-gray-600 leading-relaxed">{{ $rs->service_admin_catatan }}</p>
                                @endif
                            </div>
                            @if($rs->bukti_pembayaran)
                                <a href="{{ asset('storage/' . ltrim($rs->bukti_pembayaran, '/')) }}" target="_blank" class="inline-flex items-center justify-center w-full py-3 rounded-xl bg-amber-600 text-white text-xs font-black uppercase tracking-widest hover:bg-amber-700 transition">
                                    Lihat Bukti Pembayaran
                                </a>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 space-y-4">
                            <div>
                                <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Tindak Lanjut Admin</p>
                                <p class="mt-1 text-sm font-semibold text-emerald-800">Admin cukup lanjutkan langkah yang relevan, tanpa dropdown status panjang.</p>
                            </div>
                            @if(!$rs->isPaymentConfirmed())
                                <div class="rounded-xl bg-white/90 border border-emerald-200 px-4 py-4 text-sm font-semibold text-emerald-900 leading-relaxed">
                                    Pesanan ini masih menunggu pembayaran pelanggan. Setelah bukti pembayaran masuk, request akan siap dijadwalkan untuk {{ strtolower($rs->trackingMethodLabel()) }}.
                                </div>
                            @elseif(!$rs->teknisi_id)
                                <form action="{{ route('admin.pesanan.assign-teknisi', $rs) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="text-[10px] font-black text-emerald-700 uppercase tracking-widest block mb-2">Pilih Teknisi</label>
                                        <select name="teknisi_id" class="w-full px-4 py-3 rounded-xl bg-white border border-emerald-200 text-sm font-bold text-emerald-900 focus:outline-none focus:border-emerald-400" required>
                                            <option value="">-- Pilih Teknisi --</option>
                                            @foreach($teknisis as $tek)
                                                <option value="{{ $tek->id }}" @selected($rs->teknisi_id == $tek->id)>{{ $tek->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition">
                                        Assign Teknisi
                                    </button>
                                </form>
                            @else
                                <div class="rounded-xl bg-white/90 border border-emerald-200 px-4 py-4 text-sm font-semibold text-emerald-900 leading-relaxed">
                                    Request ini sudah ditugaskan ke <span class="font-black">{{ $rs->teknisi->name }}</span>.
                                    @if($rs->status === 'dikerjakan teknisi')
                                        Teknisi sedang mengerjakan unit pelanggan.
                                    @else
                                        Menunggu progres lanjutan dari teknisi.
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Tindak Lanjut Admin dari Teknisi --}}
        @if(isset($selesaiTeknisi) && $selesaiTeknisi->count() > 0)
        <div class="bg-emerald-50 rounded-[2.5rem] border border-emerald-200 shadow-sm overflow-hidden mb-8">
            <div class="px-8 py-5 border-b border-emerald-200/60 flex justify-between items-center bg-emerald-100/50">
                <h3 class="text-lg font-black text-emerald-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tindak Lanjut Admin - Selesai oleh Teknisi
                </h3>
                <span class="px-3 py-1.5 rounded-full bg-emerald-200 text-emerald-800 text-[10px] font-black uppercase tracking-widest">
                    {{ $selesaiTeknisi->count() }} Tugas
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-emerald-100/30">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-emerald-700 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-emerald-700 uppercase tracking-widest">Alamat</th>
                            <th class="px-6 py-4 text-[10px] font-black text-emerald-700 uppercase tracking-widest">Detail Pekerjaan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-emerald-700 uppercase tracking-widest">Teknisi</th>
                            <th class="px-6 py-4 text-[10px] font-black text-emerald-700 uppercase tracking-widest">Tanggal Selesai</th>
                            <th class="px-6 py-4 text-[10px] font-black text-emerald-700 uppercase tracking-widest">Catatan Teknisi</th>
                            <th class="px-6 py-4 text-[10px] font-black text-emerald-700 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100/50">
                        @foreach($selesaiTeknisi as $st)
                        <tr class="hover:bg-emerald-100/40 transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-black text-emerald-900">{{ $st->pelanggan?->nama ?? '-' }}</p>
                                <p class="text-xs text-emerald-700 mt-1">{{ $st->pelanggan?->no_wa ?? '-' }}</p>
                                <p class="text-[10px] font-black text-emerald-600 mt-1">REQ-{{ $st->id }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-emerald-800 max-w-[220px]">{{ $st->pelanggan?->alamat ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs font-semibold text-emerald-800 max-w-[240px]">
                                @php
                                    $selesaiLayananLabel = match ((string) ($st->service_jenis_layanan ?? 'service')) {
                                        'refill' => 'Isi Ulang / Refill',
                                        default => 'Perawatan / Service',
                                    };
                                @endphp
                                {{ $selesaiLayananLabel }} - {{ $st->service_jenis_apar ?? '-' }} - {{ $st->service_jumlah_unit ?? 0 }} unit
                                <p class="mt-1">{{ $st->service_keluhan ?: ($st->keterangan ?? '-') }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-black text-emerald-900">{{ $st->teknisi?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs font-bold text-emerald-900">{{ optional($st->teknisi_selesai_at)->format('d M Y H:i') ?: '-' }}</td>
                            <td class="px-6 py-4 text-xs text-emerald-800 max-w-[220px]">{{ $st->teknisi_catatan ?: '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex flex-col gap-2 w-52">
                                    <form action="{{ route('admin.pesanan.konfirmasi-pelanggan', $st) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-[#25D366] text-white text-xs font-black uppercase tracking-widest hover:bg-[#1ebe5e] transition">
                                            Konfirmasi ke Pelanggan
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.pesanan.selesai-final', $st) }}" method="POST" onsubmit="return confirm('Selesaikan final request ini?')">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-emerald-700 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-800 transition">
                                            Selesaikan Final
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Service Log Table --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 flex flex-col md:flex-row gap-4 bg-gray-50/30 border-b border-gray-50">
                <div class="flex-grow flex items-center px-6 py-4 bg-white rounded-2xl border border-gray-100 gap-4 shadow-sm">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" x-model="search" placeholder="Cari unit atau paket..." class="w-full border-none focus:ring-0 text-sm font-medium placeholder:text-gray-300">
                </div>
                <button onclick="window.print()" class="px-6 py-4 bg-white text-gray-600 font-bold rounded-2xl border border-gray-100 flex items-center gap-2 hover:bg-gray-50 transition shadow-sm no-print">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak Laporan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit / Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Paket</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Estimasi Peralatan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Biaya</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($serviceLogs as $s)
                            <tr class="hover:bg-gray-50/30 transition-colors group"
                                x-show="search === '' || '{{ strtolower($s->display_unit_label ?? '') }} {{ strtolower($s->jenis_service ?? '') }} {{ strtolower($s->display_customer_name ?? '') }}'.includes(search.toLowerCase())">
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-gray-900">{{ optional($s->tgl_service)->format('d M Y') }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-red-700 transition">{{ $s->display_unit_label }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $s->display_customer_name }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-red-700">{{ $s->servicePaket?->nama ?? $s->jenis_service }}</p>
                                    @if($s->servicePaket?->rincian_layanan)
                                        <p class="text-[10px] text-gray-500 mt-1 line-clamp-2">{{ Str::limit($s->servicePaket->rincian_layanan, 60) }}</p>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    @if($s->estimasi_peralatan)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($s->estimasi_peralatan as $est)
                                            <span class="inline-flex flex-col px-2 py-1 bg-gray-100 rounded-lg text-[10px] font-bold text-gray-600">
                                                {{ $est['nama'] }}
                                                <span class="text-gray-400 font-medium">×{{ $est['jumlah'] }}</span>
                                            </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $konfClass = match($s->status_konfirmasi) {
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'reported' => 'bg-blue-100 text-blue-700',
                                            'confirmed' => 'bg-emerald-100 text-emerald-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                        $konfLabel = match($s->status_konfirmasi) {
                                            'pending' => 'Pending',
                                            'reported' => 'Dilaporkan',
                                            'confirmed' => 'Selesai',
                                            'rejected' => 'Ditolak',
                                            default => 'Unknown',
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $konfClass }}">
                                        {{ $konfLabel }}
                                    </span>
                                    @if($s->tgl_selesai_admin)
                                        <p class="text-[10px] text-gray-400 mt-1">{{ $s->tgl_selesai_admin->format('d M Y') }}</p>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <p class="text-xs font-bold text-gray-900">Rp {{ number_format($s->biaya, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($s->status_konfirmasi === 'pending')
                                            <a href="{{ route('admin.service.edit', $s) }}" class="p-3 bg-white text-gray-400 hover:text-blue-600 rounded-xl border border-gray-100 hover:border-blue-100 hover:shadow-lg transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.service.destroy', $s) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-3 bg-white text-gray-400 hover:text-red-600 rounded-xl border border-gray-100 hover:border-red-100 hover:shadow-lg transition-all" onclick="return confirm('Yakin ingin menghapus?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-16 text-center">
                                    <p class="text-gray-400 font-medium">Belum ada data service log.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- INPUT SERVICE MODAL --}}
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
                class="relative w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-gray-200/60"
            >
                {{-- HEADER --}}
                <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-8 py-5 flex items-center justify-between gap-6 shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-600/30 border border-red-500/30 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tight leading-tight">Input Service APAR</h3>
                            <p class="text-sm text-white/50 font-medium mt-0.5">Pilih paket service standar A–C</p>
                        </div>
                    </div>
                    <button type="button" @click="openModal = false" class="w-10 h-10 rounded-2xl bg-white/10 text-white/60 hover:text-white hover:bg-white/20 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-8" x-data="{
                    selectedPaketId: '',
                    pelangganId: '',
                    unitId: '',
                    pelanggans: @js($pelanggans->map(fn($p) => [
                        'id' => $p->id,
                        'nama' => $p->nama,
                        'wa' => $p->no_wa,
                        'alamat' => $p->alamat_maps ?? $p->alamat ?? '-',
                        'kota' => $p->alamat_kota ?? '-',
                        'provinsi' => $p->alamat_provinsi ?? '-',
                        'lat' => $p->alamat_lat,
                        'lng' => $p->alamat_lng,
                        'units' => $p->units->map(fn($u) => [
                            'id' => $u->id,
                            'seri' => $u->no_seri,
                            'merek' => $u->produk->merek ?? '-',
                            'kapasitas' => $u->produk->kapasitas ?? '-',
                            'jenis' => $u->produk->jenisApar->nama ?? '-',
                            'expired' => $u->tgl_expired ? $u->tgl_expired->format('d M Y') : '-',
                        ])->values(),
                    ])->values()),
                    pakets: @js($servicePakets->map(fn ($p) => [
                        'id' => $p->id,
                        'nama' => $p->nama,
                        'label' => $p->label,
                        'harga' => $p->harga,
                        'rincian' => $p->rincian_layanan,
                        'peralatans' => $p->peralatans->map(fn ($pr) => ['id' => $pr->id, 'nama' => $pr->nama, 'jumlah' => $pr->pivot->jumlah_estimasi])->values(),
                    ])->values()),
                    get unitOptions() {
                        let found = this.pelanggans.find((p) => Number(p.id) === Number(this.pelangganId));
                        return found ? found.units : [];
                    },
                    selectedPaket() {
                        return this.pakets.find((p) => String(p.id) === String(this.selectedPaketId))
                    },
                    selectedPelanggan() {
                        return this.pelanggans.find((p) => Number(p.id) === Number(this.pelangganId))
                    },
                }">
                    <form action="{{ route('admin.service.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                {{-- LANGKAH 1: PILIH PELANGGAN --}}
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Langkah 1 — Pilih Pelanggan <span class="text-red-500">*</span></label>
                                    <select x-model="pelangganId" @change="unitId = ''; selectedPaketId = ''"
                                        class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition text-sm">
                                        <option value="">— Pilih pelanggan —</option>
                                        <template x-for="p in pelanggans" :key="p.id">
                                            <option :value="p.id" x-text="p.nama + ' — ' + (p.wa || '-')"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- INFO PELANGGAN + PETA (muncul setelah pilih pelanggan) --}}
                                <template x-if="pelangganId">
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50/50 overflow-hidden">
                                        <div class="px-5 py-4 bg-gray-100/60 border-b border-gray-200/60 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <p class="text-xs font-black text-gray-700 uppercase tracking-widest">Langkah 2 — Info & Lokasi Pelanggan</p>
                                        </div>
                                        <div class="p-5 space-y-3">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama</span>
                                                <span class="text-sm font-black text-gray-900" x-text="selectedPelanggan()?.nama || '-'"></span>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No. WA</span>
                                                <span class="text-sm font-bold text-gray-900" x-text="selectedPelanggan()?.wa || '-'"></span>
                                            </div>
                                            <div class="flex items-start gap-2">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest shrink-0">Alamat</span>
                                                <div class="text-right">
                                                    <p class="text-xs font-semibold text-gray-700" x-text="selectedPelanggan()?.alamat || '-'"></p>
                                                    <p class="text-[10px] font-medium text-gray-400" x-text="(selectedPelanggan()?.kota || '-') + ', ' + (selectedPelanggan()?.provinsi || '-')"></p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- PETA PELANGGAN --}}
                                        <div class="px-5 pb-5">
                                            <template x-if="selectedPelanggan()?.lat && selectedPelanggan()?.lng">
                                                <div class="rounded-xl overflow-hidden border border-gray-200 shadow-inner">
                                                    <iframe
                                                        width="100%" height="160" frameborder="0" scrolling="yes" marginheight="0" marginwidth="0"
                                                        :src="'https://www.openstreetmap.org/export/embed.html?bbox=' + ((selectedPelanggan()?.lng * 1) - 0.005) + ',' + ((selectedPelanggan()?.lat * 1) - 0.005) + ',' + ((selectedPelanggan()?.lng * 1) + 0.005) + ',' + ((selectedPelanggan()?.lat * 1) + 0.005) + '&layer=mapnik&marker=' + (selectedPelanggan()?.lat * 1) + ',' + (selectedPelanggan()?.lng * 1)"
                                                        style="width:100%;height:160px;display:block;">
                                                    </iframe>
                                                </div>
                                            </template>
                                            <template x-if="!selectedPelanggan()?.lat || !selectedPelanggan()?.lng">
                                                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-100/50 h-[160px] flex items-center justify-center">
                                                    <p class="text-xs text-gray-400 font-semibold">Tanpa koordinat GPS</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- LANGKAH 3: PILIH UNIT (muncul setelah pilih pelanggan) --}}
                                <template x-if="pelangganId">
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Langkah 3 — Pilih Unit APAR Milik Pelanggan <span class="text-red-500">*</span></label>
                                        <select name="unit_apar_id" x-model="unitId" @change="selectedPaketId = ''" required
                                            class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition text-sm">
                                            <option value="">— Pilih unit APAR —</option>
                                            <template x-for="u in unitOptions" :key="u.id">
                                                <option :value="u.id" x-text="u.seri"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                {{-- INFO UNIT (muncul setelah pilih unit) --}}
                                <template x-if="unitId">
                                    @php
                                        $unitAll = $units->map(fn($u) => [
                                            'id' => $u->id,
                                            'pelangganId' => $u->pelanggan->id ?? 0,
                                            'merek' => $u->produk->merek ?? '-',
                                            'kapasitas' => $u->produk->kapasitas ?? '-',
                                            'jenis' => $u->produk->jenisApar->nama ?? '-',
                                            'expired' => optional($u->tgl_expired)->format('d M Y'),
                                        ]);
                                    @endphp
                                    <div>
                                        <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 space-y-2">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No. Seri</span>
                                                <span class="text-sm font-black text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.seri || '-')"></span>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Merek</span>
                                                <span class="text-sm font-black text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.merek || '-')"></span>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ukuran</span>
                                                <span class="text-sm font-bold text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.kapasitas || '-')"></span>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis</span>
                                                <span class="text-sm font-bold text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.jenis || '-')"></span>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Expired</span>
                                                <span class="text-sm font-black text-red-700" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.expired || '-')"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="space-y-6">
                                {{-- LANGKAH 4: PILIH PAKET --}}
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Langkah 4 — Pilih Paket Service <span class="text-red-500">*</span></label>
                                    <select name="service_paket_id" x-model="selectedPaketId" required
                                        class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition text-sm">
                                        <option value="">— Pilih paket service —</option>
                                        <template x-for="p in pakets" :key="p.id">
                                            <option :value="p.id" x-text="(p.label ? p.label + ' - ' : '') + p.nama + ' — Rp ' + Number(p.harga).toLocaleString('id-ID')"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- PAKET INFO --}}
                                <template x-if="selectedPaket()">
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50/50 overflow-hidden">
                                        <div class="px-5 py-4 bg-slate-800/80 border-b border-slate-700">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-black text-white uppercase tracking-widest" x-text="selectedPaket()?.nama"></p>
                                                <p class="text-sm font-black text-red-400" x-text="'Rp ' + Number(selectedPaket()?.harga).toLocaleString('id-ID')"></p>
                                            </div>
                                        </div>
                                        <div class="p-5 space-y-4">
                                            <div>
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rincian Layanan</p>
                                                <p class="text-sm font-semibold text-gray-700" x-text="selectedPaket()?.rincian"></p>
                                            </div>
                                            <template x-if="selectedPaket()?.peralatans?.length > 0">
                                                <div>
                                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Estimasi Peralatan</p>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <template x-for="per in selectedPaket()?.peralatans" :key="per.id">
                                                            <span class="inline-flex flex-col px-2.5 py-1 bg-gray-100 rounded-lg text-[10px] font-bold text-gray-700">
                                                                <span x-text="per.nama"></span>
                                                                <span class="text-gray-400 font-medium">×<span x-text="per.jumlah"></span></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="!selectedPaket()?.peralatans?.length">
                                                <p class="text-[10px] text-gray-400 italic">Paket ini tidak menggunakan peralatan.</p>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- WARNING --}}
                                <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div>
                                            <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest mb-1">Perhatian</p>
                                            <p class="text-xs font-semibold text-amber-800">Stok peralatan <strong>tidak berkurang</strong> saat service diinput. Stok akan dikurangi setelah teknisi submit laporan dan admin mengonfirmasi selesai.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FORM BAWAH --}}
                        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Service <span class="text-red-500">*</span></label>
                                <input type="date" name="tgl_service" value="{{ old('tgl_service', date('Y-m-d')) }}" required
                                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                                <x-input-error :messages="$errors->get('tgl_service')" class="mt-2" />
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Keterangan / Catatan</label>
                                <textarea name="keterangan" rows="3"
                                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 placeholder:text-gray-300 transition text-sm resize-none">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        {{-- TOMBOL --}}
                        <div class="mt-8 flex justify-end gap-4 pt-4 border-t border-gray-100">
                            <button type="button" @click="openModal = false" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</button>
                            <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                                Simpan Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
