<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Dashboard Teknisi</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Ringkasan tugas aktif dan riwayat penyelesaian bulan ini.</p>
        </div>
    </x-slot>

    @php
        $greeting = match(true) {
            now()->hour < 12 => 'Selamat pagi',
            now()->hour < 15 => 'Selamat siang',
            default => 'Selamat sore',
        };
    @endphp

    <div class="space-y-8">
        {{-- Greeting + Stats --}}
        <div class="bg-gradient-to-r from-red-700 to-red-800 rounded-3xl p-8 shadow-xl shadow-red-700/20 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-red-200 text-sm font-bold mb-1">{{ $greeting }}, {{ auth()->user()->name }} 👋</p>
                    <h3 class="text-2xl font-black tracking-tight">Siap kerja hari ini?</h3>
                    <p class="text-red-100 text-sm font-medium mt-2">Lihat tugas yang menunggu dan selesaikan sebelum batas waktu.</p>
                </div>
                <div class="flex gap-6 flex-wrap">
                    <div class="text-center">
                        <p class="text-4xl font-black">{{ $summary['aktif_produk'] + $summary['aktif_service'] + $summary['aktif_refill_stock'] }}</p>
                        <p class="text-[10px] font-black text-red-200 uppercase tracking-widest mt-1">Tugas Aktif</p>
                    </div>
                    <div class="text-center">
                        <p class="text-4xl font-black">{{ $summary['selesai_bulan_ini'] }}</p>
                        <p class="text-[10px] font-black text-red-200 uppercase tracking-widest mt-1">Selesai Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tugas Produk</p>
                        <p class="text-4xl font-black text-slate-900 mt-3">{{ $summary['aktif_produk'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 text-amber-700 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <a href="{{ route('teknisi.tugas-produk') }}" class="inline-flex items-center gap-1 mt-4 text-xs font-bold text-amber-700 hover:text-amber-800">
                    Lihat tugas <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Layanan APAR</p>
                        <p class="text-4xl font-black text-slate-900 mt-3">{{ $summary['aktif_service'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-700 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/></svg>
                    </div>
                </div>
                <a href="{{ route('teknisi.tugas-service-refill') }}" class="inline-flex items-center gap-1 mt-4 text-xs font-bold text-blue-700 hover:text-blue-800">
                    Lihat tugas <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Refill Stock</p>
                        <p class="text-4xl font-black text-slate-900 mt-3">{{ $summary['aktif_refill_stock'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-50 text-red-700 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                </div>
                <a href="{{ route('teknisi.refill-stock.index') }}" class="inline-flex items-center gap-1 mt-4 text-xs font-bold text-red-700 hover:text-red-800">
                    Lihat tugas <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Avg. Waktu Kerja</p>
                        <p class="text-4xl font-black text-slate-900 mt-3">{{ $summary['avg_waktu'] }}<span class="text-lg">j</span></p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-700 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-xs font-semibold text-slate-500 mt-4">Rata-rata penyelesaian per order bulan ini</p>
            </div>
            <a href="{{ route('teknisi.service-log') }}" class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition block">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Log Layanan</p>
                        <p class="text-4xl font-black text-slate-900 mt-3">{{ ($summary['service_log_count'] ?? 0) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-700 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/></svg>
                    </div>
                </div>
                <p class="text-xs font-bold text-blue-700 mt-4">Lihat service log <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></p>
            </a>
        </div>

        {{-- Notifikasi: Pesanan Baru & APAR Expired --}}
        @php
            $tNotifOrders = \App\Models\Pesanan::with('pelanggan')
                ->whereNotIn('status', ['selesai final', 'ditolak'])
                ->whereDate('created_at', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();
            $tNotifExpiring = \App\Models\UnitApar::with('pelanggan')
                ->whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->orderBy('tgl_expired', 'asc')
                ->limit(4)
                ->get();
            $tNotifExpired = \App\Models\UnitApar::with('pelanggan')
                ->whereDate('tgl_expired', '<', now()->toDateString())
                ->orderBy('tgl_expired', 'asc')
                ->limit(4)
                ->get();
            $tNotifStats = [
                'order_hari_ini' => \App\Models\Pesanan::whereNotIn('status', ['selesai final','ditolak'])->whereDate('created_at', now()->toDateString())->count(),
                'expiring_soon' => \App\Models\UnitApar::whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
                'already_expired' => \App\Models\UnitApar::whereDate('tgl_expired', '<', now()->toDateString())->count(),
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Pesanan Baru --}}
            <div class="bg-white rounded-2xl border border-emerald-200/60 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Pesanan Baru</p>
                        <h5 class="text-base font-black text-slate-900 mt-0.5">Hari Ini</h5>
                    </div>
                    <span id="notif-order-count" class="px-3 py-1 bg-emerald-50 border border-emerald-100 rounded-xl text-base font-black text-emerald-700">{{ $tNotifStats['order_hari_ini'] }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($tNotifOrders as $order)
                        <a href="#" class="block p-3 bg-emerald-50/60 border border-emerald-100 rounded-xl hover:bg-emerald-100 transition">
                            <p class="text-xs font-black text-slate-900">{{ $order->pelanggan?->nama ?? '-' }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $order->created_at->format('H:i') }} — {{ $order->status }}</p>
                        </a>
                    @empty
                        <p class="py-3 text-center text-xs font-medium text-slate-400">Belum ada pesanan baru.</p>
                    @endforelse
                </div>
            </div>

            {{-- APAR Akan Expired --}}
            <div class="bg-white rounded-2xl border border-amber-200/60 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Segera</p>
                        <h5 class="text-base font-black text-slate-900 mt-0.5">Akan Expired</h5>
                    </div>
                    <span id="notif-exp-count" class="px-3 py-1 bg-amber-50 border border-amber-100 rounded-xl text-base font-black text-amber-700">{{ $tNotifStats['expiring_soon'] }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($tNotifExpiring as $apar)
                        <div class="p-3 bg-amber-50/60 border border-amber-100 rounded-xl">
                            <p class="text-xs font-black text-slate-900">{{ $apar->no_seri }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $apar->pelanggan?->nama ?? '-' }}</p>
                            <p class="text-[10px] font-black text-amber-700 mt-1">{{ $apar->tgl_expired->format('d M') }} ({{ now()->diffInDays($apar->tgl_expired) }} hari)</p>
                        </div>
                    @empty
                        <p class="py-3 text-center text-xs font-medium text-slate-400">Tidak ada APAR hampir expired.</p>
                    @endforelse
                </div>
            </div>

            {{-- APAR Expired --}}
            <div class="bg-white rounded-2xl border border-red-200/60 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">Perhatian</p>
                        <h5 class="text-base font-black text-slate-900 mt-0.5">Sudah Expired</h5>
                    </div>
                    <span id="notif-expired-count" class="px-3 py-1 bg-red-50 border border-red-100 rounded-xl text-base font-black text-red-700">{{ $tNotifStats['already_expired'] }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($tNotifExpired as $apar)
                        <div class="p-3 bg-red-50/60 border border-red-100 rounded-xl">
                            <p class="text-xs font-black text-slate-900">{{ $apar->no_seri }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $apar->pelanggan?->nama ?? '-' }}</p>
                            <p class="text-[10px] font-black text-red-700 mt-1">Expired {{ now()->diffInDays($apar->tgl_expired) }} hari lalu</p>
                        </div>
                    @empty
                        <p class="py-3 text-center text-xs font-medium text-slate-400">Tidak ada APAR expired.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Completed + Quick Actions --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-7 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Riwayat Bulan Ini</p>
                        <h4 class="text-lg font-black text-slate-900 mt-1">Tugas Baru Selesai</h4>
                    </div>
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase rounded-lg">{{ $summary['selesai_bulan_ini'] }} selesai</span>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recentCompleted as $task)
                        <div class="px-7 py-4 hover:bg-slate-50/50 transition">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-{{ $task->tipe === 'produk' ? 'amber' : 'blue' }}-50 text-{{ $task->tipe === 'produk' ? 'amber' : 'blue' }}-700 flex items-center justify-center font-black text-sm shrink-0">
                                        {{ strtoupper(substr($task->pelanggan?->nama ?? 'T', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-slate-900 truncate">{{ $task->pelanggan?->nama ?? '-' }}</p>
                                        <p class="text-[10px] font-semibold text-slate-400 mt-0.5">#{{ $task->id }} — {{ $task->tipe }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-[10px] font-bold text-slate-400">{{ $task->teknisi_selesai_at?->diffForHumans() }}</p>
                                    <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase rounded-lg mt-1">Selesai</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-7 py-10 text-center text-slate-400 font-medium text-sm">Belum ada tugas selesai bulan ini.</div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Aksi Cepat</p>
                    <div class="space-y-3">
                        <a href="{{ route('teknisi.tugas-produk') }}" class="flex items-center gap-3 p-4 bg-amber-50 rounded-xl hover:bg-amber-100 transition group">
                            <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span class="text-sm font-bold text-amber-800 group-hover:text-amber-900">Tugas Produk</span>
                        </a>
                        <a href="{{ route('teknisi.tugas-service-refill') }}" class="flex items-center gap-3 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition group">
                            <svg class="w-5 h-5 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/></svg>
                            <span class="text-sm font-bold text-blue-800 group-hover:text-blue-900">Service / Refill</span>
                        </a>
                        <a href="{{ route('teknisi.refill-stock.index') }}" class="flex items-center gap-3 p-4 bg-red-50 rounded-xl hover:bg-red-100 transition group">
                            <svg class="w-5 h-5 text-red-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span class="text-sm font-bold text-red-800 group-hover:text-red-900">Refill Stock</span>
                        </a>
                        <a href="{{ route('teknisi.riwayat-tugas') }}" class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition group">
                            <svg class="w-5 h-5 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900">Riwayat Tugas</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const pollUrl = '{{ route('notifications.index') }}';
        let since = new Date().toISOString();
        const seenOrderIds = new Set();
        const seenExpiringIds = new Set();

        function formatRupiah(v) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(v || 0));
        }

        function renderOrderToast(o) {
            const wrap = document.getElementById('reverb-toast-wrap');
            if (!wrap) return;
            const t = document.createElement('div');
            t.className = 'w-72 rounded-2xl border border-emerald-100 bg-white/95 backdrop-blur px-4 py-3 shadow-xl shadow-emerald-200/40 cursor-pointer hover:shadow-2xl transition-all';
            t.innerHTML = '<p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Pesanan Baru</p><p class="text-sm font-black text-slate-900 mt-1">' + (o.pelanggan || '-') + '</p><p class="text-xs text-slate-500 mt-0.5">' + o.kode + '</p><p class="text-sm font-black text-emerald-700 mt-1">' + formatRupiah(o.total) + '</p>';
            t.onclick = () => { window.location.href = '/admin/pesanan/' + o.id; };
            wrap.appendChild(t);
            setTimeout(() => t.remove(), 6000);
        }

        function renderExpiringToast(a) {
            const wrap = document.getElementById('reverb-toast-wrap');
            if (!wrap) return;
            const t = document.createElement('div');
            t.className = 'w-72 rounded-2xl border border-amber-100 bg-white/95 backdrop-blur px-4 py-3 shadow-xl shadow-amber-200/40 cursor-pointer hover:shadow-2xl transition-all';
            t.innerHTML = '<p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">APAR Akan Expired</p><p class="text-sm font-black text-slate-900 mt-1">' + a.no_seri + '</p><p class="text-xs text-slate-500 mt-0.5">' + a.pelanggan + '</p><p class="text-sm font-black text-amber-700 mt-1">Exp: ' + a.tgl_expired + ' (' + a.days_left + ' hari)</p>';
            t.onclick = () => { window.location.href = '/admin/unit-apar/' + a.id; };
            wrap.appendChild(t);
            setTimeout(() => t.remove(), 6000);
        }

        async function poll() {
            try {
                const resp = await fetch(pollUrl + '?since=' + encodeURIComponent(since), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!resp.ok) return;
                const d = await resp.json();
                if (!d.success) return;
                since = d.server_time || since;

                (d.orders || []).forEach(o => {
                    if (seenOrderIds.has(o.id)) return;
                    seenOrderIds.add(o.id);
                    renderOrderToast(o);
                });
                (d.expiring_apar || []).forEach(a => {
                    if (seenExpiringIds.has(a.id)) return;
                    seenExpiringIds.add(a.id);
                    renderExpiringToast(a);
                });

                const countEl = document.getElementById('notif-order-count');
                const expEl = document.getElementById('notif-exp-count');
                const expiredEl = document.getElementById('notif-expired-count');
                if (countEl) countEl.textContent = d.stats.order_hari_ini;
                if (expEl) expEl.textContent = d.stats.expiring_soon;
                if (expiredEl) expiredEl.textContent = d.stats.already_expired;
            } catch(e) {}
        }

        setInterval(poll, 15000);
    })();
    </script>
</x-app-layout>
