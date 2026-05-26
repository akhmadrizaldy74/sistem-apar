<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Monitoring APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Pantau kelayakan dan masa berlaku unit baru maupun unit lama milik pelanggan</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-unit-modal'))" class="px-8 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white font-black rounded-2xl hover:from-red-800 hover:to-red-900 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Registrasi APAR
            </button>
        </div>
    </x-slot>

    @php
        $today = now();
        $unitItems = $units->map(function ($unit) use ($today) {
            $isExpired = $unit->tgl_expired && $unit->tgl_expired->lte($today);
            $daysLeft = $unit->tgl_expired ? $today->diffInDays($unit->tgl_expired, false) : null;
            $isNearExpiry = ! $isExpired && $daysLeft !== null && $daysLeft <= 30;
            $status = $isExpired ? 'expired' : ($isNearExpiry ? 'hampir' : 'aktif');

            $namaPelanggan = (string) ($unit->pelanggan->nama ?? 'Pelanggan');
            $nomorUnit = (string) ($unit->no_seri ?? '-');
            $namaProduk = (string) ($unit->produk->nama ?? '-');
            $tanggalExpired = $unit->tgl_expired ? $unit->tgl_expired->format('d M Y') : '-';
            $waRaw = (string) ($unit->pelanggan->no_wa ?? '');
            $waDigits = preg_replace('/\D+/', '', $waRaw);

            if (str_starts_with($waDigits, '0')) {
                $waDigits = '62'.substr($waDigits, 1);
            } elseif (str_starts_with($waDigits, '8')) {
                $waDigits = '62'.$waDigits;
            }

            $needsReminder = in_array($status, ['hampir', 'expired'], true);
            $waTooltip = '';
            $waMessage = '';
            if ($status === 'hampir') {
                $waTooltip = 'Kirim pengingat ke pelanggan';
                $waMessage = "Halo Bapak/Ibu {$namaPelanggan}, kami ingin menginformasikan bahwa unit APAR Anda dengan nomor unit {$nomorUnit} dan jenis {$namaProduk} akan segera memasuki masa kedaluwarsa pada {$tanggalExpired}. Silakan hubungi kami untuk pengecekan, servis, atau isi ulang. Terima kasih.";
            } elseif ($status === 'expired') {
                $waTooltip = 'Kirim notifikasi expired ke pelanggan';
                $waMessage = "Halo Bapak/Ibu {$namaPelanggan}, kami ingin menginformasikan bahwa unit APAR Anda dengan nomor unit {$nomorUnit} dan jenis {$namaProduk} telah melewati masa berlaku pada {$tanggalExpired}. Mohon segera dilakukan pengecekan, servis, atau isi ulang agar tetap aman digunakan. Terima kasih.";
            }

            return [
                'id' => (int) $unit->id,
                'pelanggan_id' => (string) $unit->pelanggan_id,
                'pelanggan_nama' => (string) ($unit->pelanggan->nama ?? '-'),
                'no_seri' => (string) ($unit->no_seri ?? '-'),
                'produk_nama' => (string) ($unit->produk->nama ?? '-'),
                'ukuran' => (string) ($unit->ukuran ?? '-'),
                'bahan' => (string) ($unit->bahan ?? '-'),
                'tgl_expired_label' => $unit->tgl_expired ? $unit->tgl_expired->format('d M Y') : '-',
                'tgl_beli_label' => optional($unit->tgl_beli ?? $unit->tgl_produksi)->format('d M Y') ?: '-',
                'status' => $status,
                'search_text' => strtolower(trim(($unit->pelanggan->nama ?? '').' '.($unit->no_seri ?? '').' '.($unit->produk->nama ?? ''))),
                'wa_url' => ($needsReminder && $waDigits !== '') ? ('https://wa.me/'.$waDigits.'?text='.rawurlencode($waMessage)) : null,
                'wa_tooltip' => $waTooltip,
            ];
        })->values();

        $pelangganOptions = $pelanggans
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(fn ($pelanggan) => [
                'id' => (string) $pelanggan->id,
                'nama' => (string) $pelanggan->nama,
            ])
            ->all();
    @endphp

    <div
        class="space-y-8"
        x-data="monitoringAparPage({
            units: @js($unitItems),
            unitBaseUrl: @js(url('/admin/unit-apar')),
            openModal: {{ $errors->any() ? 'true' : 'false' }},
        })"
        x-init="init()"
        @open-unit-modal.window="openModal = true"
    >
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Total Unit Terdaftar</p>
                        <p class="text-5xl font-black text-slate-900">{{ \App\Models\UnitApar::count() }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 text-white flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Status Aktif</p>
                        <p class="text-5xl font-black text-emerald-700">{{ \App\Models\UnitApar::where('tgl_expired', '>', now())->whereRaw('DATEDIFF(tgl_expired, NOW()) > 30')->count() }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-3">Hampir Habis</p>
                        <p class="text-5xl font-black text-amber-600">{{ \App\Models\UnitApar::whereRaw('tgl_expired > NOW() AND DATEDIFF(tgl_expired, NOW()) <= 30')->count() }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center shadow-lg shadow-amber-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                </div>
                <p class="text-xs font-bold text-amber-500 mt-3">≤ 30 hari lagi</p>
            </div>
            <div class="bg-gradient-to-br from-red-700 to-red-900 rounded-3xl p-8 shadow-xl shadow-red-700/30 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-red-100 mb-3">Masa Berlaku Habis</p>
                        <p class="text-5xl font-black">{{ \App\Models\UnitApar::where('tgl_expired', '<=', now())->count() }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white/60 overflow-hidden">
            <div class="p-8 space-y-4 bg-slate-50/60 backdrop-blur-sm border-b border-gray-100/70">
                <div class="flex flex-col xl:flex-row gap-4">
                    <div class="relative flex-grow flex items-center px-6 py-4 bg-white rounded-2xl border border-gray-200 gap-4 shadow-sm">
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" x-model="search" placeholder="Cari nama pelanggan atau nomor unit..." class="w-full border-none focus:ring-0 text-sm font-medium placeholder:text-slate-300 bg-transparent text-slate-700">
                    </div>
                    <div class="w-full xl:w-80">
                        <label for="pelanggan_filter" class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Pilih Pelanggan</label>
                        <select id="pelanggan_filter" x-model="selectedPelanggan" class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl font-bold text-sm text-slate-700 focus:ring-2 focus:ring-red-600/20 shadow-sm">
                            <option value="all">Semua Pelanggan</option>
                            @foreach($pelangganOptions as $pelangganOption)
                                <option value="{{ $pelangganOption['id'] }}">{{ $pelangganOption['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button onclick="window.print()" class="px-6 py-4 bg-white text-slate-600 font-bold rounded-2xl border border-gray-200 flex items-center justify-center gap-2 hover:bg-slate-50 transition shadow-sm no-print">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak Laporan
                    </button>
                </div>

                <div class="flex flex-col xl:flex-row gap-4">
                    <div class="inline-flex p-1.5 bg-white border border-gray-200 rounded-2xl shadow-sm w-full xl:w-auto">
                        <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-white'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">
                            List Semua Unit
                        </button>
                        <button type="button" @click="viewMode = 'group'" :class="viewMode === 'group' ? 'bg-red-700 text-white border-red-700' : 'bg-white text-red-700 border-white'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">
                            Kelompok per Pelanggan
                        </button>
                    </div>

                    <div class="inline-flex p-1.5 bg-white border border-gray-200 rounded-2xl shadow-sm w-full xl:w-auto">
                        <button type="button" @click="statusFilter = 'semua'" :class="statusFilter === 'semua' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-white'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">
                            Semua
                        </button>
                        <button type="button" @click="statusFilter = 'hampir'" :class="statusFilter === 'hampir' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-amber-600 border-white'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">
                            Hampir Expired
                        </button>
                        <button type="button" @click="statusFilter = 'expired'" :class="statusFilter === 'expired' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-red-600 border-white'" class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition">
                            Expired
                        </button>
                    </div>
                </div>

                <p class="text-[11px] font-semibold text-slate-500">
                    Menampilkan <span class="font-black text-slate-700" x-text="filteredUnits().length"></span> unit.
                    Klik nama pelanggan pada mode kelompok untuk expand/collapse daftar unit.
                </p>
            </div>

            <div x-show="viewMode === 'list'" x-cloak class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80 backdrop-blur-sm border-b border-gray-100/70">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit Info</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Produk</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal Beli / Expired</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/70">
                        <template x-for="unit in filteredUnits()" :key="unit.id">
                            <tr class="hover:bg-red-50/30 transition-colors group">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-red-700 transition" x-text="unit.no_seri"></p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1" x-text="unit.ukuran + ' / ' + unit.bahan"></p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-slate-700" x-text="unit.pelanggan_nama"></p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-medium text-slate-500" x-text="unit.produk_nama"></p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold" :class="unit.status === 'expired' ? 'text-red-600' : 'text-slate-900'" x-text="unit.tgl_expired_label"></p>
                                    <p class="text-[10px] font-medium text-slate-400" x-text="'Beli: ' + unit.tgl_beli_label"></p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl shadow-sm" :class="statusBadgeClass(unit.status)" x-text="statusLabel(unit.status)"></span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template x-if="unit.wa_url">
                                            <a :href="unit.wa_url" target="_blank" rel="noopener noreferrer" :title="unit.wa_tooltip" class="px-3 py-2 bg-emerald-600 text-white rounded-xl border border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700 transition-all text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4z"/></svg>
                                                Ingatkan
                                            </a>
                                        </template>
                                        <a :href="detailUrl(unit.id)"
                                           class="p-3 bg-white text-emerald-600 hover:bg-emerald-50 rounded-xl border border-emerald-100 hover:border-emerald-200 hover:shadow-lg transition-all shadow-sm"
                                           title="Lihat Detail & Riwayat">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a :href="editUrl(unit.id)" class="p-3 bg-white text-blue-600 hover:bg-blue-50 rounded-xl border border-blue-100 hover:border-blue-200 hover:shadow-lg transition-all shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </a>
                                        <form :action="destroyUrl(unit.id)" method="POST" class="inline" @submit="if (!confirm('Yakin ingin menghapus?')) $event.preventDefault()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-3 bg-white text-red-600 hover:bg-red-50 rounded-xl border border-red-100 hover:border-red-200 hover:shadow-lg transition-all shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredUnits().length === 0" x-cloak>
                            <td colspan="6" class="px-8 py-10 text-center text-sm font-semibold text-gray-500">Tidak ada unit yang cocok dengan filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div x-show="viewMode === 'group'" x-cloak class="p-6 sm:p-8 space-y-4">
                <template x-for="group in groupedUnits()" :key="group.pelanggan_id">
                    <div class="rounded-2xl border overflow-hidden transition-colors" :class="groupCardClass(group)">
                        <button type="button" class="w-full px-6 py-5 flex items-center justify-between text-left" @click="toggleGroup(group.pelanggan_id)">
                            <div>
                                <p class="text-sm font-black text-gray-900" x-text="group.pelanggan_nama"></p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-white/80 border border-gray-200 text-[10px] font-black uppercase tracking-widest text-gray-700" x-text="group.total + ' unit'"></span>
                                    <span x-show="group.nearCount > 0" class="inline-flex px-2.5 py-1 rounded-lg bg-amber-100 text-[10px] font-black uppercase tracking-widest text-amber-700" x-text="'Hampir Expired: ' + group.nearCount"></span>
                                    <span x-show="group.expiredCount > 0" class="inline-flex px-2.5 py-1 rounded-lg bg-red-100 text-[10px] font-black uppercase tracking-widest text-red-700" x-text="'Expired: ' + group.expiredCount"></span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-500 transition-transform" :class="isExpanded(group.pelanggan_id) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="isExpanded(group.pelanggan_id)" x-transition class="border-t border-white/70 bg-white/70">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50/80 backdrop-blur-sm">
                                        <tr>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nomor Unit</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Produk</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Expired</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100/70">
                                        <template x-for="unit in group.units" :key="unit.id">
                                            <tr class="hover:bg-red-50/30 transition-colors">
                                                <td class="px-6 py-4">
                                                    <p class="text-sm font-bold text-slate-900" x-text="unit.no_seri"></p>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1" x-text="unit.ukuran + ' / ' + unit.bahan"></p>
                                                </td>
                                                <td class="px-6 py-4 text-xs font-semibold text-slate-600" x-text="unit.produk_nama"></td>
                                                <td class="px-6 py-4">
                                                    <p class="text-xs font-bold" :class="unit.status === 'expired' ? 'text-red-600' : 'text-slate-900'" x-text="unit.tgl_expired_label"></p>
                                                    <p class="text-[10px] font-medium text-slate-400" x-text="'Beli: ' + unit.tgl_beli_label"></p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl shadow-sm" :class="statusBadgeClass(unit.status)" x-text="statusLabel(unit.status)"></span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <template x-if="unit.wa_url">
                                                            <a :href="unit.wa_url" target="_blank" rel="noopener noreferrer" :title="unit.wa_tooltip" class="px-2.5 py-2 bg-emerald-600 text-white rounded-xl border border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700 transition-all text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1 shadow-sm">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4z"/></svg>
                                                                WA
                                                            </a>
                                                        </template>
                                                        <a :href="detailUrl(unit.id)"
                                                           class="p-2.5 bg-white text-emerald-600 hover:bg-emerald-50 rounded-xl border border-emerald-100 hover:border-emerald-200 hover:shadow-lg transition-all shadow-sm"
                                                           title="Lihat Detail & Riwayat">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        </a>
                                                        <a :href="editUrl(unit.id)" class="p-2.5 bg-white text-blue-600 hover:bg-blue-50 rounded-xl border border-blue-100 hover:border-blue-200 hover:shadow-lg transition-all shadow-sm">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                        </a>
                                                        <form :action="destroyUrl(unit.id)" method="POST" class="inline" @submit="if (!confirm('Yakin ingin menghapus?')) $event.preventDefault()">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-2.5 bg-white text-red-600 hover:bg-red-50 rounded-xl border border-red-100 hover:border-red-200 hover:shadow-lg transition-all shadow-sm">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="groupedUnits().length === 0" x-cloak class="rounded-2xl border border-gray-100 bg-gray-50 px-6 py-10 text-center text-sm font-semibold text-gray-500">
                    Tidak ada grup pelanggan yang cocok dengan filter.
                </div>
            </div>
        </div>

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
                class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between px-8 py-6 bg-white/95 backdrop-blur border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Registrasi Unit APAR</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Daftarkan unit APAR baru maupun unit lama untuk monitoring kelayakan dan masa berlaku.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.unit-apar.store') }}" method="POST" class="p-8 sm:p-10">
                    @csrf
                    @if($errors->any())
                        <div class="mb-8 rounded-2xl border border-red-100 bg-red-50 px-6 py-5">
                            <p class="text-sm font-black text-red-700">Registrasi unit belum berhasil. Periksa data berikut:</p>
                            <ul class="mt-3 space-y-1 text-sm font-semibold text-red-600">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-6">
                            <div>
                                <label for="pelanggan_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pelanggan / Pemilik <span class="text-red-500">*</span></label>
                                <select name="pelanggan_id" id="pelanggan_id" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                    <option value="">Pilih Pelanggan</option>
                                    @foreach($pelanggans as $p)
                                        <option value="{{ $p->id }}" @selected(old('pelanggan_id') == $p->id)>{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pelanggan_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="produk_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Model Produk <span class="text-red-500">*</span></label>
                                <select name="produk_id" id="produk_id" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                    <option value="">Pilih Produk</option>
                                    @foreach($produks as $pr)
                                        <option value="{{ $pr->id }}" @selected(old('produk_id') == $pr->id)>{{ $pr->nama }} / {{ $pr->kapasitas ?? '-' }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('produk_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="no_seri" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor Seri Unit <span class="text-gray-300">(Opsional)</span></label>
                                <input type="text" name="no_seri" id="no_seri" value="{{ old('no_seri') }}"
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Kosongkan untuk generate otomatis">
                                <p class="text-[10px] font-semibold text-gray-500 mt-2">Jika dikosongkan, sistem akan membuat nomor seri otomatis (contoh: BUD-260421-PWD-1KG-01).</p>
                                <x-input-error :messages="$errors->get('no_seri')" class="mt-2" />
                            </div>

                            <div>
                                <label for="lokasi_unit" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Lokasi Unit <span class="text-gray-300">(Opsional)</span></label>
                                <input type="text" name="lokasi_unit" id="lokasi_unit" value="{{ old('lokasi_unit') }}"
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Contoh: Gudang belakang / Lantai 2">
                                <x-input-error :messages="$errors->get('lokasi_unit')" class="mt-2" />
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label for="tgl_beli" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Beli <span class="text-red-500">*</span></label>
                                <input type="date" name="tgl_beli" id="tgl_beli" value="{{ old('tgl_beli') }}" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <p class="mt-2 text-[10px] text-gray-500 font-semibold">Tanggal beli boleh perkiraan jika dokumen pembelian tidak tersedia.</p>
                                <x-input-error :messages="$errors->get('tgl_beli')" class="mt-2" />
                            </div>
                            <div>
                                <label for="kondisi_awal" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kondisi Awal Unit <span class="text-red-500">*</span></label>
                                <select name="kondisi_awal" id="kondisi_awal" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                    <option value="layak" @selected(old('kondisi_awal', 'layak') === 'layak')>Layak</option>
                                    <option value="perlu_servis" @selected(old('kondisi_awal') === 'perlu_servis')>Perlu servis</option>
                                    <option value="tidak_aktif" @selected(old('kondisi_awal') === 'tidak_aktif')>Tidak aktif</option>
                                </select>
                                <x-input-error :messages="$errors->get('kondisi_awal')" class="mt-2" />
                            </div>
                            <div>
                                <label for="catatan_unit" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Catatan Unit <span class="text-gray-300">(Opsional)</span></label>
                                <textarea name="catatan_unit" id="catatan_unit" rows="4"
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Contoh: data dari arsip lama, segel sudah aus">{{ old('catatan_unit') }}</textarea>
                                <x-input-error :messages="$errors->get('catatan_unit')" class="mt-2" />
                            </div>
                            <div class="bg-gray-50 border border-gray-100 rounded-2xl p-6">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Monitoring Otomatis</p>
                                <p class="text-sm font-bold text-gray-700 mt-3">Data ini dipakai untuk monitoring masa berlaku dan prioritas jadwal servis berikutnya.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" @click="openModal = false" class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition">Batal</button>
                        <button type="submit" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white font-black rounded-2xl hover:from-red-800 hover:to-red-900 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                            Registrasi Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
    </div>

    @once
        <script>
            function monitoringAparPage(config) {
                return {
                    search: '',
                    statusFilter: 'semua',
                    selectedPelanggan: 'all',
                    viewMode: 'list',
                    openModal: !!config.openModal,
                    units: config.units ?? [],
                    unitBaseUrl: config.unitBaseUrl ?? '',
                    expandedGroups: {},
                    init() {
                        this.units.forEach((unit) => {
                            const key = String(unit.pelanggan_id || '0');
                            if (this.expandedGroups[key] === undefined) {
                                this.expandedGroups[key] = true;
                            }
                        });
                    },
                    normalize(value) {
                        return String(value ?? '').toLowerCase();
                    },
                    shouldInclude(unit) {
                        const keyword = this.normalize(this.search).trim();
                        if (keyword !== '' && !this.normalize(unit.search_text).includes(keyword)) {
                            return false;
                        }

                        if (this.selectedPelanggan !== 'all' && String(unit.pelanggan_id) !== String(this.selectedPelanggan)) {
                            return false;
                        }

                        if (this.statusFilter !== 'semua' && unit.status !== this.statusFilter) {
                            return false;
                        }

                        return true;
                    },
                    filteredUnits() {
                        return this.units
                            .filter((unit) => this.shouldInclude(unit))
                            .sort((a, b) => {
                                const ownerCompare = this.normalize(a.pelanggan_nama).localeCompare(this.normalize(b.pelanggan_nama));
                                if (ownerCompare !== 0) {
                                    return ownerCompare;
                                }
                                return this.normalize(a.no_seri).localeCompare(this.normalize(b.no_seri));
                            });
                    },
                    groupedUnits() {
                        const groups = {};

                        this.filteredUnits().forEach((unit) => {
                            const key = String(unit.pelanggan_id || '0');
                            if (!groups[key]) {
                                groups[key] = {
                                    pelanggan_id: key,
                                    pelanggan_nama: unit.pelanggan_nama,
                                    units: [],
                                    nearCount: 0,
                                    expiredCount: 0,
                                    total: 0,
                                };
                            }

                            groups[key].units.push(unit);
                            groups[key].total += 1;

                            if (unit.status === 'hampir') {
                                groups[key].nearCount += 1;
                            }
                            if (unit.status === 'expired') {
                                groups[key].expiredCount += 1;
                            }
                        });

                        return Object.values(groups)
                            .map((group) => {
                                group.units.sort((a, b) => this.normalize(a.no_seri).localeCompare(this.normalize(b.no_seri)));
                                return group;
                            })
                            .sort((a, b) => this.normalize(a.pelanggan_nama).localeCompare(this.normalize(b.pelanggan_nama)));
                    },
                    toggleGroup(id) {
                        const key = String(id);
                        this.expandedGroups[key] = !this.isExpanded(key);
                    },
                    isExpanded(id) {
                        const key = String(id);
                        return this.expandedGroups[key] !== false;
                    },
                    statusLabel(status) {
                        if (status === 'expired') {
                            return 'Expired';
                        }
                        if (status === 'hampir') {
                            return 'Hampir Expired';
                        }
                        return 'Aktif';
                    },
                    statusBadgeClass(status) {
                        if (status === 'expired') {
                            return 'bg-red-50 text-red-600';
                        }
                        if (status === 'hampir') {
                            return 'bg-yellow-50 text-yellow-600';
                        }
                        return 'bg-green-50 text-green-600';
                    },
                    groupCardClass(group) {
                        if (group.expiredCount > 0) {
                            return 'border-red-200 bg-red-50/40';
                        }
                        if (group.nearCount > 0) {
                            return 'border-amber-200 bg-amber-50/40';
                        }
                        return 'border-gray-100 bg-white';
                    },
                    editUrl(id) {
                        return `${this.unitBaseUrl}/${id}/edit`;
                    },
                    detailUrl(id) {
                        return `${this.unitBaseUrl}/${id}`;
                    },
                    destroyUrl(id) {
                        return `${this.unitBaseUrl}/${id}`;
                    },
                };
            }
        </script>
    @endonce
</x-app-layout>
