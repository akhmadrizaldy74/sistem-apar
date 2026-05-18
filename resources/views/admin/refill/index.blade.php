<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Refil APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola data refil APAR sesuai alur admin dan teknisi.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-refill-modal'))" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Data Refil
            </button>
        </div>
    </x-slot>

    <div
        class="space-y-8"
        x-data="{
            search: '',
            openModal: false,
        }"
        @open-refill-modal.window="openModal = true"
    >
        {{-- Stats --}}
        @php
            $antrianAssign = $requestRefills->filter(fn($r) => $r->isPaymentConfirmed() && !$r->teknisi_id)->count();
            $sedangDikerjakan = $requestRefills->filter(fn($r) => in_array($r->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi']))->count();
            $menungguBayar = $requestRefills->filter(fn($r) => !$r->isPaymentConfirmed())->count();
            $totalDataRefil = $refills->count() + $requestRefills->count();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Data Refil</p>
                <p class="text-4xl font-black text-gray-900">{{ $totalDataRefil }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Input Admin</p>
                <p class="text-4xl font-black text-emerald-600">{{ $refills->count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Dari Pelanggan</p>
                <p class="text-4xl font-black text-amber-700">{{ $requestRefills->count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Dikerjakan Teknisi</p>
                <p class="text-4xl font-black text-emerald-600">{{ $sedangDikerjakan }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                <h3 class="text-lg font-black text-gray-900">Data Refil APAR</h3>
                <p class="mt-1 text-xs font-semibold text-gray-500">Data refil yang ditambah atau diubah admin.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit APAR</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Refil</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Biaya</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($refills as $refill)
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-xs font-bold text-gray-900">{{ optional($refill->tgl_refill)->format('d M Y') }}</p>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">RFL-{{ $refill->id }}</p>
                                </td>
                                <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $refill->unitApar?->pelanggan?->nama ?? '-' }}</td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-gray-900">{{ $refill->unitApar?->no_seri ?? '-' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ $refill->unitApar?->produk?->nama ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $refill->jenisRefill?->nama ?? '-' }}</td>
                                <td class="px-8 py-5 text-sm font-black text-gray-900">Rp {{ number_format((float) ($refill->biaya ?? 0), 0, ',', '.') }}</td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.refill.edit', $refill) }}" class="px-3 py-2 rounded-xl border border-blue-100 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 transition">Edit</a>
                                        <form action="{{ route('admin.refill.destroy', $refill) }}" method="POST" onsubmit="return confirm('Hapus data refil ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 rounded-xl border border-red-100 bg-red-50 text-red-700 text-[10px] font-black uppercase tracking-widest hover:bg-red-100 transition">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data refil APAR dari admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Table Data Refil --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 space-y-4 bg-gray-50/30 border-b border-gray-50">
                <h3 class="text-lg font-black text-gray-900">Data Refil dari Pelanggan</h3>
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
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Detail</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Estimasi</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($requestRefills as $refill)
                        @php
                            $isPaid = $refill->isPaymentConfirmed();
                            $canAssign = $isPaid && !$refill->teknisi_id;
                            $s = $refill->status;
                            $statusBadge = match(true) {
                                $s === 'selesai final' || $s === 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
                                $s === 'dikonfirmasi admin' => ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI'],
                                $s === 'selesai oleh teknisi' => ['bg-emerald-50 text-emerald-700', 'SELESAI TEKNISI'],
                                $s === 'dikerjakan teknisi' => ['bg-indigo-50 text-indigo-700', 'DIKERJAKAN'],
                                $s === 'ditugaskan ke teknisi' => ['bg-purple-50 text-purple-700', 'DITUGASKAN'],
                                $refill->teknisi_id => ['bg-blue-50 text-blue-700', 'SIAP KERJA'],
                                $isPaid => ['bg-amber-50 text-amber-700', 'LUNAS'],
                                default => ['bg-gray-100 text-gray-600', 'MENUNGGU BAYAR'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-8 py-6">
                                <p class="text-xs font-bold text-gray-900">{{ optional($refill->tanggal)->format('d M Y') }}</p>
                                <p class="text-[10px] font-black text-gray-400 mt-1">RFL-{{ $refill->id }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-bold text-gray-900">{{ $refill->pelanggan?->nama ?? '-' }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $refill->pelanggan?->no_wa ?? '-' }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="inline-flex px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase">
                                    Refil
                                </span>
                                <p class="text-xs font-semibold text-gray-500 mt-2">{{ $refill->service_ukuran_apar ?? '-' }} - {{ (int) ($refill->service_jumlah_unit ?? 0) }} unit</p>
                                <p class="text-[10px] font-semibold text-gray-400 mt-1">Metode: {{ $refill->service_metode_penanganan ?? '-' }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-black text-gray-900">Rp {{ number_format((float) ($refill->service_estimasi_biaya ?? 0), 0, ',', '.') }}</p>
                                @if($refill->service_total_kg)
                                    <p class="text-[10px] font-semibold text-gray-500 mt-1">{{ rtrim(rtrim(number_format((float) $refill->service_total_kg, 2, ',', '.'), '0'), ',') }} kg</p>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusBadge[0] }}">
                                    {{ $statusBadge[1] }}
                                </span>
                                @if($refill->teknisi_id)
                                    <p class="mt-2 text-[10px] font-bold text-red-600">{{ $refill->teknisi->name }}</p>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    @if($refill->bukti_pembayaran)
                                        <a href="{{ asset('storage/' . ltrim($refill->bukti_pembayaran, '/')) }}" target="_blank" class="px-3 py-2 rounded-xl border border-sky-200 bg-sky-50 text-sky-700 text-[10px] font-black uppercase tracking-widest hover:bg-sky-100 transition shadow-sm">
                                            Bukti Bayar
                                        </a>
                                    @endif
                                    @if($canAssign)
                                        <div class="relative" x-data="{ show: false }">
                                            <button type="button" @click="show = !show" class="px-3 py-2 bg-red-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-red-700 transition shadow-sm flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Tugas
                                            </button>
                                            <div x-show="show" @click.away="show = false" x-cloak class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 p-4">
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tugaskan Teknisi</p>
                                                <form action="{{ route('admin.refill.assign-teknisi', $refill) }}" method="POST" class="space-y-3">
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
                                    @elseif($refill->teknisi_id)
                                        <div class="px-3 py-1.5 bg-red-50 rounded-xl border border-red-100 flex flex-col items-end mr-1">
                                            <span class="text-[8px] font-black uppercase tracking-widest text-red-400">Ditugaskan</span>
                                            <span class="text-[10px] font-bold text-red-700 whitespace-nowrap">{{ $refill->teknisi->name }}</span>
                                        </div>
                                    @endif
                                    <form action="{{ route('admin.pesanan.destroy', $refill) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin ingin menghapus data refil ini?')" class="p-2.5 bg-white text-gray-400 hover:text-red-600 rounded-xl border border-gray-100 hover:border-red-100 hover:shadow-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                    <button type="button" onclick="openDetailModal({{ $refill->id }})" class="px-3 py-2 bg-white text-gray-600 border border-gray-200 rounded-xl text-[10px] font-black uppercase hover:bg-gray-50 transition shadow-sm">
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center">
                                <p class="text-gray-400 font-medium">Belum ada data refil aktif.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Detail Modal --}}
        <div id="detail-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" onclick="closeDetailModal()"></div>
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-gray-100 z-10">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Detail Data Refil</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5" id="detail-modal-subtitle"></p>
                    </div>
                    <button onclick="closeDetailModal()" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5" id="detail-modal-content"></div>
            </div>
        </div>

        {{-- INPUT MANUAL MODAL --}}
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
                class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between px-8 py-6 bg-white/95 backdrop-blur border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Tambah Data Refil APAR</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Isi data refil APAR, lalu klik Simpan.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-8 sm:p-10">
                    <form action="{{ route('admin.refill.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Unit APAR</label>
                                <select name="unit_apar_id" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->no_seri }} - {{ $unit->pelanggan->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Refil</label>
                                <select name="jenis_refill_id" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                    <option value="">-- Pilih Jenis --</option>
                                    @foreach($jenisRefills as $jr)
                                        <option value="{{ $jr->id }}">{{ $jr->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal</label>
                                <input type="date" name="tgl_refill" value="{{ now()->format('Y-m-d') }}" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Biaya</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400">Rp</span>
                                    <input type="number" name="biaya" required min="0" class="w-full pl-12 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900" placeholder="250000">
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end gap-4">
                            <button type="button" @click="openModal = false" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</button>
                            <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
    $refillDetailData = $requestRefills->map(function($r) {
        return [
            'id' => $r->id,
            'pelanggan' => $r->pelanggan?->nama ?? '-',
            'no_wa' => $r->pelanggan?->no_wa ?? '-',
            'alamat' => $r->pelanggan?->alamat ?? '-',
            'jenis' => $r->serviceJenisRefill?->nama_label ?? 'Refil',
            'estimasi' => number_format((float) ($r->service_estimasi_biaya ?? 0), 0, ',', '.'),
            'ukuran' => $r->service_ukuran_apar ?? '-',
            'unit' => (int) ($r->service_jumlah_unit ?? 0),
            'metode' => $r->service_metode_penanganan === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput',
            'total_kg' => $r->service_total_kg,
            'teknisi' => $r->teknisi?->name ?? 'Belum',
            'catatan' => $r->service_keluhan ?? '-',
            'status' => $r->status,
            'is_paid' => $r->isPaymentConfirmed(),
        ];
    })->toArray();
    @endphp

    <script>
    const refillDetailData = @json($refillDetailData);

    function openDetailModal(id) {
        const data = refillDetailData.find(r => r.id === id);
        if (!data) return;

        document.getElementById('detail-modal-subtitle').textContent = 'RFL-' + id + ' - ' + data.pelanggan;

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
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jenis</p>
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
