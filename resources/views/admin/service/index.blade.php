<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-[24px] font-black text-gray-900 tracking-tight">Service APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola permintaan service APAR dengan harga standar per jenis service dan stok peralatan yang terhubung.</p>
            </div>
        </div>
    </x-slot>

    @php
        $riwayatLamaServices = $requestServices->filter(fn ($service) => $service->isLegacyAdminSource());
        $teknisiAktif = $requestServices->filter(fn ($service) => in_array((string) $service->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi'], true));
        $actionButtonBase = 'inline-flex items-center justify-center px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition shadow-sm';
        $actionButtonNeutral = $actionButtonBase . ' border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
        $actionButtonPrimary = $actionButtonBase . ' border-red-600 bg-red-600 text-white hover:bg-red-700 hover:border-red-700';
        $actionButtonProof = $actionButtonBase . ' min-w-[92px] border-transparent bg-blue-600 text-white hover:bg-blue-700';
        $actionButtonProofStyle = 'background-color:#2563eb;border-color:#2563eb;color:#fff;';
        $actionButtonDanger = $actionButtonBase . ' border-red-200 bg-white text-red-600 hover:bg-red-50';
        $actionButtonSuccess = $actionButtonBase . ' border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 hover:border-emerald-700';
        $actionButtonDisabled = $actionButtonBase . ' border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed';
        $normalizeText = fn ($value) => filled(trim((string) $value)) ? trim((string) $value) : null;
        $normalizePhone = fn ($value) => preg_replace('/\D+/', '', (string) $value);
        $findCustomerByWa = function ($wa) use ($pelanggans, $normalizePhone) {
            $needle = $normalizePhone($wa);
            if (!$needle) {
                return null;
            }

            return $pelanggans->first(fn ($pelanggan) => $normalizePhone($pelanggan->no_wa) === $needle);
        };
        $resolveCustomer = function (...$items) use ($pelanggans, $normalizeText, $findCustomerByWa) {
            $candidatePelanggans = collect();
            $fallbackWa = null;

            foreach ($items as $item) {
                if (!$item) {
                    continue;
                }

                $candidatePelanggans->push(data_get($item, 'pelanggan'));
                $candidatePelanggans->push(data_get($item, 'unitApar.pelanggan'));
                $candidatePelanggans->push(data_get($item, 'service.unitApar.pelanggan'));
                $candidatePelanggans->push(data_get($item, 'service.pesanan.pelanggan'));
                $candidatePelanggans->push(data_get($item, 'pesanan.pelanggan'));

                $pelangganId = data_get($item, 'pelanggan_id')
                    ?: data_get($item, 'pesanan.pelanggan_id')
                    ?: data_get($item, 'service.pesanan.pelanggan_id');
                if ($pelangganId) {
                    $candidatePelanggans->push($pelanggans->firstWhere('id', $pelangganId));
                }

                $fallbackWa = $fallbackWa
                    ?: $normalizeText(data_get($item, 'no_wa'))
                    ?: $normalizeText(data_get($item, 'whatsapp'))
                    ?: $normalizeText(data_get($item, 'telepon'));
            }

            $pelanggan = $candidatePelanggans->filter()->first(fn ($pelanggan) => $normalizeText($pelanggan->nama ?? null));

            if (!$pelanggan && $fallbackWa) {
                $pelanggan = $findCustomerByWa($fallbackWa);
            }

            return [
                'nama' => $normalizeText($pelanggan?->nama ?? null) ?? '-',
                'wa' => $normalizeText($pelanggan?->no_wa ?? null) ?? $fallbackWa ?? '-',
            ];
        };
        $resolveUnitDisplay = function ($record = null, $unitApar = null) {
            if ($record instanceof \App\Models\Pesanan) {
                return $record->serviceUnitDisplay();
            }

            if ($record instanceof \App\Models\Service) {
                return \App\Support\ServiceUnitDisplay::forService($record);
            }

            if ($record instanceof \App\Models\Refill) {
                return \App\Support\ServiceUnitDisplay::forRefill($record);
            }

            if ($unitApar instanceof \App\Models\UnitApar) {
                return \App\Support\ServiceUnitDisplay::forUnitApar($unitApar);
            }

            return \App\Support\ServiceUnitDisplay::empty();
        };
        $servicePaketOptions = collect($servicePaketCatalog ?? [])->values();
        $legacyServices = $serviceLogs->filter(fn($log) => is_null($log->pesanan_id));
        $mergedHistory = $selesaiTeknisi->concat($legacyServices)->sortByDesc(function ($item) {
            return $item->teknisi_selesai_at ?? $item->tgl_selesai_admin ?? $item->created_at;
        });

        $serviceDetailData = $requestServices->map(function ($service) use ($resolveCustomer) {
            $customer = $resolveCustomer($service);
            $unitDisplay = $service->serviceUnitDisplay();
            return [
                'id' => $service->id,
                'pelanggan' => $customer['nama'],
                'no_wa' => $customer['wa'],
                'wa_url' => \App\Support\WhatsApp::customerLink($customer['wa'], 'Halo Bapak/Ibu, kami ingin mengonfirmasi service APAR Anda.'),
                'alamat' => $service->pelanggan?->alamat ?? '-',
                'alamat_lat' => $service->alamat_lat ?? $service->pelanggan?->alamat_lat,
                'alamat_lng' => $service->alamat_lng ?? $service->pelanggan?->alamat_lng,
                'transaksi' => $service->transactionDisplayName(),
                'waktu' => $service->displayTransactionDateTime(),
                'jenis' => $service->servicePaket?->nama ?? 'Service APAR',
                'estimasi' => number_format((float) ($service->service_estimasi_biaya ?? 0), 0, ',', '.'),
                'ukuran' => $service->service_ukuran_apar ?? '-',
                'unit' => (int) ($service->service_jumlah_unit ?? 0),
                'source' => $service->adminSourceLabel(),
                'teknisi' => $service->teknisi?->name ?? 'Belum ditugaskan',
                'catatan' => $service->catatan_admin ?: $service->service_admin_catatan ?: $service->serviceCustomerNote() ?: $service->keterangan ?: '-',
                'status' => $service->status,
                'status_label' => $service->publicStatusLabel(),
                'hide_payment_badge' => $service->shouldHidePaymentStatusBadge(),
                'is_paid' => $service->isPaymentConfirmed(),
                'proof_url' => !empty($service->bukti_pembayaran) ? '/storage/' . ltrim($service->bukti_pembayaran, '/') : null,
                'line_items' => $service->servicePricingBreakdown(),
                'peralatan' => $service->servicePeralatanItems(),
                'unit_display' => $unitDisplay,
            ];
        })->concat($mergedHistory->map(function ($item) use ($resolveCustomer, $resolveUnitDisplay) {
            $isLegacy = $item instanceof \App\Models\Service;
            $pesanan = $isLegacy ? null : $item;
            $service = $isLegacy ? $item : $item->service;
            $customer = $resolveCustomer($pesanan, $service);
            $unitDisplay = $isLegacy
                ? $resolveUnitDisplay($service, $service?->unitApar)
                : $resolveUnitDisplay($pesanan, $pesanan?->service?->unitApar);
            
            return [
                'id' => $isLegacy ? 'log-' . $service->id : $pesanan->id,
                'pelanggan' => $customer['nama'],
                'no_wa' => $customer['wa'],
                'wa_url' => \App\Support\WhatsApp::customerLink(
                    $customer['wa'],
                    'Halo Bapak/Ibu, kami ingin mengonfirmasi ' . strtolower($pesanan ? $pesanan->transactionDisplayName() : $service->transactionDisplayName()) . ' APAR Anda.'
                ),
                'alamat' => $pesanan?->pelanggan?->alamat ?? $service?->unitApar?->pelanggan?->alamat ?? '-',
                'alamat_lat' => $pesanan?->alamat_lat ?? $pesanan?->pelanggan?->alamat_lat ?? $service?->alamat_lat ?? $service?->unitApar?->pelanggan?->alamat_lat,
                'alamat_lng' => $pesanan?->alamat_lng ?? $pesanan?->pelanggan?->alamat_lng ?? $service?->alamat_lng ?? $service?->unitApar?->pelanggan?->alamat_lng,
                'transaksi' => $pesanan ? $pesanan->transactionDisplayName() : $service->transactionDisplayName(),
                'waktu' => $pesanan ? $pesanan->displayTransactionDateTime() : $service->displayTransactionDateTime(),
                'jenis' => $pesanan?->servicePaket?->nama ?? $service?->servicePaket?->nama ?? $service?->jenis_service ?? 'Service APAR',
                'estimasi' => number_format((float) ($pesanan?->payableTotal() ?? $service?->biaya ?? 0), 0, ',', '.'),
                'ukuran' => $pesanan?->service_ukuran_apar ?? $service?->unitApar?->produk?->kapasitas ?? '-',
                'unit' => $pesanan ? (int) ($pesanan->service_jumlah_unit ?? 1) : 1,
                'source' => $pesanan ? $pesanan->adminSourceLabel() : 'Riwayat Lama',
                'teknisi' => $pesanan?->teknisi?->name ?? 'Selesai',
                'catatan' => $pesanan?->catatan_admin ?: $pesanan?->serviceCustomerNote() ?: $service?->keterangan ?: '-',
                'status' => $pesanan?->status ?? 'selesai final',
                'status_label' => $pesanan?->publicStatusLabel() ?? 'Selesai Final',
                'hide_payment_badge' => $pesanan ? $pesanan->shouldHidePaymentStatusBadge() : true,
                'is_paid' => $pesanan ? $pesanan->isPaymentConfirmed() : true,
                'proof_url' => !empty($pesanan?->bukti_pembayaran) ? '/storage/' . ltrim($pesanan->bukti_pembayaran, '/') : (!empty($service?->pesanan?->bukti_pembayaran) ? '/storage/' . ltrim($service->pesanan->bukti_pembayaran, '/') : null),
                'line_items' => $pesanan?->servicePricingBreakdown() ?? [],
                'peralatan' => $pesanan?->servicePeralatanItems() ?? ($service?->effective_peralatan ?? []),
                'unit_display' => $unitDisplay,
            ];
        }))->values();
    @endphp

    <div class="space-y-8" x-data="{ openModal: {{ $errors->any() ? 'true' : 'false' }} }">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Permintaan</p>
                <p class="text-4xl font-black text-gray-900">{{ $requestServices->count() + $selesaiTeknisi->count() + $legacyServices->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Transaksi Pelanggan</p>
                <p class="text-4xl font-black text-emerald-700">{{ $requestServices->count() - $riwayatLamaServices->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Riwayat Lama</p>
                <p class="text-4xl font-black text-amber-700">{{ $riwayatLamaServices->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Proses Teknisi</p>
                <p class="text-4xl font-black text-red-700">{{ $teknisiAktif->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Data Service dari Pelanggan</h3>
                <p class="mt-1 text-sm font-semibold text-gray-500">Permintaan service yang sedang diproses admin.</p>
            </div>
            <div class="responsive-table-wrap">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit APAR</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Service</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($requestServices as $service)
                            @php
                                $serviceCustomer = $resolveCustomer($service);
                                $isLegacySource = $service->isLegacyAdminSource();
                                $canAssign = $service->isPaymentConfirmed() && !$service->teknisi_id;
                                $canReadyToShip = $service->canMarkReadyToShip();
                                $canFinalize = $service->canFinalizeDirectlyByAdmin();
                                $statusBadge = match ((string) $service->status) {
                                    'selesai final', 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
                                    'siap dikirim' => ['bg-cyan-50 text-cyan-700', 'SIAP DIKIRIM'],
                                    'dikonfirmasi admin' => ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI ADMIN'],
                                    'selesai oleh teknisi' => ['bg-cyan-50 text-cyan-700', 'SELESAI OLEH TEKNISI'],
                                    'dikerjakan teknisi' => ['bg-indigo-50 text-indigo-700', 'SEDANG DIKERJAKAN'],
                                    'ditugaskan ke teknisi' => ['bg-purple-50 text-purple-700', 'DITUGASKAN'],
                                    'diproses' => ['bg-red-50 text-blue-700', 'DIPROSES'],
                                    default => ['bg-amber-50 text-amber-700', 'MENUNGGU'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <p class="text-xs font-bold text-gray-900">{{ $service->displayTransactionDateTime() }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $service->transactionDisplayName() }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900">{{ $serviceCustomer['nama'] }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $serviceCustomer['wa'] }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    @include('admin.partials.unit-apar-column', ['pesanan' => $service, 'unitApar' => $service->service?->unitApar])
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900">{{ $service->servicePaket?->nama ?? 'Service APAR' }}</p>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format((float) ($service->service_estimasi_biaya ?? 0), 0, ',', '.') }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusBadge[0] }}">
                                        {{ $statusBadge[1] }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!$isLegacySource)
                                            <button
                                                type="button"
                                                onclick="openServiceProofModal(@js(!empty($service->bukti_pembayaran) ? '/storage/' . ltrim($service->bukti_pembayaran, '/') : null), @js([
                                                    "customer" => $service->pelanggan?->nama ?? "-",
                                                    "date" => $service->displayTransactionDateTime(),
                                                    "type" => "Service",
                                                ]))"
                                                class="{{ $actionButtonProof }}"
                                                style="{{ $actionButtonProofStyle }}"
                                            >
                                                Bukti TF
                                            </button>
                                        @endif
                                        @if($canReadyToShip)
                                            <form action="{{ route('admin.pesanan.konfirmasi-pelanggan', $service) }}" method="POST" data-confirm="Ubah status service ini menjadi Siap Dikirim?" data-confirm-title="Konfirmasi Pengiriman" data-confirm-button="Ya, Siapkan">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonSuccess }}">Siap Dikirim</button>
                                            </form>
                                        @elseif($canFinalize)
                                            <form action="{{ route('admin.pesanan.selesai-final', $service) }}" method="POST" data-confirm="Selesaikan final service ini?" data-confirm-title="Konfirmasi Final" data-confirm-button="Ya, Finalkan">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonSuccess }}">Final</button>
                                            </form>
                                        @elseif($canAssign)
                                            <form action="{{ route('admin.pesanan.assign-teknisi', $service) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonPrimary }}">Assign</button>
                                            </form>
                                        @endif
                                        <button type="button" onclick="openServiceDetailModal({{ $service->id }})" class="{{ $actionButtonNeutral }}">
                                            Detail
                                        </button>
                                        
                                        <a href="{{ route('invoice.show', $service) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                            Lihat Invoice
                                        </a>

                                        @if($service->status !== 'selesai' && $service->status !== 'selesai final')
                                            <form action="{{ route('admin.pesanan.destroy', $service) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus data service ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
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
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mergedHistory as $item)
                            @php
                                $isLegacy = $item instanceof \App\Models\Service;
                                $pesanan = $isLegacy ? null : $item;
                                $service = $isLegacy ? $item : $item->service;
                                
                                $serviceHistoryIsLegacy = $pesanan ? $pesanan->isLegacyAdminSource() : true;
                                
                                $tanggal = $pesanan ? $pesanan->displayTransactionDateTime() : $service->displayTransactionDateTime();
                                $trxName = $pesanan ? $pesanan->transactionDisplayName() : $service->transactionDisplayName();
                                $serviceCustomer = $resolveCustomer($pesanan, $service);
                                $pelangganNama = $serviceCustomer['nama'];
                                $pelangganWa = $serviceCustomer['wa'];
                                $jenisService = $pesanan ? ($pesanan->servicePaket?->nama ?? $pesanan->jenis_service ?? 'Service APAR') : ($service->servicePaket?->nama ?? $service->jenis_service ?? 'Service APAR');
                                $keterangan = $pesanan ? ($pesanan->catatan_admin ?: $pesanan->serviceCustomerNote() ?: '-') : ($service->keterangan ?: '-');
                                $totalBiaya = $pesanan ? ($pesanan->total_harga ?? 0) : ($service->biaya ?? 0);
                            @endphp
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-xs font-bold text-gray-900">{{ $tanggal }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $trxName }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $pelangganNama }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $pelangganWa }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    @include('admin.partials.unit-apar-column', ['pesanan' => $pesanan, 'unitApar' => $service?->unitApar])
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $jenisService }}</p>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format((float) $totalBiaya, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700">
                                        SELESAI FINAL
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    @php
                                        $serviceProofUrl = !empty($pesanan?->bukti_pembayaran)
                                            ? '/storage/' . ltrim($pesanan->bukti_pembayaran, '/')
                                            : (!empty($service?->pesanan?->bukti_pembayaran) ? '/storage/' . ltrim($service->pesanan->bukti_pembayaran, '/') : null);
                                    @endphp
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!$serviceHistoryIsLegacy && $serviceProofUrl)
                                            <button
                                                type="button"
                                                onclick="openServiceProofModal(@js($serviceProofUrl), @js([
                                                    "customer" => $pelangganNama,
                                                    "date" => $tanggal,
                                                    "type" => "Service",
                                                ]))"
                                                class="{{ $actionButtonProof }}"
                                                style="{{ $actionButtonProofStyle }}"
                                            >
                                                Bukti TF
                                            </button>
                                        @endif
                                        <button type="button" onclick="openServiceDetailModal('{{ $isLegacy ? 'log-' . $service->id : $pesanan->id }}')" class="{{ $actionButtonNeutral }}" title="Detail">
                                            Detail
                                        </button>
                                        
                                        @if($pesanan)
                                            <a href="{{ route('invoice.show', $pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                                Lihat Invoice
                                            </a>
                                        @elseif($service?->pesanan)
                                            <a href="{{ route('invoice.show', $service->pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                                Lihat Invoice
                                            </a>
                                        @else
                                            <button type="button" onclick="showAppAlert('Invoice tidak tersedia untuk data legacy ini.', 'warning', 'Peringatan')" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                                Lihat Invoice
                                            </button>
                                        @endif
                                        @if($pesanan)
                                        <form action="{{ route('admin.pesanan.destroy', $pesanan) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus data service ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
                                        </form>
                                        @elseif($isLegacy)
                                        <form action="{{ route('admin.service.destroy', $service) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus riwayat service ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
                                        </form>
                                        @endif
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
                        <h3 class="text-lg font-black text-gray-900" id="service-proof-title">Bukti Transfer</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">Preview bukti pembayaran pelanggan</p>
                    </div>
                    <button type="button" onclick="closeServiceProofModal()" class="rounded-xl bg-gray-50 p-2 text-gray-400 transition hover:text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="service-proof-body" class="max-h-[78vh] overflow-auto bg-gray-50 p-6"></div>
            </div>
        </div>

        @if(false)
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
                    'pelanggan_id' => old('pelanggan_id'),
                    'status_unit' => old('status_unit', 'belum_terdaftar'),
                    'service_paket_id' => old('service_paket_id'),
                    'jenis_apar' => old('jenis_apar', ($serviceMediaOptions[0]['label'] ?? 'Powder')),
                    'ukuran_apar' => old('ukuran_apar', '6 Kg'),
                    'jumlah_unit' => old('jumlah_unit', 1),
                    'selected_unit_ids' => collect(old('unit_apar_ids', old('unit_apar_id') ? [old('unit_apar_id')] : []))->map(fn ($id) => (string) $id)->values(),
                ]), @js($serviceMediaOptions ?? []), @js($pelanggans->map(fn($p) => [
                    'id' => (string) $p->id,
                    'no_wa' => $p->no_wa,
                    'email' => $p->user?->email,
                    'alamat_lengkap' => $p->alamat,
                    'units' => $p->units->map(fn($u) => [
                        'id' => (string) $u->id,
                        'code' => $u->no_seri,
                        'nama' => $u->produk?->nama ?? 'Unit Custom',
                        'ukuran' => $u->ukuran ?? $u->produk?->kapasitas,
                        'jenis' => $u->produk?->jenisApar?->nama ?? $u->bahan,
                        'display_label' => trim(($u->no_seri ? $u->no_seri . ' - ' : '') . ($u->produk?->nama ?? 'APAR') . ' ' . ($u->produk?->jenisApar?->nama ?? $u->bahan ?? '') . ' ' . ($u->ukuran ?? $u->produk?->kapasitas ?? '')),
                    ])->values()
                ])->values()))">
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
                                    <div class="space-y-4">
                                        <label for="pelanggan_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pilih Pelanggan <span class="text-red-500">*</span></label>
                                        <select name="pelanggan_id" id="pelanggan_id" required x-model="selectedPelangganId" @change="syncPelangganProfile" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm">
                                            <option value="">-- Pilih Pelanggan Terdaftar --</option>
                                            @foreach($pelanggans as $pelanggan)
                                                <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }} ({{ $pelanggan->no_wa }})</option>
                                            @endforeach
                                        </select>
                                        <p class="text-[10px] font-semibold leading-relaxed text-slate-500">
                                            Pelanggan service offline harus berasal dari akun role pelanggan. Jika belum ada, buat akun pelanggan terlebih dahulu melalui
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
                                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                                        <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 font-black text-sm flex items-center justify-center shrink-0">2</span>
                                        <h4 class="font-black text-gray-900 uppercase tracking-wider text-xs">Informasi Service</h4>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Service <span class="text-red-500">*</span></label>
                                        <select name="service_paket_id" x-model="servicePaketId" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                            <option value="">Pilih jenis service</option>
                                            <template x-for="paket in pakets" :key="paket.id">
                                                <option :value="String(paket.id)" x-text="paketOptionLabel(paket)"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <template x-if="selectedPaket()">
                                        <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-5 space-y-2">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rincian Service</p>
                                            <p class="text-sm font-semibold text-gray-700 leading-relaxed" x-text="selectedPaket()?.rincian || 'Jenis service ini belum memiliki rincian tambahan.'"></p>
                                        </div>
                                    </template>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                        <div class="md:col-span-4">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Status Unit APAR <span class="text-red-500">*</span></label>
                                            <select x-model="statusUnit" @change="syncUnitApar" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm">
                                                <option value="belum_terdaftar">APAR Belum Terdaftar (Manual)</option>
                                                <option value="terdaftar">APAR Terdaftar</option>
                                            </select>
                                        </div>

                                        <div class="md:col-span-4" x-show="statusUnit === 'terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pilih Unit APAR <span class="text-red-500">*</span></label>
                                            <div class="space-y-2 rounded-2xl border border-gray-200 bg-gray-50 p-3">
                                                <template x-for="unit in (selectedPelangganInfo?.units || [])" :key="unit.id">
                                                    <label class="flex items-start gap-3 rounded-2xl border border-white bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm">
                                                        <input type="checkbox" name="unit_apar_ids[]" :value="unit.id" x-model="selectedUnitAparIds" @change="syncUnitApar" class="mt-1 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                        <div class="min-w-0">
                                                            <p class="font-black text-gray-900" x-text="unit.code || unit.nama"></p>
                                                            <p class="mt-1 text-xs font-semibold text-gray-500" x-text="'APAR ' + (unit.jenis || '-') + ' ' + (unit.ukuran || '-')"></p>
                                                        </div>
                                                    </label>
                                                </template>
                                            </div>
                                            <p x-show="selectedPelangganId && !(selectedPelangganInfo?.units?.length)" class="text-[10px] font-bold text-red-600 mt-2">Pelanggan ini belum memiliki Unit APAR terdaftar.</p>
                                            <p x-show="selectedPelangganId && selectedPelangganInfo?.units?.length && !selectedUnitAparIds.length" class="text-[10px] font-bold text-red-600 mt-2">Centang minimal satu unit APAR terlebih dahulu.</p>
                                            <x-input-error :messages="$errors->get('unit_apar_ids')" class="mt-2" />
                                        </div>

                                        <div x-show="statusUnit === 'belum_terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Media APAR <span class="text-red-500">*</span></label>
                                            <select name="jenis_apar" x-model="jenisApar" @change="syncUkuranOptions()" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                                <template x-for="media in mediaOptions" :key="media.key">
                                                    <option :value="media.label" x-text="media.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div x-show="statusUnit === 'belum_terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Ukuran APAR <span class="text-red-500">*</span></label>
                                            <select name="ukuran_apar" x-model="ukuranApar" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                                <template x-for="ukuran in ukuranOptions" :key="ukuran">
                                                    <option :value="ukuran" x-text="ukuran"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div x-show="statusUnit === 'belum_terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah Unit <span class="text-red-500">*</span></label>
                                            <input type="number" name="jumlah_unit" x-model.number="jumlahUnit" min="1" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                        </div>
                                        <div x-show="statusUnit === 'terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah Unit Terpilih</label>
                                            <div class="w-full rounded-2xl bg-gray-50 px-6 py-4 text-sm font-black text-gray-900" x-text="displayJumlahUnit() + ' unit'"></div>
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
                                            <span>Harga Ringkasan</span>
                                            <span class="font-bold text-gray-900" x-text="currency(hargaRingkasan())"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Detail APAR</span>
                                            <span class="font-bold text-gray-900 text-right" x-text="displayDetailApar()"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Jumlah Unit</span>
                                            <span class="font-bold text-gray-900" x-text="displayJumlahUnit() + ' unit'"></span>
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
                            <button type="submit" :disabled="!canSubmit()" :class="!canSubmit() ? 'bg-gray-300 text-gray-500 shadow-none cursor-not-allowed' : 'bg-red-700 text-white hover:bg-red-800 shadow-xl shadow-red-700/30'" class="w-full px-10 py-4 font-black rounded-2xl transition uppercase tracking-widest text-xs sm:w-auto">
                                Simpan Service Offline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        const serviceDetailData = @json($serviceDetailData);

        function openServiceDetailModal(id) {
            const data = serviceDetailData.find((item) => String(item.id) === String(id));
            if (!data) return;

            document.getElementById('service-detail-subtitle').textContent = `${data.transaksi} - ${data.waktu}`;
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
            const unitEntries = (data.unit_display?.entries || []).map((entry) => `
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <p class="font-semibold text-gray-900">${entry.label || '-'}</p>
                    ${entry.code ? `<p class="mt-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">${entry.code}</p>` : ''}
                </div>
            `).join('');
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
            const proofHtml = data.proof_url
                ? `<div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Bukti Pembayaran</p>
                    ${data.proof_url
                        ? (/\.pdf($|\?)/i.test(String(data.proof_url))
                            ? `<iframe src="${data.proof_url}" class="h-72 w-full rounded-xl border border-gray-200 bg-white" title="Preview bukti pembayaran"></iframe>`
                            : `<img src="${data.proof_url}" alt="Preview bukti pembayaran" class="mx-auto max-h-72 rounded-xl border border-gray-200 bg-white object-contain">`)
                        : `<div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">Bukti transfer belum tersedia atau file tidak ditemukan.</div>`
                    }
                </div>`
                : '';

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
                        ${(data.alamat_lat && data.alamat_lng) ? `
                        <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-sm">
                            <div id="service-detail-map" class="w-full bg-gray-100" style="height: 200px;"></div>
                        </div>
                        ` : `
                        <div class="mt-2 rounded-xl border border-gray-200 bg-gray-100/50 px-4 py-2.5 text-xs font-bold text-gray-500 text-center">
                            Titik lokasi map tidak diatur.
                        </div>
                        `}
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kategori Data</p>
                        <p class="font-black text-slate-900">${data.source}</p>
                    </div>
                    ${paymentStatusHtml}
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                        <p class="font-semibold text-gray-900">${data.status_label}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">${data.unit_display?.heading || 'Unit APAR'}</p>
                        <div class="space-y-3">${unitEntries || `<div class="rounded-xl border border-gray-200 bg-white px-4 py-3"><p class="font-semibold text-gray-900">${data.unit_display?.detail_label || data.unit_display?.summary || '-'}</p></div>`}</div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jumlah Unit</p>
                        <p class="font-black text-gray-900">${data.unit_display?.quantity || data.unit || 0} unit</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jenis Service</p>
                        <p class="font-black text-emerald-700">${data.jenis}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total</p>
                        <p class="font-black text-gray-900">Rp ${data.estimasi}</p>
                    </div>
                </div>
                ${lineItemsHtml ? `<div class="rounded-xl border border-gray-200 bg-gray-50 p-4"><p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Harga Service</p><div class="space-y-3">${lineItemsHtml}</div></div>` : ''}
                ${peralatanHtml ? `<div class="rounded-xl border border-gray-200 bg-gray-50 p-4"><p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Peralatan Service</p><div class="space-y-3">${peralatanHtml}</div></div>` : ''}
                ${proofHtml}
                ${data.catatan !== '-' ? `<div class="rounded-xl border border-amber-100 bg-amber-50 p-4"><span class="text-[10px] font-black text-amber-600 uppercase">Catatan</span><p class="mt-1 text-sm font-semibold whitespace-pre-line">${data.catatan}</p></div>` : ''}
                <div class="flex flex-wrap justify-center gap-3">
                    ${data.wa_url ? `<a href="${data.wa_url}" target="_blank" rel="noopener noreferrer" class="px-6 py-3 bg-green-500 text-white font-black text-xs uppercase rounded-xl hover:bg-green-600 transition">Hubungi Pelanggan</a>` : ''}
                    <button type="button" onclick="closeServiceDetailModal()" class="px-8 py-3 bg-gray-200 text-gray-700 font-black text-xs uppercase rounded-xl hover:bg-gray-300 transition">Tutup</button>
                </div>
            `;

            document.getElementById('service-detail-modal').classList.remove('hidden');

            if (data.alamat_lat && data.alamat_lng) {
                setTimeout(() => {
                    const mapDiv = document.getElementById('service-detail-map');
                    if (mapDiv) {
                        const lat = Number(data.alamat_lat);
                        const lng = Number(data.alamat_lng);
                        const map = L.map('service-detail-map', {
                            scrollWheelZoom: false,
                            zoomControl: true
                        }).setView([lat, lng], 16);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        const markerIcon = L.divIcon({
                            html: '<div class="flex items-center justify-center w-8 h-8 rounded-full bg-red-600 text-white shadow-lg border-2 border-white"><i class="fa-solid fa-location-dot"></i></div>',
                            className: 'service-map-marker',
                            iconSize: [32, 32],
                            iconAnchor: [16, 32]
                        });
                        L.marker([lat, lng], { icon: markerIcon }).addTo(map);

                        setTimeout(() => {
                            map.invalidateSize();
                        }, 100);
                    }
                }, 50);
            }
        }

        function closeServiceDetailModal() {
            document.getElementById('service-detail-modal').classList.add('hidden');
            document.getElementById('service-detail-content').innerHTML = '';
        }

        function openServiceProofModal(url, meta = {}) {
            const modal = document.getElementById('service-proof-modal');
            const body = document.getElementById('service-proof-body');
            const heading = document.getElementById('service-proof-title');
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
                            <p class="mt-1 text-sm font-semibold text-gray-900">${meta.type || 'Service'}</p>
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

        function closeServiceProofModal() {
            document.getElementById('service-proof-modal').classList.add('hidden');
            document.getElementById('service-proof-body').innerHTML = '';
        }

        function serviceOfflineForm(pakets, initialState, mediaOptions, pelanggansData) {
            return {
                pakets,
                mediaOptions,
                pelanggansData: pelanggansData || [],
                selectedPelangganId: initialState?.pelanggan_id || '',
                selectedPelangganInfo: {},
                statusUnit: initialState?.status_unit || 'belum_terdaftar',
                selectedUnitAparIds: Array.isArray(initialState?.selected_unit_ids)
                    ? initialState.selected_unit_ids.map(String)
                    : [],
                servicePaketId: String(initialState?.service_paket_id || ''),
                jenisApar: String(initialState?.jenis_apar || ''),
                ukuranApar: String(initialState?.ukuran_apar || ''),
                jumlahUnit: Number(initialState?.jumlah_unit || 1),
                ukuranOptions: [],
                init() {
                    if (this.selectedPelangganId) {
                        this.syncPelangganProfile();
                    }
                    if (!this.jenisApar && this.mediaOptions.length) {
                        this.jenisApar = String(this.mediaOptions[0].label || '');
                    }
                    this.syncUkuranOptions();
                },
                syncPelangganProfile() {
                    const info = this.pelanggansData.find(p => p.id === this.selectedPelangganId);
                    this.selectedPelangganInfo = info || {};
                    this.selectedUnitAparIds = [];
                    this.syncUnitApar();
                },
                syncUnitApar() {
                    if (this.statusUnit === 'terdaftar') {
                        const availableIds = new Set((this.selectedPelangganInfo?.units || []).map(unit => String(unit.id)));
                        this.selectedUnitAparIds = this.selectedUnitAparIds.filter(id => availableIds.has(String(id)));
                        this.jumlahUnit = Math.max(1, this.selectedRegisteredUnits().length || 1);
                    } else if (this.statusUnit === 'belum_terdaftar') {
                        this.selectedUnitAparIds = [];
                        this.jumlahUnit = 1;
                    }
                },
                selectedPaket() {
                    return this.pakets.find((item) => String(item.id) === String(this.servicePaketId)) || null;
                },
                selectedRegisteredUnits() {
                    const selectedIds = new Set(this.selectedUnitAparIds.map(String));
                    return (this.selectedPelangganInfo?.units || []).filter(unit => selectedIds.has(String(unit.id)));
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
                    return (label ? label + ' - ' : '') + paket.nama + ' - Harga standar per unit APAR';
                },
                hargaPaketFor(mediaLabel, ukuranLabel) {
                    const paket = this.selectedPaket();
                    if (!paket) return 0;
                    const mediaKey = this.normalizeMediaKey(mediaLabel);
                    const mediaPrices = paket.price_matrix?.[mediaKey] || {};
                    const direct = Number(mediaPrices?.[ukuranLabel] || 0);
                    if (direct > 0) return direct;

                    const ukuranKg = Number(String(ukuranLabel || '').replace(',', '.').match(/(\d+(?:\.\d+)?)/)?.[1] || 0);
                    const matchedSize = Object.keys(mediaPrices).find((size) => Number(String(size || '').replace(',', '.').match(/(\d+(?:\.\d+)?)/)?.[1] || 0) === ukuranKg);
                    return matchedSize ? Number(mediaPrices[matchedSize] || 0) : 0;
                },
                hargaPaket() {
                    return this.hargaPaketFor(this.jenisApar, this.ukuranApar);
                },
                hargaRingkasan() {
                    if (this.statusUnit === 'terdaftar') {
                        const units = this.selectedRegisteredUnits();
                        if (units.length === 0) return 0;
                        return units.reduce((total, unit) => total + this.hargaPaketFor(unit.jenis, unit.ukuran), 0);
                    }

                    return this.hargaPaket();
                },
                totalBiaya() {
                    if (this.statusUnit === 'terdaftar') {
                        return this.selectedRegisteredUnits().reduce(
                            (total, unit) => total + this.hargaPaketFor(unit.jenis, unit.ukuran),
                            0
                        );
                    }

                    return this.hargaPaket() * Math.max(1, Number(this.jumlahUnit || 0));
                },
                displayJumlahUnit() {
                    return this.statusUnit === 'terdaftar'
                        ? this.selectedRegisteredUnits().length
                        : Math.max(1, Number(this.jumlahUnit || 0));
                },
                displayDetailApar() {
                    if (this.statusUnit === 'terdaftar') {
                        const details = this.selectedRegisteredUnits()
                            .map(unit => `APAR ${unit.jenis || '-'} ${unit.ukuran || '-'}`)
                            .filter(Boolean);

                        return details.length ? Array.from(new Set(details)).join(', ') : '-';
                    }

                    return `APAR ${this.jenisApar || '-'} ${this.ukuranApar || '-'}`.trim();
                },
                canSubmit() {
                    if (!this.selectedPelangganId || !this.servicePaketId) {
                        return false;
                    }

                    if (this.statusUnit === 'terdaftar') {
                        return this.selectedRegisteredUnits().length > 0
                            && this.selectedRegisteredUnits().every(unit => this.hargaPaketFor(unit.jenis, unit.ukuran) > 0);
                    }

                    return !!this.jenisApar && !!this.ukuranApar && Number(this.jumlahUnit || 0) > 0 && this.hargaPaket() > 0;
                },
                currency(value) {
                    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
                },
            }
        }
    </script>
</x-app-layout>
