<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Service APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola transaksi service APAR dari pelanggan online maupun offline.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-service-modal'))" class="px-5 py-3 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-bold rounded-xl transition shadow-sm text-xs flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Input Service Offline
            </button>
        </div>
    </x-slot>

    @php
        $offlineServices = $requestServices->filter(fn ($service) => in_array((string) $service->sumber_pesanan, ['datang_langsung', 'offline'], true));
        $teknisiAktif = $requestServices->filter(fn ($service) => in_array((string) $service->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi'], true));
        $actionButtonBase = 'inline-flex items-center justify-center px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition shadow-sm';
        $actionButtonNeutral = $actionButtonBase . ' border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
        $actionButtonPrimary = $actionButtonBase . ' border-red-600 bg-red-600 text-white hover:bg-red-700 hover:border-red-700';
        $actionButtonProof = $actionButtonBase . ' border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100';
        $actionButtonDanger = $actionButtonBase . ' border-red-200 bg-white text-red-600 hover:bg-red-50';
        $actionButtonSuccess = $actionButtonBase . ' border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 hover:border-emerald-700';
        $actionButtonDisabled = $actionButtonBase . ' border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed';
        $servicePaketOptions = collect($servicePaketCatalog ?? [])->values();
        $serviceDetailData = $requestServices->map(function ($service) {
            return [
                'id' => $service->id,
                'pelanggan' => $service->pelanggan?->nama ?? '-',
                'no_wa' => $service->pelanggan?->no_wa ?? '-',
                'alamat' => $service->pelanggan?->alamat ?? '-',
                'transaksi' => $service->transactionDisplayName(),
                'waktu' => $service->displayTransactionDateTime(),
                'jenis' => $service->servicePaket?->nama ?? 'Service APAR',
                'estimasi' => number_format((float) ($service->service_estimasi_biaya ?? 0), 0, ',', '.'),
                'ukuran' => $service->service_ukuran_apar ?? '-',
                'unit' => (int) ($service->service_jumlah_unit ?? 0),
                'source' => in_array((string) $service->sumber_pesanan, ['datang_langsung', 'offline'], true) ? 'Offline' : 'Online',
                'teknisi' => $service->teknisi?->name ?? 'Belum ditugaskan',
                'catatan' => $service->catatan_admin ?: $service->service_admin_catatan ?: $service->service_keluhan ?: $service->keterangan ?: '-',
                'status' => $service->status,
                'is_paid' => $service->isPaymentConfirmed(),
                'line_items' => $service->servicePricingBreakdown(),
                'peralatan' => $service->servicePeralatanItems(),
            ];
        })->concat($serviceLogs->map(function ($s) {
            $pesanan = $s->pesanan;
            return [
                'id' => 'log-' . $s->id,
                'pelanggan' => $s->unitApar?->pelanggan?->nama ?? $pesanan?->pelanggan?->nama ?? '-',
                'no_wa' => $s->unitApar?->pelanggan?->no_wa ?? $pesanan?->pelanggan?->no_wa ?? '-',
                'alamat' => $s->unitApar?->pelanggan?->alamat ?? $pesanan?->pelanggan?->alamat ?? '-',
                'transaksi' => $s->transactionDisplayName(),
                'waktu' => $s->displayTransactionDateTime(),
                'jenis' => $s->servicePaket?->nama ?? $pesanan?->servicePaket?->nama ?? $s->jenis_service ?? 'Service APAR',
                'estimasi' => number_format((float) ($s->biaya ?? $pesanan?->payableTotal() ?? 0), 0, ',', '.'),
                'ukuran' => $s->unitApar?->produk?->kapasitas ?? $pesanan?->service_ukuran_apar ?? '-',
                'unit' => 1,
                'source' => $pesanan ? (in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline'], true) ? 'Offline' : 'Online') : 'Offline',
                'teknisi' => $pesanan?->teknisi?->name ?? 'Selesai',
                'catatan' => $s->keterangan ?: $pesanan?->catatan_admin ?: $pesanan?->service_keluhan ?: $pesanan?->keterangan ?: '-',
                'status' => $pesanan?->status ?? 'selesai final',
                'is_paid' => $pesanan ? $pesanan->isPaymentConfirmed() : true,
                'line_items' => $pesanan?->servicePricingBreakdown() ?? [],
                'peralatan' => $pesanan?->servicePeralatanItems() ?? ($s->effective_peralatan ?? []),
            ];
        }))->values();
    @endphp

    <div class="space-y-8" x-data="{ openModal: {{ $errors->any() ? 'true' : 'false' }} }" @open-service-modal.window="openModal = true">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Data Service</p>
                <p class="text-4xl font-black text-gray-900">{{ $serviceLogs->count() + $requestServices->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Offline</p>
                <p class="text-4xl font-black text-emerald-700">{{ $offlineServices->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Online</p>
                <p class="text-4xl font-black text-amber-700">{{ $requestServices->count() - $offlineServices->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Proses Teknisi</p>
                <p class="text-4xl font-black text-red-700">{{ $teknisiAktif->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Data Service dari Pelanggan</h3>
                <p class="mt-1 text-xs font-semibold text-gray-500">Permintaan service dari pelanggan online maupun input offline admin.</p>
            </div>
            <div class="responsive-table-wrap">
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
                        @forelse($requestServices as $service)
                            @php
                                $isOffline = in_array((string) $service->sumber_pesanan, ['datang_langsung', 'offline'], true);
                                $canAssign = $service->isPaymentConfirmed() && !$service->teknisi_id;
                                $statusBadge = match ((string) $service->status) {
                                    'selesai final', 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
                                    'dikonfirmasi admin' => ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI ADMIN'],
                                    'selesai oleh teknisi' => ['bg-emerald-50 text-emerald-700', 'SELESAI OLEH TEKNISI'],
                                    'dikerjakan teknisi' => ['bg-indigo-50 text-indigo-700', 'SEDANG DIKERJAKAN'],
                                    'ditugaskan ke teknisi' => ['bg-purple-50 text-purple-700', 'DITUGASKAN'],
                                    'diproses' => ['bg-blue-50 text-blue-700', 'DIPROSES'],
                                    default => ['bg-amber-50 text-amber-700', 'MENUNGGU'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <p class="text-xs font-bold text-gray-900">{{ $service->displayTransactionDateTime() }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $service->transactionDisplayName() }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900">{{ $service->pelanggan?->nama ?? '-' }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $service->pelanggan?->no_wa ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest">Service APAR</span>
                                        <span class="inline-flex px-3 py-1 rounded-full {{ $isOffline ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600' }} text-[10px] font-black uppercase tracking-widest">{{ $isOffline ? 'Offline' : 'Online' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-black text-gray-900">{{ $service->servicePaket?->nama ?? 'Paket Service' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $service->service_ukuran_apar ?? '-' }} - {{ (int) ($service->service_jumlah_unit ?? 0) }} unit</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900">Rp {{ number_format((float) ($service->service_estimasi_biaya ?? 0), 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusBadge[0] }}">
                                        {{ $statusBadge[1] }}
                                    </span>
                                    @if($service->teknisi_id)
                                        <p class="mt-2 text-[10px] font-bold text-red-600">{{ $service->teknisi->name }}</p>
                                    @endif
                                    @if($service->isPaymentConfirmed())
                                        <p class="mt-2 text-[10px] font-bold text-emerald-600">Lunas</p>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!empty($service->bukti_pembayaran))
                                            <button type="button" onclick='openServiceProofModal(@js(asset("storage/" . ltrim($service->bukti_pembayaran, "/"))), @js("Bukti TF - " . $service->transactionDisplayName()))' class="{{ $actionButtonProof }}">
                                                Bukti TF
                                            </button>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}">
                                                Bukti TF
                                            </button>
                                        @endif
                                        @if(in_array((string) $service->status, ['selesai oleh teknisi', 'dikonfirmasi admin'], true))
                                            <form action="{{ route('admin.pesanan.selesai-final', $service) }}" method="POST" onsubmit="return confirm('Selesaikan final service ini?')">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonSuccess }}">Final</button>
                                            </form>
                                        @elseif($canAssign)
                                            <form action="{{ route('admin.pesanan.assign-teknisi', $service) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonPrimary }}">Assign Teknisi</button>
                                            </form>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}">
                                                Assign Teknisi
                                            </button>
                                        @endif
                                        <button type="button" onclick="openServiceDetailModal({{ $service->id }})" class="{{ $actionButtonNeutral }}">
                                            Detail
                                        </button>
                                        
                                        <a href="{{ route('invoice.show', $service) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                            Lihat Invoice
                                        </a>

                                        @if($service->status !== 'selesai' && $service->status !== 'selesai final')
                                            <form action="{{ route('admin.pesanan.destroy', $service) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Yakin ingin menghapus data service ini?')" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
                                            </form>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}" title="Hapus">Hapus</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data service dari pelanggan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Riwayat Data Service</h3>
                <p class="mt-1 text-xs font-semibold text-gray-500">Log service APAR yang sudah tercatat di sistem.</p>
            </div>
            <div class="responsive-table-wrap">
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
                                    <p class="text-xs font-bold text-gray-900">{{ $service->displayTransactionDateTime() }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $service->transactionDisplayName() }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $service->display_customer_name }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-gray-900">{{ $service->display_unit_label }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $service->unitApar?->produk?->nama ?? 'Unit offline / belum terhubung' }}</p>
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
                                    @php
                                        $serviceProofUrl = !empty($service->pesanan?->bukti_pembayaran)
                                            ? asset('storage/' . ltrim($service->pesanan->bukti_pembayaran, '/'))
                                            : null;
                                    @endphp
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if($serviceProofUrl)
                                            <button type="button" onclick='openServiceProofModal(@js($serviceProofUrl), @js("Bukti TF - " . $service->transactionDisplayName()))' class="{{ $actionButtonProof }}">
                                                Bukti TF
                                            </button>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}">
                                                Bukti TF
                                            </button>
                                        @endif
                                        <button type="button" onclick="openServiceDetailModal('log-{{ $service->id }}')" class="{{ $actionButtonNeutral }}" title="Detail">
                                            Detail
                                        </button>
                                        
                                        @if($service->pesanan)
                                            <a href="{{ route('invoice.show', $service->pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                                Lihat Invoice
                                            </a>
                                        @else
                                            <button type="button" disabled class="{{ $actionButtonDisabled }}" title="Invoice tidak tersedia">
                                                Lihat Invoice
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.service.destroy', $service) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Yakin ingin menghapus riwayat service ini?')" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada log service APAR.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="service-detail-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" onclick="closeServiceDetailModal()"></div>
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-gray-100 z-10">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Detail Data Service</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5" id="service-detail-subtitle"></p>
                    </div>
                    <button onclick="closeServiceDetailModal()" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5" id="service-detail-content"></div>
            </div>
        </div>

        <div id="service-proof-modal" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/70 backdrop-blur-sm" onclick="closeServiceProofModal()"></div>
            <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900" id="service-proof-title">Bukti TF</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">Preview bukti pembayaran pelanggan</p>
                    </div>
                    <button type="button" onclick="closeServiceProofModal()" class="rounded-xl bg-gray-50 p-2 text-gray-400 transition hover:text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="service-proof-body" class="max-h-[78vh] overflow-auto bg-gray-50 p-6"></div>
            </div>
        </div>

        <div x-show="openModal" x-cloak class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto p-3 sm:items-center sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openModal = false"></div>
            <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95" class="app-modal-shell relative my-3 max-w-5xl sm:my-6">
                <div class="app-modal-header flex items-start justify-between gap-4 bg-gradient-to-r from-slate-800 to-slate-700 px-5 py-4 sm:items-center sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-600/30 border border-red-500/30 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tight leading-tight">Input Service Offline</h3>
                            <p class="text-sm text-white/70 font-medium mt-0.5">Form ini digunakan untuk mencatat service APAR dari pelanggan yang datang langsung ke toko.</p>
                        </div>
                    </div>
                    <button type="button" @click="openModal = false" class="w-10 h-10 rounded-2xl bg-white/10 text-white/60 hover:text-white hover:bg-white/20 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="app-modal-body flex-1 p-5 sm:p-6 lg:p-8" x-data="serviceOfflineForm(@js($servicePaketOptions), @js([
                    'service_paket_id' => old('service_paket_id'),
                    'jenis_apar' => old('jenis_apar', ($serviceMediaOptions[0]['label'] ?? 'Powder')),
                    'ukuran_apar' => old('ukuran_apar', '6 Kg'),
                    'jumlah_unit' => old('jumlah_unit', 1),
                ]), @js($serviceMediaOptions ?? []))">
                    <form action="{{ route('admin.service.store') }}" method="POST">
                        @csrf
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
                                        </div>
                                        <div>
                                            <label for="new_pelanggan_no_wa" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor Telepon <span class="text-red-500">*</span></label>
                                            <input type="text" name="new_pelanggan_no_wa" id="new_pelanggan_no_wa" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="081234567890" value="{{ old('new_pelanggan_no_wa') }}">
                                            <p class="text-[9px] font-semibold text-gray-400 mt-1.5">Admin tidak perlu memilih pelanggan lama. Jika nomor sudah ada, sistem akan cocokkan otomatis di backend.</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="new_pelanggan_alamat" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alamat <span class="text-gray-300">(Opsional)</span></label>
                                        <input type="text" name="new_pelanggan_alamat" id="new_pelanggan_alamat" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="Alamat pelanggan jika perlu dicatat" value="{{ old('new_pelanggan_alamat') }}">
                                    </div>
                                </div>

                                <div class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                                        <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 font-black text-sm flex items-center justify-center shrink-0">2</span>
                                        <h4 class="font-black text-gray-900 uppercase tracking-wider text-xs">Informasi Service</h4>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Paket Service <span class="text-red-500">*</span></label>
                                        <select name="service_paket_id" x-model="servicePaketId" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                            <option value="">Pilih paket service</option>
                                            <template x-for="paket in pakets" :key="paket.id">
                                                <option :value="String(paket.id)" x-text="paketOptionLabel(paket)"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <template x-if="selectedPaket()">
                                        <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-5 space-y-2">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rincian Paket</p>
                                            <p class="text-sm font-semibold text-gray-700 leading-relaxed" x-text="selectedPaket()?.rincian || 'Paket ini belum memiliki rincian tambahan.'"></p>
                                        </div>
                                    </template>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                        <div>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Media APAR <span class="text-red-500">*</span></label>
                                            <select name="jenis_apar" x-model="jenisApar" @change="syncUkuranOptions()" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                                <template x-for="media in mediaOptions" :key="media.key">
                                                    <option :value="media.label" x-text="media.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Ukuran APAR <span class="text-red-500">*</span></label>
                                            <select name="ukuran_apar" x-model="ukuranApar" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                                <template x-for="ukuran in ukuranOptions" :key="ukuran">
                                                    <option :value="ukuran" x-text="ukuran"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah Unit <span class="text-red-500">*</span></label>
                                            <input type="number" name="jumlah_unit" x-model.number="jumlahUnit" min="1" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Service <span class="text-red-500">*</span></label>
                                            <input type="date" name="tgl_service" value="{{ old('tgl_service', now()->format('Y-m-d')) }}" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="catatan_admin" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Catatan <span class="text-gray-300">(Opsional)</span></label>
                                        <textarea name="catatan_admin" id="catatan_admin" rows="4" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition text-sm resize-none" placeholder="Catatan tambahan untuk teknisi atau administrasi...">{{ old('catatan_admin') }}</textarea>
                                    </div>
                                    <div class="px-4 py-3 bg-emerald-50 rounded-xl border border-emerald-200">
                                        <p class="text-xs font-bold text-emerald-800">Transaksi service offline langsung dianggap <span class="font-black">lunas</span>, tanpa metode penanganan, dan langsung siap masuk proses teknisi.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="sticky-summary-xl rounded-3xl border border-gray-100 bg-gray-50 p-5 sm:p-6 shadow-sm">
                                    <div class="flex items-center gap-2 border-b border-gray-200/60 pb-3">
                                        <span class="w-6 h-6 rounded-md bg-red-50 text-red-700 font-black text-xs flex items-center justify-center shrink-0">3</span>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Offline</p>
                                    </div>
                                    <div class="mt-6 space-y-4">
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Layanan</span>
                                            <span class="font-black text-red-700">Service Offline</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Harga per Unit</span>
                                            <span class="font-bold text-gray-900" x-text="currency(hargaPaket())"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Media APAR</span>
                                            <span class="font-bold text-gray-900" x-text="jenisApar || '-'"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Jumlah Unit</span>
                                            <span class="font-bold text-gray-900" x-text="jumlahUnit + ' unit'"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Ukuran</span>
                                            <span class="font-bold text-gray-900" x-text="ukuranApar"></span>
                                        </div>
                                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                                            <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Total Akhir</span>
                                            <span class="text-xl font-black text-red-700" x-text="currency(totalBiaya())"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="app-modal-footer mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                            <button type="button" @click="openModal = false" class="w-full px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition sm:w-auto">Batal</button>
                            <button type="submit" class="w-full px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs sm:w-auto">
                                Simpan Service Offline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const serviceDetailData = @json($serviceDetailData);

        function openServiceDetailModal(id) {
            const data = serviceDetailData.find((item) => item.id === id);
            if (!data) return;

            document.getElementById('service-detail-subtitle').textContent = `${data.transaksi} - ${data.waktu}`;

            const paidBadge = data.is_paid
                ? '<span class="inline-flex px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase">LUNAS</span>'
                : '<span class="inline-flex px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black uppercase">BELUM BAYAR</span>';
            const lineItemsHtml = (data.line_items || []).map((item) => `
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <p class="font-bold text-gray-900">${item.label}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">${Number(item.qty || 1)} unit • Rp ${Number(item.total || 0).toLocaleString('id-ID')}</p>
                </div>
            `).join('');
            const peralatanHtml = (data.peralatan || []).map((item) => `
                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <span class="font-bold text-gray-900">${item.nama || '-'}</span>
                    <span class="text-xs font-semibold text-gray-500">x${Number(item.jumlah || 0)}</span>
                </div>
            `).join('');

            document.getElementById('service-detail-content').innerHTML = `
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
                        <p class="font-semibold text-gray-700">${data.alamat}</p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Sumber</p>
                        <p class="font-black text-slate-900">${data.source}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Bayar</p>
                        <div class="mt-1">${paidBadge}</div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Paket Service</p>
                        <p class="font-black text-emerald-700">${data.jenis}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total</p>
                        <p class="font-black text-gray-900">Rp ${data.estimasi}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Ukuran / Unit</p>
                        <p class="font-semibold text-gray-900">${data.ukuran} - ${data.unit} unit</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Teknisi</p>
                        <p class="font-semibold text-gray-900">${data.teknisi}</p>
                    </div>
                </div>
                ${lineItemsHtml ? `<div class="rounded-xl border border-gray-200 bg-gray-50 p-4"><p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Harga Per Unit</p><div class="space-y-3">${lineItemsHtml}</div></div>` : ''}
                ${peralatanHtml ? `<div class="rounded-xl border border-gray-200 bg-gray-50 p-4"><p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Peralatan Paket</p><div class="space-y-3">${peralatanHtml}</div></div>` : ''}
                ${data.catatan !== '-' ? `<div class="rounded-xl border border-amber-100 bg-amber-50 p-4"><span class="text-[10px] font-black text-amber-600 uppercase">Catatan</span><p class="mt-1 text-sm font-semibold whitespace-pre-line">${data.catatan}</p></div>` : ''}
                <div class="flex justify-center">
                    <button type="button" onclick="closeServiceDetailModal()" class="px-8 py-3 bg-gray-200 text-gray-700 font-black text-xs uppercase rounded-xl hover:bg-gray-300 transition">Tutup</button>
                </div>
            `;

            document.getElementById('service-detail-modal').classList.remove('hidden');
        }

        function closeServiceDetailModal() {
            document.getElementById('service-detail-modal').classList.add('hidden');
        }

        function openServiceProofModal(url, title) {
            const modal = document.getElementById('service-proof-modal');
            const body = document.getElementById('service-proof-body');
            const heading = document.getElementById('service-proof-title');
            const isPdf = /\.pdf($|\?)/i.test(String(url || ''));

            heading.textContent = title || 'Bukti TF';
            body.innerHTML = isPdf
                ? `<iframe src="${url}" class="h-[70vh] w-full rounded-2xl border border-gray-200 bg-white" title="Preview bukti pembayaran"></iframe>`
                : `<img src="${url}" alt="Preview bukti pembayaran" class="mx-auto max-h-[70vh] rounded-2xl border border-gray-200 bg-white object-contain">`;

            modal.classList.remove('hidden');
        }

        function closeServiceProofModal() {
            document.getElementById('service-proof-modal').classList.add('hidden');
            document.getElementById('service-proof-body').innerHTML = '';
        }

        function serviceOfflineForm(pakets, initialState, mediaOptions) {
            return {
                pakets,
                mediaOptions,
                servicePaketId: String(initialState?.service_paket_id || ''),
                jenisApar: String(initialState?.jenis_apar || ''),
                ukuranApar: String(initialState?.ukuran_apar || ''),
                jumlahUnit: Number(initialState?.jumlah_unit || 1),
                ukuranOptions: [],
                init() {
                    if (!this.jenisApar && this.mediaOptions.length) {
                        this.jenisApar = String(this.mediaOptions[0].label || '');
                    }
                    this.syncUkuranOptions();
                },
                selectedPaket() {
                    return this.pakets.find((item) => String(item.id) === String(this.servicePaketId)) || null;
                },
                normalizeMediaKey(value) {
                    const text = String(value || '').toLowerCase().trim();
                    if (text.includes('powder') || text.includes('dry chemical') || text.includes('dcp')) return 'powder';
                    if (text.includes('foam')) return 'foam';
                    if (text.includes('co2') || text.includes('carbon')) return 'co2';
                    if (text.includes('clean agent') || text.includes('halotron')) return 'clean_agent';
                    return text.replace(/[^a-z0-9]+/g, '_');
                },
                selectedMedia() {
                    const mediaKey = this.normalizeMediaKey(this.jenisApar);
                    return this.mediaOptions.find((item) => this.normalizeMediaKey(item.label || item.key) === mediaKey) || null;
                },
                syncUkuranOptions() {
                    this.ukuranOptions = this.selectedMedia()?.sizes || [];
                    if (!this.ukuranOptions.includes(this.ukuranApar)) {
                        this.ukuranApar = this.ukuranOptions[0] || '';
                    }
                },
                paketOptionLabel(paket) {
                    const label = String(paket.label || '').trim();
                    return (label ? label + ' - ' : '') + paket.nama + ' - Harga mengikuti media & ukuran';
                },
                hargaPaket() {
                    const paket = this.selectedPaket();
                    if (!paket) return 0;
                    const mediaKey = this.normalizeMediaKey(this.jenisApar);
                    const mediaPrices = paket.price_matrix?.[mediaKey] || {};
                    const direct = Number(mediaPrices?.[this.ukuranApar] || 0);
                    if (direct > 0) return direct;

                    const ukuranKg = Number(String(this.ukuranApar || '').replace(',', '.').match(/(\d+(?:\.\d+)?)/)?.[1] || 0);
                    const matchedSize = Object.keys(mediaPrices).find((size) => Number(String(size || '').replace(',', '.').match(/(\d+(?:\.\d+)?)/)?.[1] || 0) === ukuranKg);
                    return matchedSize ? Number(mediaPrices[matchedSize] || 0) : 0;
                },
                totalBiaya() {
                    return this.hargaPaket() * Math.max(1, Number(this.jumlahUnit || 0));
                },
                currency(value) {
                    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
                },
            }
        }
    </script>
</x-app-layout>
