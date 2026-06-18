<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-[24px] font-black text-gray-900 tracking-tight">Refill APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola permintaan refill APAR yang masuk dari pelanggan.</p>
            </div>
        </div>
    </x-slot>

    @php
        $riwayatLamaRefills = $requestRefills->filter(fn ($refill) => $refill->isLegacyAdminSource());
        $dikerjakanTeknisi = $requestRefills->filter(fn ($refill) => in_array((string) $refill->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi'], true));
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

            if ($record instanceof \App\Models\Refill) {
                return \App\Support\ServiceUnitDisplay::forRefill($record);
            }

            if ($record instanceof \App\Models\Service) {
                return \App\Support\ServiceUnitDisplay::forService($record);
            }

            if ($unitApar instanceof \App\Models\UnitApar) {
                return \App\Support\ServiceUnitDisplay::forUnitApar($unitApar);
            }

            return \App\Support\ServiceUnitDisplay::empty();
        };
        $refillOfflineOptions = $jenisRefills->map(fn ($jenisRefill) => [
            'id' => $jenisRefill->id,
            'nama' => $jenisRefill->nama_label,
            'harga' => (float) ($jenisRefill->harga ?? 0),
            'satuan' => $jenisRefill->satuan_label,
            'rules' => collect($jenisRefill->service_price_rules_json ?? [])->map(fn ($rule) => [
                'ukuran' => (string) ($rule['ukuran'] ?? ''),
                'harga' => (float) ($rule['harga'] ?? 0),
            ])->values()->all(),
        ])->values();
        $legacyRefills = $refills->filter(fn($log) => is_null($log->service?->pesanan_id));
        $mergedHistory = $completedRequestRefills->concat($legacyRefills)->sortByDesc(function ($item) {
            return $item->teknisi_selesai_at ?? $item->tgl_selesai_admin ?? $item->created_at;
        });

        $refillDetailData = $requestRefills->map(function ($refill) use ($resolveCustomer) {
            $customer = $resolveCustomer($refill);
            $unitDisplay = $refill->serviceUnitDisplay();
            return [
                'id' => $refill->id,
                'pelanggan' => $customer['nama'],
                'no_wa' => $customer['wa'],
                'wa_url' => \App\Support\WhatsApp::customerLink($customer['wa'], 'Halo Bapak/Ibu, kami ingin mengonfirmasi refill APAR Anda.'),
                'alamat' => $refill->pelanggan?->alamat ?? '-',
                'transaksi' => $refill->transactionDisplayName(),
                'waktu' => $refill->displayTransactionDateTime(),
                'jenis' => $refill->serviceJenisRefill?->nama_label ?? 'Refill APAR',
                'estimasi' => number_format((float) ($refill->service_estimasi_biaya ?? 0), 0, ',', '.'),
                'ukuran' => $refill->service_ukuran_apar ?? '-',
                'unit' => (int) ($refill->service_jumlah_unit ?? 0),
                'source' => $refill->adminSourceLabel(),
                'teknisi' => $refill->teknisi?->name ?? 'Belum ditugaskan',
                'catatan' => $refill->catatan_admin ?: $refill->serviceCustomerNote() ?: $refill->keterangan ?: '-',
                'status' => $refill->status,
                'status_label' => $refill->publicStatusLabel(),
                'hide_payment_badge' => $refill->shouldHidePaymentStatusBadge(),
                'is_paid' => $refill->isPaymentConfirmed(),
                'proof_url' => !empty($refill->bukti_pembayaran) ? '/storage/' . ltrim($refill->bukti_pembayaran, '/') : null,
                'unit_display' => $unitDisplay,
            ];
        })->concat($mergedHistory->map(function ($item) use ($resolveCustomer, $resolveUnitDisplay) {
            $isLegacy = $item instanceof \App\Models\Refill;
            $pesanan = $isLegacy ? null : $item;
            $refill = $isLegacy ? $item : null;
            $customer = $resolveCustomer($pesanan, $refill);
            $unitDisplay = $isLegacy
                ? $resolveUnitDisplay($refill, $refill?->unitApar)
                : $resolveUnitDisplay($pesanan, $pesanan?->service?->unitApar);
            
            return [
                'id' => $isLegacy ? 'log-' . $refill->id : $pesanan->id,
                'pelanggan' => $customer['nama'],
                'no_wa' => $customer['wa'],
                'wa_url' => \App\Support\WhatsApp::customerLink(
                    $customer['wa'],
                    'Halo Bapak/Ibu, kami ingin mengonfirmasi ' . strtolower($pesanan ? $pesanan->transactionDisplayName() : $refill->transactionDisplayName()) . ' APAR Anda.'
                ),
                'alamat' => $pesanan?->pelanggan?->alamat ?? $refill?->unitApar?->pelanggan?->alamat ?? '-',
                'transaksi' => $pesanan ? $pesanan->transactionDisplayName() : $refill->transactionDisplayName(),
                'waktu' => $pesanan ? $pesanan->displayTransactionDateTime() : $refill->displayTransactionDateTime(),
                'jenis' => $pesanan?->serviceJenisRefill?->nama_label ?? $refill?->jenisRefill?->nama_label ?? 'Refill APAR',
                'estimasi' => number_format((float) ($pesanan?->payableTotal() ?? $refill?->biaya ?? 0), 0, ',', '.'),
                'ukuran' => $pesanan?->service_ukuran_apar ?? $refill?->unitApar?->produk?->kapasitas ?? '-',
                'unit' => $pesanan ? (int) ($pesanan->service_jumlah_unit ?? 1) : 1,
                'source' => $pesanan ? $pesanan->adminSourceLabel() : 'Riwayat Lama',
                'teknisi' => $pesanan?->teknisi?->name ?? 'Selesai',
                'catatan' => $pesanan?->catatan_admin ?: $pesanan?->serviceCustomerNote() ?: $refill?->service?->keterangan ?: '-',
                'status' => $pesanan?->status ?? 'selesai final',
                'status_label' => $pesanan?->publicStatusLabel() ?? 'Selesai Final',
                'hide_payment_badge' => $pesanan ? $pesanan->shouldHidePaymentStatusBadge() : true,
                'is_paid' => $pesanan ? $pesanan->isPaymentConfirmed() : true,
                'proof_url' => !empty($pesanan?->bukti_pembayaran) ? '/storage/' . ltrim($pesanan->bukti_pembayaran, '/') : (!empty($refill?->service?->pesanan?->bukti_pembayaran) ? '/storage/' . ltrim($refill->service->pesanan->bukti_pembayaran, '/') : null),
                'unit_display' => $unitDisplay,
            ];
        }))->values();
    @endphp

    <div class="space-y-8" x-data="{ openModal: {{ $errors->any() ? 'true' : 'false' }} }">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Permintaan</p>
                <p class="text-4xl font-black text-gray-900">{{ $requestRefills->count() + $completedRequestRefills->count() + $legacyRefills->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Transaksi Pelanggan</p>
                <p class="text-4xl font-black text-emerald-700">{{ $requestRefills->count() - $riwayatLamaRefills->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Riwayat Lama</p>
                <p class="text-4xl font-black text-amber-700">{{ $riwayatLamaRefills->count() }}</p>
            </div>
            <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Proses Teknisi</p>
                <p class="text-4xl font-black text-red-700">{{ $dikerjakanTeknisi->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Data Refill dari Pelanggan</h3>
                <p class="mt-1 text-sm font-semibold text-gray-500">Permintaan refill yang sedang diproses admin.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit APAR</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Refill</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($requestRefills as $refill)
                            @php
                                $refillCustomer = $resolveCustomer($refill);
                                $isLegacySource = $refill->isLegacyAdminSource();
                                $canAssign = $refill->isPaymentConfirmed() && !$refill->teknisi_id;
                                $statusBadge = match ((string) $refill->status) {
                                    'selesai final', 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
                                    'dikonfirmasi admin' => ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI ADMIN'],
                                    'selesai oleh teknisi' => ['bg-cyan-50 text-cyan-700', 'SELESAI OLEH TEKNISI'],
                                    'dikerjakan teknisi' => ['bg-indigo-50 text-indigo-700', 'SEDANG DIKERJAKAN'],
                                    'ditugaskan ke teknisi' => ['bg-purple-50 text-purple-700', 'DITUGASKAN'],
                                    'diproses' => ['bg-red-50 text-blue-700', 'DIPROSES'],
                                    default => ['bg-amber-50 text-amber-700', 'MENUNGGU'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/30 transition-colors">
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-gray-900">{{ $refill->displayTransactionDateTime() }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $refill->transactionDisplayName() }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $refillCustomer['nama'] }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $refillCustomer['wa'] }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    @include('admin.partials.unit-apar-column', ['pesanan' => $refill, 'unitApar' => $refill->service?->unitApar])
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900">{{ $refill->serviceJenisRefill?->nama_label ?? 'Refill APAR' }}</p>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format((float) ($refill->service_estimasi_biaya ?? 0), 0, ',', '.') }}</span>
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
                                                onclick="openRefillProofModal(@js(!empty($refill->bukti_pembayaran) ? '/storage/' . ltrim($refill->bukti_pembayaran, '/') : null), @js([
                                                    "customer" => $refill->pelanggan?->nama ?? "-",
                                                    "date" => $refill->displayTransactionDateTime(),
                                                    "type" => "Refill",
                                                ]))"
                                                class="{{ $actionButtonProof }}"
                                                style="{{ $actionButtonProofStyle }}"
                                            >
                                                Bukti TF
                                            </button>
                                        @endif
                                        @if(in_array((string) $refill->status, ['selesai oleh teknisi', 'dikonfirmasi admin'], true))
                                            <form action="{{ route('admin.pesanan.selesai-final', $refill) }}" method="POST" data-confirm="Selesaikan final refill ini?" data-confirm-title="Konfirmasi Final" data-confirm-button="Ya, Finalkan">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonSuccess }}">Final</button>
                                            </form>
                                        @elseif($canAssign)
                                            <form action="{{ route('admin.refill.assign-teknisi', $refill) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="{{ $actionButtonPrimary }}">Assign</button>
                                            </form>
                                        @endif
                                        <button type="button" onclick="openRefillDetailModal({{ $refill->id }})" class="{{ $actionButtonNeutral }}">
                                            Detail
                                        </button>
                                        
                                        <a href="{{ route('invoice.show', $refill) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                            Lihat Invoice
                                        </a>

                                        @if($refill->status !== 'selesai' && $refill->status !== 'selesai final')
                                            <form action="{{ route('admin.pesanan.destroy', $refill) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus data refill ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
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
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data refill dari pelanggan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Riwayat Data Refill</h3>
                <p class="mt-1 text-xs font-semibold text-gray-500">Data refill yang sudah tercatat pada log refill.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit APAR</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Refill</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mergedHistory as $item)
                            @php
                                $isLegacy = $item instanceof \App\Models\Refill;
                                $pesanan = $isLegacy ? null : $item;
                                $refill = $isLegacy ? $item : null;
                                
                                $refillHistoryIsLegacy = $pesanan ? $pesanan->isLegacyAdminSource() : true;
                                
                                $tanggal = $pesanan ? $pesanan->displayTransactionDateTime() : $refill->displayTransactionDateTime();
                                $trxName = $pesanan ? $pesanan->transactionDisplayName() : $refill->transactionDisplayName();
                                $refillCustomer = $resolveCustomer($pesanan, $refill);
                                $pelangganNama = $refillCustomer['nama'];
                                $pelangganWa = $refillCustomer['wa'];
                                $jenisRefill = $pesanan ? ($pesanan->serviceJenisRefill?->nama_label ?? 'Refill APAR') : ($refill->jenisRefill?->nama_label ?? '-');
                                $totalBiaya = $pesanan ? ($pesanan->total_harga ?? 0) : ($refill->biaya ?? 0);
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
                                    @include('admin.partials.unit-apar-column', ['pesanan' => $pesanan ?? $refill->service?->pesanan, 'unitApar' => $refill?->unitApar ?? $refill?->service?->unitApar ?? $pesanan?->service?->unitApar])
                                </td>
                                <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $jenisRefill }}</td>
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
                                        $refillProofUrl = !empty($pesanan?->bukti_pembayaran)
                                            ? '/storage/' . ltrim($pesanan->bukti_pembayaran, '/')
                                            : (!empty($refill?->service?->pesanan?->bukti_pembayaran) ? '/storage/' . ltrim($refill->service->pesanan->bukti_pembayaran, '/') : null);
                                    @endphp
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!$refillHistoryIsLegacy && $refillProofUrl)
                                            <button
                                                type="button"
                                                onclick="openRefillProofModal(@js($refillProofUrl), @js([
                                                    "customer" => $pelangganNama,
                                                    "date" => $tanggal,
                                                    "type" => "Refill",
                                                ]))"
                                                class="{{ $actionButtonProof }}"
                                                style="{{ $actionButtonProofStyle }}"
                                            >
                                                Bukti TF
                                            </button>
                                        @endif
                                        <button type="button" onclick="openRefillDetailModal('{{ $isLegacy ? 'log-' . $refill->id : $pesanan->id }}')" class="{{ $actionButtonNeutral }}" title="Detail">
                                            Detail
                                        </button>
                                        
                                        @if($pesanan)
                                            <a href="{{ route('invoice.show', $pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                                Lihat Invoice
                                            </a>
                                        @elseif($refill?->service?->pesanan)
                                            <a href="{{ route('invoice.show', $refill->service->pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                                Lihat Invoice
                                            </a>
                                        @else
                                            <button type="button" onclick="showAppAlert('Invoice tidak tersedia untuk data legacy ini.', 'warning', 'Peringatan')" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                                                Lihat Invoice
                                            </button>
                                        @endif
                                        
                                        @if($pesanan)
                                        <form action="{{ route('admin.pesanan.destroy', $pesanan) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus data refill ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
                                        </form>
                                        @elseif($isLegacy)
                                        <form action="{{ route('admin.refill.destroy', $refill) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus riwayat refill ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
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
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data refill pada log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="refill-detail-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" onclick="closeRefillDetailModal()"></div>
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-gray-100 z-10">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Detail Data Refill</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5" id="refill-detail-subtitle"></p>
                    </div>
                    <button onclick="closeRefillDetailModal()" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5" id="refill-detail-content"></div>
            </div>
        </div>

        <div id="refill-proof-modal" class="hidden fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/70 backdrop-blur-sm" onclick="closeRefillProofModal()"></div>
            <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900" id="refill-proof-title">Bukti Transfer</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">Preview bukti pembayaran pelanggan</p>
                    </div>
                    <button type="button" onclick="closeRefillProofModal()" class="rounded-xl bg-gray-50 p-2 text-gray-400 transition hover:text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="refill-proof-body" class="max-h-[78vh] overflow-auto bg-gray-50 p-6"></div>
            </div>
        </div>

        @if(false)
        <div x-show="openModal" x-cloak class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto p-3 sm:items-center sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openModal = false"></div>
            <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95" class="app-modal-shell relative my-3 max-w-5xl sm:my-6">
                <div class="app-modal-header flex items-start justify-between gap-4 bg-gradient-to-r from-slate-800 to-slate-700 px-5 py-4 sm:items-center sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-600/30 border border-red-500/30 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tight leading-tight">Input Refill Offline</h3>
                            <p class="text-sm text-white/70 font-medium mt-0.5">Form ini digunakan untuk mencatat layanan refill APAR dari pelanggan yang datang langsung ke toko.</p>
                        </div>
                    </div>
                    <button type="button" @click="openModal = false" class="w-10 h-10 rounded-2xl bg-white/10 text-white/60 hover:text-white hover:bg-white/20 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="app-modal-body flex-1 p-5 sm:p-6 lg:p-8" x-data="refillOfflineForm(@js($refillOfflineOptions), @js([
                    'jenis_refill_id' => old('jenis_refill_id'),
                    'ukuran_apar' => old('ukuran_apar', '6 Kg'),
                    'jumlah_unit' => old('jumlah_unit', 1),
                    'status_unit' => old('status_unit', 'belum_terdaftar'),
                    'pelanggan_id' => old('pelanggan_id'),
                    'selected_unit_ids' => collect(old('unit_apar_ids', old('unit_apar_id') ? [old('unit_apar_id')] : []))->map(fn ($id) => (string) $id)->values(),
                ]), @js($pelanggans->map(fn($p) => [
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
                    <form action="{{ route('admin.refill.store') }}" method="POST">
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
                                                <option value="{{ $pelanggan->id }}" @selected(old('pelanggan_id') == $pelanggan->id)>{{ $pelanggan->nama }} ({{ $pelanggan->no_wa }})</option>
                                            @endforeach
                                        </select>
                                        <p class="text-[10px] font-semibold leading-relaxed text-slate-500">
                                            Pelanggan refill offline harus berasal dari akun role pelanggan. Jika belum ada, buat akun pelanggan terlebih dahulu melalui
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
                                        <h4 class="font-black text-gray-900 uppercase tracking-wider text-xs">Informasi Refill</h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                        <div class="md:col-span-4">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Status Unit APAR <span class="text-red-500">*</span></label>
                                            <select x-model="statusUnit" @change="syncUnitApar" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm">
                                                <option value="belum_terdaftar">APAR Belum Terdaftar (Manual)</option>
                                                <option value="terdaftar">APAR Terdaftar</option>
                                            </select>
                                            <input type="hidden" name="status_unit" :value="statusUnit">
                                        </div>

                                        <template x-if="statusUnit === 'terdaftar'">
                                            <div class="md:col-span-4">
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
                                        </template>

                                        <div class="md:col-span-2">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Refill <span class="text-red-500">*</span></label>
                                            <select x-model="jenisRefillId" @change="updateUkuranOptions()" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm">
                                                <option value="">Pilih jenis refill</option>
                                                <template x-for="jenis in jenisRefills" :key="jenis.id">
                                                    <option :value="String(jenis.id)" x-text="jenis.nama"></option>
                                                </template>
                                            </select>
                                            <input type="hidden" name="jenis_refill_id" :value="jenisRefillId">
                                        </div>
                                        <div class="md:col-span-2" x-show="statusUnit === 'belum_terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Ukuran APAR <span class="text-red-500">*</span></label>
                                            <select x-model="ukuranApar" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm">
                                                <template x-for="ukuran in ukuranOptions" :key="ukuran">
                                                    <option :value="ukuran" x-text="ukuran"></option>
                                                </template>
                                            </select>
                                            <input type="hidden" name="ukuran_apar" :value="ukuranApar" x-bind:disabled="statusUnit !== 'belum_terdaftar'">
                                        </div>
                                        <div class="md:col-span-2" x-show="statusUnit === 'belum_terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah Unit <span class="text-red-500">*</span></label>
                                            <input type="number" min="1" x-model.number="jumlahUnit" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 text-sm">
                                            <input type="hidden" name="jumlah_unit" :value="jumlahUnit" x-bind:disabled="statusUnit !== 'belum_terdaftar'">
                                        </div>
                                        <div class="md:col-span-2" x-show="statusUnit === 'terdaftar'" x-cloak>
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jumlah Unit Terpilih</label>
                                            <div class="w-full rounded-2xl bg-gray-50 px-6 py-4 text-sm font-black text-gray-900" x-text="displayJumlahUnit() + ' unit'"></div>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Refill <span class="text-red-500">*</span></label>
                                            <input type="date" name="tgl_refill" value="{{ old('tgl_refill', now()->format('Y-m-d')) }}" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                        </div>
                                    </div>
                                    <div x-show="hargaTidakTersedia()" x-cloak class="px-4 py-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <p class="text-xs font-bold text-amber-800">Harga standar refill untuk jenis dan ukuran ini belum tersedia.</p>
                                    </div>
                                    <div>
                                        <label for="catatan_admin" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Catatan <span class="text-gray-300">(Opsional)</span></label>
                                        <textarea name="catatan_admin" id="catatan_admin" rows="4" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition text-sm resize-none" placeholder="Catatan tambahan untuk teknisi atau administrasi...">{{ old('catatan_admin') }}</textarea>
                                    </div>
                                    <div class="px-4 py-3 bg-emerald-50 rounded-xl border border-emerald-200">
                                        <p class="text-xs font-bold text-emerald-800">Transaksi offline langsung dianggap <span class="font-black">lunas</span>, tanpa metode penanganan, dan stok refill baru berkurang saat status <span class="font-black">Selesai Final</span>.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="sticky-summary-xl rounded-3xl border border-gray-100 bg-gray-50 p-5 shadow-sm sm:p-6">
                                    <div class="flex items-center gap-2 border-b border-gray-200/60 pb-3">
                                        <span class="w-6 h-6 rounded-md bg-red-50 text-red-700 font-black text-xs flex items-center justify-center shrink-0">3</span>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Offline</p>
                                    </div>
                                    <div class="mt-6 space-y-4">
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Layanan</span>
                                            <span class="font-black text-red-700">Refill Offline</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Harga Standar</span>
                                            <span class="font-bold text-gray-900" x-text="currency(hargaRingkasan())"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Jumlah Unit</span>
                                            <span class="font-bold text-gray-900" x-text="displayJumlahUnit() + ' unit'"></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                            <span>Detail APAR</span>
                                            <span class="font-bold text-gray-900 text-right" x-text="displayDetailApar()"></span>
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
                            <button type="button" @click="openModal = false" class="w-full px-8 py-4 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900 sm:w-auto">Batal</button>
                            <button type="submit" :disabled="!canSubmit()" :class="!canSubmit() ? 'bg-gray-300 text-gray-500 shadow-none cursor-not-allowed' : 'bg-red-700 text-white hover:bg-red-800 shadow-xl shadow-red-700/30'" class="w-full rounded-2xl px-10 py-4 text-xs font-black uppercase tracking-widest transition sm:w-auto">
                                Simpan Refill Offline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        const refillDetailData = @json($refillDetailData);

        function openRefillDetailModal(id) {
            const data = refillDetailData.find((item) => String(item.id) === String(id));
            if (!data) return;

            document.getElementById('refill-detail-subtitle').textContent = `${data.transaksi} - ${data.waktu}`;
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

            document.getElementById('refill-detail-content').innerHTML = `
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
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jenis Refill</p>
                        <p class="font-black text-emerald-700">${data.jenis}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total</p>
                        <p class="font-black text-gray-900">Rp ${data.estimasi}</p>
                    </div>
                </div>
                ${proofHtml}
                ${data.catatan !== '-' ? `<div class="rounded-xl border border-amber-100 bg-amber-50 p-4"><span class="text-[10px] font-black text-amber-600 uppercase">Catatan</span><p class="mt-1 text-sm font-semibold whitespace-pre-line">${data.catatan}</p></div>` : ''}
                <div class="flex flex-wrap justify-center gap-3">
                    ${data.wa_url ? `<a href="${data.wa_url}" target="_blank" rel="noopener noreferrer" class="px-6 py-3 bg-green-500 text-white font-black text-xs uppercase rounded-xl hover:bg-green-600 transition">Hubungi Pelanggan</a>` : ''}
                    <button type="button" onclick="closeRefillDetailModal()" class="px-8 py-3 bg-gray-200 text-gray-700 font-black text-xs uppercase rounded-xl hover:bg-gray-300 transition">Tutup</button>
                </div>
            `;

            document.getElementById('refill-detail-modal').classList.remove('hidden');
        }

        function closeRefillDetailModal() {
            document.getElementById('refill-detail-modal').classList.add('hidden');
        }

        function openRefillProofModal(url, meta = {}) {
            const modal = document.getElementById('refill-proof-modal');
            const body = document.getElementById('refill-proof-body');
            const heading = document.getElementById('refill-proof-title');
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
                            <p class="mt-1 text-sm font-semibold text-gray-900">${meta.type || 'Refill'}</p>
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

        function closeRefillProofModal() {
            document.getElementById('refill-proof-modal').classList.add('hidden');
            document.getElementById('refill-proof-body').innerHTML = '';
        }

        function refillOfflineForm(jenisRefills, initialState, pelanggansData) {
            return {
                jenisRefills,
                pelanggansData: pelanggansData || [],
                selectedPelangganId: initialState?.pelanggan_id || '',
                selectedPelangganInfo: {},
                statusUnit: initialState?.status_unit || 'belum_terdaftar',
                selectedUnitAparIds: Array.isArray(initialState?.selected_unit_ids)
                    ? initialState.selected_unit_ids.map(String)
                    : [],
                jenisRefillId: String(initialState?.jenis_refill_id || ''),
                ukuranApar: String(initialState?.ukuran_apar || ''),
                jumlahUnit: Number(initialState?.jumlah_unit || 1),
                _allUkuranOptions: @js($ukuranAparOptions),
                init() {
                    if (this.selectedPelangganId) {
                        const info = this.pelanggansData.find(p => p.id === this.selectedPelangganId);
                        this.selectedPelangganInfo = info || {};
                    }
                    if (!this.ukuranApar) {
                        this.updateUkuranOptions();
                    }
                    this.syncUnitApar();
                },
                get ukuranOptions() {
                    const selected = this.selectedJenis();
                    if (!selected || !selected.rules || !selected.rules.length) {
                        return this._allUkuranOptions;
                    }

                    return selected.rules.map(r => r.ukuran).filter(Boolean);
                },
                updateUkuranOptions() {
                    const selected = this.selectedJenis();
                    const sizes = (selected && selected.rules && selected.rules.length)
                        ? selected.rules.map(r => r.ukuran).filter(Boolean)
                        : this._allUkuranOptions;

                    this.ukuranApar = sizes.includes(this.ukuranApar) ? this.ukuranApar : (sizes[0] || '');
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
                        if (!this.jenisRefillId && this.selectedRegisteredUnits().length === 1) {
                            const unit = this.selectedRegisteredUnits()[0];
                            const matchedJenis = this.jenisRefills.find(j => (j.nama || '').toLowerCase().includes((unit.jenis || '').toLowerCase()));
                            if (matchedJenis) {
                                this.jenisRefillId = String(matchedJenis.id);
                            }
                        }
                    } else if (this.statusUnit === 'belum_terdaftar') {
                        this.selectedUnitAparIds = [];
                        this.jumlahUnit = 1;
                    }
                },
                selectedJenis() {
                    return this.jenisRefills.find((item) => String(item.id) === String(this.jenisRefillId)) || null;
                },
                selectedRegisteredUnits() {
                    const selectedIds = new Set(this.selectedUnitAparIds.map(String));
                    return (this.selectedPelangganInfo?.units || []).filter(unit => selectedIds.has(String(unit.id)));
                },
                resolveHargaUntukUkuran(ukuran) {
                    const jenis = this.selectedJenis();
                    if (!jenis) return 0;
                    const rules = Array.isArray(jenis.rules) ? jenis.rules : [];

                    const ukuranNormalized = String(ukuran || '').trim().toLowerCase();
                    const exactRule = rules.find((rule) => String(rule.ukuran || '').trim().toLowerCase() === ukuranNormalized);
                    if (exactRule && Number(exactRule.harga) > 0) {
                        return Number(exactRule.harga);
                    }

                    const ukuranMatch = ukuranNormalized.match(/(\d+(?:[.,]\d+)?)/);
                    if (ukuranMatch) {
                        const ukuranAngka = Number(String(ukuranMatch[1]).replace(',', '.'));
                        const numericRule = rules.find((rule) => {
                            const ruleMatch = String(rule.ukuran || '').trim().toLowerCase().match(/(\d+(?:[.,]\d+)?)/);
                            if (!ruleMatch) return false;
                            return Number(String(ruleMatch[1]).replace(',', '.')) === ukuranAngka;
                        });
                        if (numericRule && Number(numericRule.harga) > 0) {
                            return Number(numericRule.harga);
                        }
                    }

                    if (rules.length > 0) {
                        return 0;
                    }

                    const fallback = Number(jenis.harga || 0);
                    return fallback > 0 ? fallback : 0;
                },
                hargaPerUnit() {
                    return this.resolveHargaUntukUkuran(this.ukuranApar);
                },
                hargaRingkasan() {
                    if (this.statusUnit === 'terdaftar') {
                        const units = this.selectedRegisteredUnits();
                        if (units.length === 0) return 0;
                        return units.reduce((total, unit) => total + this.resolveHargaUntukUkuran(unit.ukuran), 0);
                    }

                    return this.hargaPerUnit();
                },
                hargaTidakTersedia() {
                    if (!this.selectedJenis()) {
                        return false;
                    }

                    if (this.statusUnit === 'terdaftar') {
                        const units = this.selectedRegisteredUnits();
                        return units.length > 0 && units.some(unit => this.resolveHargaUntukUkuran(unit.ukuran) <= 0);
                    }

                    return this.hargaPerUnit() <= 0;
                },
                totalBiaya() {
                    if (this.statusUnit === 'terdaftar') {
                        return this.selectedRegisteredUnits().reduce(
                            (total, unit) => total + this.resolveHargaUntukUkuran(unit.ukuran),
                            0
                        );
                    }

                    return this.hargaPerUnit() * Math.max(1, Number(this.jumlahUnit || 0));
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

                    return `APAR ${this.selectedJenis()?.nama || '-'} ${this.ukuranApar || '-'}`.trim();
                },
                canSubmit() {
                    if (!this.selectedPelangganId || !this.jenisRefillId) {
                        return false;
                    }

                    if (this.statusUnit === 'terdaftar') {
                        return this.selectedRegisteredUnits().length > 0 && !this.hargaTidakTersedia();
                    }

                    return !!this.ukuranApar && Number(this.jumlahUnit || 0) > 0 && !this.hargaTidakTersedia();
                },
                currency(value) {
                    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
                },
            }
        }
    </script>
</x-app-layout>
