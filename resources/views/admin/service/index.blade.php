<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Service APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola data service APAR sesuai alur admin dan teknisi.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-service-modal'))" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Data Service
            </button>
        </div>
    </x-slot>

    <div
        class="space-y-8"
        x-data="{
            search: '',
            openModal: {{ $errors->any() ? 'true' : 'false' }},
        }"
        @open-service-modal.window="openModal = true"
    >
        {{-- Stats --}}
        @php
            $menungguBayar = $requestServices->filter(fn($rs) => !$rs->isPaymentConfirmed())->count();
            $siapAssign = $requestServices->filter(fn($rs) => $rs->isPaymentConfirmed() && !$rs->teknisi_id)->count();
            $sedangDikerjakan = $requestServices->filter(fn($rs) => in_array($rs->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi']))->count();
            $tungguKonfirmasi = $requestServices->filter(fn($rs) => in_array($rs->status, ['selesai oleh teknisi', 'dikonfirmasi admin']))->count();
            $totalDataService = $serviceLogs->count() + $requestServices->count();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Data Service</p>
                <p class="text-4xl font-black text-gray-900">{{ $totalDataService }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Input Admin</p>
                <p class="text-4xl font-black text-emerald-700">{{ $serviceLogs->count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Dari Pelanggan</p>
                <p class="text-4xl font-black text-purple-700">{{ $requestServices->count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tunggu Konfirmasi</p>
                <p class="text-4xl font-black text-emerald-700">{{ $tungguKonfirmasi }}</p>
            </div>
        </div>

        @if(session('wa_url'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 flex items-center justify-between gap-3">
                <p class="text-sm font-black text-emerald-800">Pesan WhatsApp konfirmasi pelanggan siap dikirim.</p>
                <a href="{{ session('wa_url') }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition">
                    Buka WhatsApp
                </a>
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Data Service APAR</h3>
                <p class="mt-1 text-xs font-semibold text-gray-500">Data service yang ditambah atau diubah admin.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit APAR</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Service</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($serviceLogs as $service)
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-xs font-bold text-gray-900">{{ optional($service->tgl_service)->format('d M Y') }}</p>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">SRV-{{ $service->id }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $service->unitApar?->pelanggan?->nama ?? $service->pesanan?->pelanggan?->nama ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-gray-900">{{ $service->unitApar?->no_seri ?? '-' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $service->unitApar?->produk?->nama ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $service->servicePaket?->nama ?? $service->jenis_service }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">Rp {{ number_format((float) ($service->biaya ?? 0), 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $service->status_konfirmasi === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : ($service->status_konfirmasi === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $service->status_konfirmasi === 'confirmed' ? 'Selesai' : ($service->status_konfirmasi === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.service.edit', $service) }}" class="px-3 py-2 rounded-xl border border-blue-100 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 transition">Edit</a>
                                        @if($service->status_konfirmasi !== 'confirmed')
                                            <form action="{{ route('admin.service.destroy', $service) }}" method="POST" onsubmit="return confirm('Hapus data service ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 rounded-xl border border-red-100 bg-red-50 text-red-700 text-[10px] font-black uppercase tracking-widest hover:bg-red-100 transition">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data service APAR dari admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($requestServices->count() > 0)
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 space-y-4 bg-gray-50/30 border-b border-gray-50">
                <h3 class="text-lg font-black text-gray-900">Data Service dari Pelanggan</h3>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest bg-gray-900 text-white border-gray-900">Semua</button>
                    <button type="button" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest bg-blue-50 text-blue-700 border-blue-200">Menunggu Bayar</button>
                    <button type="button" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest bg-amber-50 text-amber-700 border-amber-200">Siap Tugas</button>
                    <button type="button" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest bg-purple-50 text-purple-700 border-purple-200">Dikerjakan</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Layanan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($requestServices as $rs)
                        @php
                            $requestLayananLabel = match ((string) ($rs->service_jenis_layanan ?? 'service')) {
                                'refill' => 'Refil APAR',
                                default => 'Service APAR',
                            };
                            $requestDetailLabel = $rs->service_jenis_layanan === 'refill'
                                ? ($rs->serviceJenisRefill?->nama_label ?? $rs->service_jenis_apar ?? 'Refill')
                                : ($rs->servicePaket?->nama ?? 'Paket Service');
                            $isPaid = $rs->isPaymentConfirmed();
                            $canAssign = $isPaid && !$rs->teknisi_id;
                            $s = $rs->status;
                            $statusBadge = match(true) {
                                $s === 'selesai final' || $s === 'selesai' => ['bg-emerald-100 text-emerald-700', 'SELESAI'],
                                $s === 'dikonfirmasi admin' => ['bg-emerald-100 text-emerald-700', 'SIAP FINAL'],
                                $s === 'selesai oleh teknisi' => ['bg-emerald-100 text-emerald-700', 'SELESAI TEKNISI'],
                                $s === 'dikerjakan teknisi' => ['bg-indigo-100 text-indigo-700', 'DIPROSES TEKNISI'],
                                $s === 'ditugaskan ke teknisi' => ['bg-purple-100 text-purple-700', 'DITUGASKAN'],
                                $rs->teknisi_id => ['bg-blue-100 text-blue-700', 'SIAP KERJA'],
                                $isPaid => ['bg-amber-100 text-amber-700', 'SUDAH BAYAR'],
                                default => ['bg-gray-100 text-gray-600', 'MENUNGGU'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <p class="text-xs font-bold text-gray-900">{{ optional($rs->tanggal)->format('d M Y') }}</p>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">SRV-{{ $rs->id }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-black text-gray-900">{{ $rs->pelanggan?->nama ?? '-' }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $rs->pelanggan?->no_wa ?? '-' }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest">
                                    {{ $requestLayananLabel }}
                                </span>
                                <p class="mt-2 text-sm font-black text-gray-900">{{ $requestDetailLabel }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500">{{ $rs->service_ukuran_apar ?? $rs->service_jenis_apar ?? '-' }} - {{ $rs->service_jumlah_unit ?? 0 }} unit</p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-black text-gray-900">Rp {{ number_format((float) ($rs->service_estimasi_biaya ?? 0), 0, ',', '.') }}</p>
                                @if($rs->service_total_kg)
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ rtrim(rtrim(number_format((float) $rs->service_total_kg, 2, ',', '.'), '0'), ',') }} Kg</p>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusBadge[0] }}">
                                    {{ $statusBadge[1] }}
                                </span>
                                @if($rs->teknisi)
                                    <p class="mt-2 text-[10px] font-bold text-red-600">{{ $rs->teknisi->name }}</p>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    @if($rs->bukti_pembayaran)
                                        <a href="{{ asset('storage/' . ltrim($rs->bukti_pembayaran, '/')) }}" target="_blank" class="px-3 py-2 rounded-xl border border-sky-200 bg-sky-50 text-sky-700 text-[10px] font-black uppercase tracking-widest hover:bg-sky-100 transition shadow-sm">
                                            Bukti Bayar
                                        </a>
                                    @endif
                                    @if($rs->service_foto)
                                        <a href="{{ asset('storage/' . ltrim($rs->service_foto, '/')) }}" target="_blank" class="px-3 py-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 transition shadow-sm">
                                            Foto
                                        </a>
                                    @endif
                                    @if(in_array($rs->status, ['selesai oleh teknisi', 'dikonfirmasi admin']))
                                        <div class="flex flex-col items-end gap-2">
                                            <p class="text-[10px] font-bold text-emerald-600 mb-1">Teknisi sudah selesai</p>
                                            <form action="{{ route('admin.pesanan.selesai-final', $rs) }}" method="POST" onsubmit="return confirm('Selesaikan final service ini?')">
                                                @csrf
                                                <button type="submit" class="px-4 py-2.5 bg-emerald-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Final
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($canAssign)
                                        <div class="relative" x-data="{ show: false }">
                                            <button type="button" @click="show = !show" class="px-3 py-2 bg-red-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-red-700 transition shadow-sm flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Tugas
                                            </button>
                                            <div x-show="show" @click.away="show = false" x-cloak class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 p-4">
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tugaskan Teknisi</p>
                                                <form action="{{ route('admin.pesanan.assign-teknisi', $rs) }}" method="POST" class="space-y-3">
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
                                            </div>
                                        </div>
                                    @elseif($rs->teknisi_id)
                                        <div class="px-3 py-1.5 bg-red-50 rounded-xl border border-red-100 flex flex-col items-end mr-1">
                                            <span class="text-[8px] font-black uppercase tracking-widest text-red-400">Ditugaskan</span>
                                            <span class="text-[10px] font-bold text-red-700 whitespace-nowrap">{{ $rs->teknisi->name }}</span>
                                        </div>
                                    @endif
                                    <form action="{{ route('admin.pesanan.destroy', $rs) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin ingin menghapus?')" class="p-2.5 bg-white text-gray-400 hover:text-red-600 rounded-xl border border-gray-100 hover:border-red-100 hover:shadow-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                    <button type="button" onclick="openDetailModal({{ $rs->id }})" class="px-3 py-2 bg-white text-gray-600 border border-gray-200 rounded-xl text-[10px] font-black uppercase hover:bg-gray-50 transition shadow-sm">
                                        Detail
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

        {{-- Detail Modal --}}
        <div id="detail-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" onclick="closeDetailModal()"></div>
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-gray-100 z-10">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Detail Data Service</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5" id="detail-modal-subtitle"></p>
                    </div>
                    <button onclick="closeDetailModal()" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5" id="detail-modal-content"></div>
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
                <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-8 py-5 flex items-center justify-between gap-6 shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-600/30 border border-red-500/30 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tight leading-tight">Input Service APAR</h3>
                            <p class="text-sm text-white/50 font-medium mt-0.5">Isi data service APAR lalu simpan</p>
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
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Langkah 1 - Pilih Pelanggan <span class="text-red-500">*</span></label>
                                    <select x-model="pelangganId" @change="unitId = ''; selectedPaketId = ''"
                                        class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition text-sm">
                                        <option value="">-- Pilih pelanggan --</option>
                                        <template x-for="p in pelanggans" :key="p.id">
                                            <option :value="p.id" x-text="p.nama + ' - ' + (p.wa || '-')"></option>
                                        </template>
                                    </select>
                                </div>
                                <template x-if="pelangganId">
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50/50 overflow-hidden">
                                        <div class="px-5 py-4 bg-gray-100/60 border-b border-gray-200/60 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <p class="text-xs font-black text-gray-700 uppercase tracking-widest">Langkah 2 - Info & Lokasi Pelanggan</p>
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
                                        </div>
                                    </div>
                                </template>
                                <template x-if="pelangganId">
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Langkah 3 - Pilih Unit APAR <span class="text-red-500">*</span></label>
                                        <select name="unit_apar_id" x-model="unitId" @change="selectedPaketId = ''" required
                                            class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition text-sm">
                                            <option value="">-- Pilih unit APAR --</option>
                                            <template x-for="u in unitOptions" :key="u.id">
                                                <option :value="u.id" x-text="u.seri"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Langkah 4 - Pilih Paket Service <span class="text-red-500">*</span></label>
                                    <select name="service_paket_id" x-model="selectedPaketId" required
                                        class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition text-sm">
                                        <option value="">-- Pilih paket service --</option>
                                        <template x-for="p in pakets" :key="p.id">
                                            <option :value="p.id" x-text="(p.label ? p.label + ' - ' : '') + p.nama + ' - Rp ' + Number(p.harga).toLocaleString('id-ID')"></option>
                                        </template>
                                    </select>
                                </div>
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
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
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

    @php
    $serviceDetailData = $requestServices->map(function($rs) {
        return [
            'id' => $rs->id,
            'pelanggan' => $rs->pelanggan?->nama ?? '-',
            'no_wa' => $rs->pelanggan?->no_wa ?? '-',
            'alamat' => $rs->pelanggan?->alamat ?? '-',
            'jenis' => $rs->service_jenis_layanan === 'refill'
                ? ($rs->serviceJenisRefill?->nama_label ?? 'Refill')
                : ($rs->servicePaket?->nama ?? 'Paket Service'),
            'estimasi' => number_format((float) ($rs->service_estimasi_biaya ?? 0), 0, ',', '.'),
            'ukuran' => $rs->service_ukuran_apar ?? '-',
            'unit' => (int) ($rs->service_jumlah_unit ?? 0),
            'metode' => $rs->service_metode_penanganan ?? '-',
            'total_kg' => $rs->service_total_kg,
            'teknisi' => $rs->teknisi?->name ?? 'Belum',
            'catatan' => $rs->service_keluhan ?? '-',
            'status' => $rs->status,
            'is_paid' => $rs->isPaymentConfirmed(),
        ];
    })->toArray();
    @endphp

    <script>
    const serviceDetailData = @json($serviceDetailData);

    function openDetailModal(id) {
        const data = serviceDetailData.find(r => r.id === id);
        if (!data) return;

        document.getElementById('detail-modal-subtitle').textContent = 'SRV-' + id + ' - ' + data.pelanggan;

        const paidBadge = data.is_paid
            ? '<span class="inline-flex px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase">LUNAS</span>'
            : '<span class="inline-flex px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black uppercase">BELUM BAYAR</span>';

        const kgHtml = data.total_kg ? `<div><span class="text-[10px] font-black text-gray-400 uppercase">KG</span><p class="font-semibold">${data.total_kg} kg</p></div>` : '';

        document.getElementById('detail-modal-content').innerHTML = `
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pelanggan</p>
                    <p class="font-bold text-gray-900">${data.pelanggan}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">WhatsApp</p>
                    <p class="font-bold text-gray-900">${data.no_wa}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Alamat</p>
                    <p class="font-semibold text-gray-700">${data.alamat}</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Layanan</p>
                    <p class="font-black text-emerald-700">${data.jenis}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Estimasi</p>
                    <p class="font-black text-gray-900">Rp ${data.estimasi}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Ukuran/Unit</p>
                    <p class="font-semibold text-gray-900">${data.ukuran} - ${data.unit} unit</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Metode</p>
                    <p class="font-semibold text-gray-900">${data.metode}</p>
                </div>
                ${kgHtml}
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                    <div class="mt-1">${paidBadge}</div>
                </div>
            </div>
            ${data.catatan !== '-' ? `<div class="rounded-xl border border-amber-100 bg-amber-50 p-4"><span class="text-[10px] font-black text-amber-600 uppercase">Catatan</span><p class="mt-1 text-sm font-semibold whitespace-pre-line">${data.catatan}</p></div>` : ''}
            <div class="flex justify-center">
                <button type="button" onclick="closeDetailModal()" class="px-8 py-3 bg-gray-200 text-gray-700 font-black text-xs uppercase rounded-xl hover:bg-gray-300 transition">
                    Tutup
                </button>
            </div>
        `;

        document.getElementById('detail-modal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
    </script>
</x-app-layout>
