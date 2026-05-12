<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Dashboard Monitoring APAR</h2>
            <p class="text-sm text-gray-500 font-medium mt-2">Ringkasan operasional unit APAR, layanan, pesanan, dan tren bulan berjalan untuk pengambilan keputusan harian.</p>
        </div>
    </x-slot>

    @php
        $totalProduk = \App\Models\Produk::count();
        $totalPelanggan = \App\Models\Pelanggan::count();
        $totalApar = \App\Models\UnitApar::count();
        $totalExpired = \App\Models\UnitApar::whereDate('tgl_expired', '<=', now())->count();
        $produkBulanIni = \App\Models\Produk::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $pelangganBulanIni = \App\Models\Pelanggan::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $aparBulanIni = \App\Models\UnitApar::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $serviceBulanIni = \App\Models\Service::where('jenis_service', '!=', 'Refill')->whereMonth('tgl_service', now()->month)->whereYear('tgl_service', now()->year)->count();
        $refillBulanIni = \App\Models\Refill::whereMonth('tgl_refill', now()->month)->whereYear('tgl_refill', now()->year)->count();
        $serviceTerbaru = \App\Models\Service::with(['unitApar.pelanggan'])->where('jenis_service', '!=', 'Refill')->latest('tgl_service')->take(3)->get();
        $refillTerbaru = \App\Models\Refill::with(['unitApar.pelanggan', 'jenisRefill'])->latest('tgl_refill')->take(3)->get();
        $expiredRate = $totalApar > 0 ? round(($totalExpired / $totalApar) * 100) : 0;
        $aktifRate = $totalApar > 0 ? 100 - $expiredRate : 0;
        $paidOrdersTodayCount = \App\Models\Pesanan::where('tipe', 'produk')
            ->whereNotNull('bukti_pembayaran')
            ->whereDate('updated_at', now()->toDateString())
            ->count();
        $paidOrdersRecent = \App\Models\Pesanan::with('pelanggan')
            ->where('tipe', 'produk')
            ->whereNotNull('bukti_pembayaran')
            ->latest('updated_at')
            ->take(5)
            ->get();
        $latestPaidAt = optional($paidOrdersRecent->first()?->updated_at)->toIso8601String() ?? now()->toIso8601String();
    @endphp

    <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm shadow-slate-200/50 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Produk</p>
                        <p class="text-3xl font-black text-slate-900 mt-2">{{ $totalProduk }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                        +{{ $produkBulanIni }} bln ini
                    </span>
                </div>
                <div class="mt-5 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center shadow-md shadow-red-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-500">Produk publik dan data admin tersinkron.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm shadow-slate-200/50 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Pelanggan</p>
                        <p class="text-3xl font-black text-slate-900 mt-2">{{ $totalPelanggan }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-wider border border-blue-100">
                        +{{ $pelangganBulanIni }} bln ini
                    </span>
                </div>
                <div class="mt-5 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-500">Pelanggan cek data via nomor WhatsApp.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm shadow-slate-200/50 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total APAR</p>
                        <p class="text-3xl font-black text-slate-900 mt-2">{{ $totalApar }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-violet-50 text-violet-700 text-[10px] font-black uppercase tracking-wider border border-violet-100">
                        +{{ $aparBulanIni }} bln ini
                    </span>
                </div>
                <div class="mt-5 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-violet-700 text-white flex items-center justify-center shadow-md shadow-violet-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-500">Unit terhubung ke pelanggan dan produk.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-red-200 bg-gradient-to-br from-red-700 to-red-800 p-6 shadow-md shadow-red-700/20 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <div class="relative z-10 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-red-100">APAR Expired</p>
                        <p class="text-3xl font-black mt-2">{{ $totalExpired }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/10 text-white text-[10px] font-black uppercase tracking-wider border border-white/10">
                        {{ $expiredRate }}% dari total
                    </span>
                </div>
                <div class="relative z-10 mt-5">
                    <div class="w-full h-1.5 rounded-full bg-white/10 overflow-hidden">
                        <div class="h-full bg-white rounded-full progress-bar" data-progress="{{ $expiredRate }}"></div>
                    </div>
                    <p class="text-xs font-semibold text-red-100 mt-3">Unit expired perlu tindak lanjut service atau refill.</p>
                </div>
            </div>
        </div>

        {{-- Notification Card: Pesanan & APAR Expiry --}}
        @php
            $notifOrders = \App\Models\Pesanan::with('pelanggan')
                ->whereNotIn('status', ['selesai final', 'ditolak'])
                ->whereDate('created_at', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $notifExpiring = \App\Models\UnitApar::with('pelanggan')
                ->whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->orderBy('tgl_expired', 'asc')
                ->limit(5)
                ->get();
            $notifExpired = \App\Models\UnitApar::with('pelanggan')
                ->whereDate('tgl_expired', '<', now()->toDateString())
                ->orderBy('tgl_expired', 'asc')
                ->limit(5)
                ->get();
            $notifStats = [
                'order_hari_ini' => \App\Models\Pesanan::whereNotIn('status', ['selesai final','ditolak'])->whereDate('created_at', now()->toDateString())->count(),
                'expiring_soon' => \App\Models\UnitApar::whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
                'already_expired' => \App\Models\UnitApar::whereDate('tgl_expired', '<', now()->toDateString())->count(),
            ];
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            {{-- Pesanan Baru Hari Ini --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pesanan Baru</p>
                        <h4 class="text-base font-black text-slate-900 mt-0.5">Hari Ini</h4>
                    </div>
                    <div class="px-3 py-1.5 bg-emerald-50 border border-emerald-100 rounded-xl">
                        <span id="dash-notif-order-count" class="text-lg font-black text-emerald-700">{{ $notifStats['order_hari_ini'] }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    @forelse($notifOrders as $order)
                        <a href="{{ route('admin.pesanan.show', $order->id) }}" class="block p-3 bg-emerald-50/60 border border-emerald-100 rounded-xl hover:bg-emerald-100 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-black text-slate-900">{{ $order->pelanggan?->nama ?? '-' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $order->created_at->format('H:i') }} — {{ $order->status }}</p>
                                </div>
                                <p class="text-sm font-black text-emerald-700">Rp {{ number_format((float) ($order->total_harga ?: $order->total), 0, ',', '.') }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-sm font-medium">Belum ada pesanan baru hari ini.</div>
                    @endforelse
                </div>
            </div>

            {{-- APAR Hampir Expired --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Segera</p>
                        <h4 class="text-base font-black text-slate-900 mt-0.5">APAR Akan Expired</h4>
                        <p class="text-[10px] text-slate-400 mt-0.5">Dalam 30 hari ke depan</p>
                    </div>
                    <div class="px-3 py-1.5 bg-amber-50 border border-amber-100 rounded-xl">
                        <span id="dash-notif-exp-count" class="text-lg font-black text-amber-700">{{ $notifStats['expiring_soon'] }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    @forelse($notifExpiring as $apar)
                        <a href="{{ route('admin.unit-apar.show', $apar->id) }}" class="block p-3 bg-amber-50/60 border border-amber-100 rounded-xl hover:bg-amber-100 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-black text-slate-900">{{ $apar->no_seri }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $apar->pelanggan?->nama ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-black text-amber-700">{{ $apar->tgl_expired->format('d M Y') }}</p>
                                    <p class="text-[10px] text-amber-500 font-bold">{{ now()->diffInDays($apar->tgl_expired) }} hari</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-sm font-medium">Tidak ada APAR yang akan expired soon.</div>
                    @endforelse
                </div>
            </div>

            {{-- APAR Sudah Expired --}}
            <div class="bg-white rounded-2xl border border-red-100 p-5 shadow-sm shadow-red-700/10">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Perhatian</p>
                        <h4 class="text-base font-black text-slate-900 mt-0.5">APAR Expired</h4>
                        <p class="text-[10px] text-slate-400 mt-0.5">Perlu tindak lanjut segera</p>
                    </div>
                    <div class="px-3 py-1.5 bg-red-50 border border-red-100 rounded-xl">
                        <span id="dash-notif-expired-count" class="text-lg font-black text-red-700">{{ $notifStats['already_expired'] }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    @forelse($notifExpired as $apar)
                        <a href="{{ route('admin.unit-apar.show', $apar->id) }}" class="block p-3 bg-red-50/60 border border-red-100 rounded-xl hover:bg-red-100 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-black text-slate-900">{{ $apar->no_seri }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $apar->pelanggan?->nama ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-black text-red-700">{{ $apar->tgl_expired->format('d M Y') }}</p>
                                    <p class="text-[10px] text-red-400 font-bold">{{ now()->diffInDays($apar->tgl_expired) }} hari lalu</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-sm font-medium">Tidak ada APAR expired.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div
            id="payment-notification-card"
            data-poll-url="{{ route('admin.pesanan.payment-notifications') }}"
            data-initial-since="{{ $latestPaidAt }}"
            class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm"
        >
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Realtime Notifikasi</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1">Pembayaran Pesanan Masuk</h3>
                    <p class="text-sm text-slate-500 font-medium mt-1">Dashboard akan otomatis mendeteksi pembayaran baru dari pelanggan tanpa reload halaman.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 shadow-sm">
                        <span id="paid-orders-live-dot" class="hidden w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span class="text-[10px] font-black uppercase tracking-wider">Pesanan bayar hari ini</span>
                        <span id="paid-orders-today-count" class="text-base font-black">{{ $paidOrdersTodayCount }}</span>
                    </div>
                    <a href="{{ route('admin.pesanan.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 text-white text-xs font-black uppercase tracking-wider hover:bg-slate-700 transition shadow-sm">
                        Buka Pesanan
                    </a>
                </div>
            </div>

            <div id="paid-order-feed" class="mt-5 grid grid-cols-1 xl:grid-cols-2 gap-3">
                @forelse($paidOrdersRecent as $paidOrder)
                    <div data-order-id="{{ $paidOrder->id }}" class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 hover:shadow-md transition">
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-wider">Pesanan #{{ $paidOrder->id }}</p>
                        <p class="text-sm font-black text-slate-900 mt-1">{{ $paidOrder->pelanggan?->nama ?? '-' }}</p>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-slate-500">
                                {{ optional($paidOrder->updated_at)->format('d M Y H:i') }}
                            </p>
                            <p class="text-sm font-black text-emerald-600">
                                Rp {{ number_format((float) ($paidOrder->total_harga ?: $paidOrder->total), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div data-feed-empty class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-5 text-sm font-semibold text-slate-500 xl:col-span-2">
                        Belum ada pembayaran masuk.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Monitoring Bulan Ini</p>
                        <h3 class="text-xl font-black text-slate-900 mt-1">Progress Input dan Monitoring</h3>
                        <p class="text-sm text-slate-500 font-medium mt-1">Alur sistem fokus pada input → simpan → tampil → monitoring.</p>
                    </div>
                    <div class="px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider">Status Sistem</p>
                        <p class="text-base font-black mt-0.5">Stabil</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4 mt-6">
                    <div class="rounded-xl border border-gray-100 bg-slate-50 px-5 py-4 shadow-sm hover:shadow-md transition">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Input Service</p>
                        <p class="text-2xl font-black text-slate-900 mt-2">{{ $serviceBulanIni }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-slate-50 px-5 py-4 shadow-sm hover:shadow-md transition">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Input Refill</p>
                        <p class="text-2xl font-black text-slate-900 mt-2">{{ $refillBulanIni }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-slate-50 px-5 py-4 shadow-sm hover:shadow-md transition">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Unit Aktif</p>
                        <p class="text-2xl font-black text-slate-900 mt-2">{{ $totalApar - $totalExpired }}</p>
                    </div>
                </div>

                <div class="space-y-4 mt-6">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-sm font-bold text-slate-700">Status APAR Aktif</p>
                            <p class="text-sm font-black text-slate-900">{{ $aktifRate }}%</p>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 progress-bar" data-progress="{{ $aktifRate }}"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-sm font-bold text-slate-700">Perlu Tindak Lanjut</p>
                            <p class="text-sm font-black text-slate-900">{{ $expiredRate }}%</p>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-red-400 to-red-600 progress-bar" data-progress="{{ $expiredRate }}"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Alur Sistem</p>
                <h3 class="text-xl font-black text-slate-900 mt-1">Langkah Admin</h3>
                <div class="space-y-3 mt-4">
                    <a href="{{ route('admin.produk.index') }}" class="block p-4 rounded-xl border border-gray-100 bg-white/60 hover:shadow-md hover:-translate-y-0.5 hover:border-red-200 transition">
                        <p class="text-[10px] font-black text-red-600 uppercase tracking-wider">Langkah 1</p>
                        <p class="text-base font-black text-slate-900 mt-1">Input Produk APAR</p>
                    </a>
                    <a href="{{ route('admin.pelanggan.index') }}" class="block p-4 rounded-xl border border-gray-100 bg-white/60 hover:shadow-md hover:-translate-y-0.5 hover:border-red-200 transition">
                        <p class="text-[10px] font-black text-red-600 uppercase tracking-wider">Langkah 2</p>
                        <p class="text-base font-black text-slate-900 mt-1">Input Pelanggan</p>
                    </a>
                    <a href="{{ route('admin.unit-apar.index') }}" class="block p-4 rounded-xl border border-gray-100 bg-white/60 hover:shadow-md hover:-translate-y-0.5 hover:border-red-200 transition">
                        <p class="text-[10px] font-black text-red-600 uppercase tracking-wider">Langkah 3</p>
                        <p class="text-base font-black text-slate-900 mt-1">Input Unit APAR</p>
                    </a>
                    <a href="{{ route('admin.service.index') }}" class="block p-4 rounded-xl border border-gray-100 bg-white/60 hover:shadow-md hover:-translate-y-0.5 hover:border-red-200 transition">
                        <p class="text-[10px] font-black text-red-600 uppercase tracking-wider">Langkah 4</p>
                        <p class="text-base font-black text-slate-900 mt-1">Kelola Layanan APAR</p>
                        <p class="text-xs font-semibold text-slate-500 mt-1">Termasuk service, perawatan, dan refill pelanggan.</p>
                    </a>
                </div>
                <div class="mt-5 p-4 rounded-xl bg-slate-50 border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Monitoring Publik</p>
                    <p class="text-sm font-semibold text-slate-700 mt-2">Pelanggan membuka website, klik cek APAR, masukkan nomor WhatsApp, lalu melihat status APAR.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-slate-50 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Aktivitas Terbaru</p>
                        <h3 class="text-lg font-black text-slate-900 mt-0.5">Aktivitas Layanan Terbaru</h3>
                    </div>
                    <a href="{{ route('admin.laporan.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-black uppercase tracking-wider hover:bg-slate-700 transition shadow-sm">
                        Buka Laporan
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Unit</th>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($serviceTerbaru as $service)
                                <tr class="hover:bg-red-50/30 transition">
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-red-50 text-red-700 border border-red-100 text-[10px] font-black uppercase tracking-wider shadow-sm">Service</span>
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-slate-700">{{ $service->tgl_service->format('d M Y') }}</td>
                                    <td class="px-6 py-3 text-sm font-black text-slate-900">{{ $service->unitApar->no_seri }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-slate-700">{{ $service->unitApar->pelanggan->nama }}</td>
                                    <td class="px-6 py-3 text-sm font-medium text-slate-500">{{ $service->jenis_service }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-sm font-semibold text-slate-500 text-center">Belum ada data service terbaru.</td>
                                </tr>
                            @endforelse

                            @forelse($refillTerbaru as $refill)
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-black uppercase tracking-wider shadow-sm">Refill</span>
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-slate-700">{{ $refill->tgl_refill->format('d M Y') }}</td>
                                    <td class="px-6 py-3 text-sm font-black text-slate-900">{{ $refill->unitApar->no_seri }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-slate-700">{{ $refill->unitApar->pelanggan->nama }}</td>
                                    <td class="px-6 py-3 text-sm font-medium text-slate-500">{{ $refill->jenisRefill->nama }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-sm font-semibold text-slate-500 text-center">Belum ada data refill terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ringkasan Cepat</p>
                <h3 class="text-lg font-black text-slate-900 mt-0.5">Statistik Monitoring</h3>
                <div class="space-y-3 mt-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-gray-100 shadow-sm">
                        <div>
                            <p class="text-sm font-black text-slate-900">Produk tampil publik</p>
                            <p class="text-xs font-semibold text-slate-500 mt-0.5">Landing page menampilkan {{ $totalProduk }} produk</p>
                        </div>
                        <span class="text-lg font-black text-slate-900 bg-white px-2.5 py-1 rounded-xl shadow-sm">{{ $totalProduk }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-gray-100 shadow-sm">
                        <div>
                            <p class="text-sm font-black text-slate-900">Service bulan ini</p>
                            <p class="text-xs font-semibold text-slate-500 mt-0.5">Input admin untuk monitoring</p>
                        </div>
                        <span class="text-lg font-black text-slate-900 bg-white px-2.5 py-1 rounded-xl shadow-sm">{{ $serviceBulanIni }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-gray-100 shadow-sm">
                        <div>
                            <p class="text-sm font-black text-slate-900">Refill bulan ini</p>
                            <p class="text-xs font-semibold text-slate-500 mt-0.5">Jenis refill: Powder, CO2, Foam</p>
                        </div>
                        <span class="text-lg font-black text-slate-900 bg-white px-2.5 py-1 rounded-xl shadow-sm">{{ $refillBulanIni }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 border border-emerald-100 shadow-sm">
                        <div>
                            <p class="text-sm font-black text-slate-900">Akses pelanggan</p>
                            <p class="text-xs font-semibold text-slate-500 mt-0.5">Tanpa login, cukup nomor WhatsApp</p>
                        </div>
                        <span class="text-base font-black text-emerald-700 bg-white px-2.5 py-1 rounded-xl shadow-sm">Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="dashboard-payment-toast-wrap" class="fixed top-24 right-6 z-[80] space-y-3"></div>

    <script>
        (function () {
            const card = document.getElementById('payment-notification-card');
            if (!card) return;

            const pollUrl = card.dataset.pollUrl || '';
            let since = card.dataset.initialSince || new Date().toISOString();
            const feed = document.getElementById('paid-order-feed');
            const countEl = document.getElementById('paid-orders-today-count');
            const liveDot = document.getElementById('paid-orders-live-dot');
            const toastWrap = document.getElementById('dashboard-payment-toast-wrap');
            if (!feed || !countEl || !liveDot || !toastWrap) return;
            const seenIds = new Set();

            feed.querySelectorAll('[data-order-id]').forEach((el) => {
                seenIds.add(Number(el.dataset.orderId));
            });

            function formatRupiah(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
            }

            function formatDate(iso) {
                if (!iso) return '-';
                const date = new Date(iso);
                return new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(date);
            }

            function renderFeedItem(order) {
                const emptyEl = feed.querySelector('[data-feed-empty]');
                if (emptyEl) emptyEl.remove();

                const item = document.createElement('div');
                item.dataset.orderId = String(order.id);
                item.className = 'rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3';
                item.innerHTML = `
                    <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">${order.kode}</p>
                    <p class="text-sm font-black text-slate-900 mt-1">${order.pelanggan || '-'}</p>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold text-slate-500">${formatDate(order.updated_at)}</p>
                        <p class="text-sm font-black text-emerald-700">${formatRupiah(order.total)}</p>
                    </div>
                `;

                feed.prepend(item);
                const items = feed.querySelectorAll('[data-order-id]');
                if (items.length > 6) {
                    items[items.length - 1].remove();
                }
            }

            function renderToast(order) {
                const toast = document.createElement('div');
                toast.className = 'w-[320px] rounded-2xl border border-emerald-100 bg-white/95 backdrop-blur px-4 py-3 shadow-xl shadow-emerald-200/50';
                toast.innerHTML = `
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em]">Pesanan Masuk</p>
                    <p class="text-sm font-black text-slate-900 mt-1">${order.pelanggan || '-'}</p>
                    <p class="text-xs text-slate-500 mt-1">${order.kode}</p>
                    <p class="text-sm font-black text-emerald-700 mt-2">${formatRupiah(order.total)}</p>
                `;
                toastWrap.appendChild(toast);
                setTimeout(() => {
                    toast.remove();
                }, 5200);
            }

            async function pollPayments() {
                if (!pollUrl) return;

                try {
                    const url = `${pollUrl}?since=${encodeURIComponent(since)}`;
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                    if (!response.ok) return;

                    const data = await response.json();
                    if (!data || !data.success) return;

                    since = data.server_time || since;
                    if (countEl && typeof data.paid_today === 'number') {
                        countEl.textContent = String(data.paid_today);
                    }

                    const freshOrders = (data.orders || []).filter((order) => !seenIds.has(Number(order.id)));
                    if (!freshOrders.length) return;

                    liveDot.classList.remove('hidden');
                    setTimeout(() => liveDot.classList.add('hidden'), 1600);

                    freshOrders.reverse().forEach((order) => {
                        seenIds.add(Number(order.id));
                        renderFeedItem(order);
                        renderToast(order);
                    });
                } catch (error) {
                    // Silent fail, next poll will retry.
                }
            }

            setInterval(pollPayments, 12000);

            // Also poll general notifications to update dashboard card counts + show toasts
            const notifPollUrl = '{{ route('notifications.index') }}';
            let notifSince = new Date().toISOString();
            // Initialize with current timestamp so first poll only returns truly NEW items
            const seenOrderIds = new Set();
            const seenExpiringIds = new Set();

            async function pollNotifications() {
                try {
                    const resp = await fetch(notifPollUrl + '?since=' + encodeURIComponent(notifSince), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) return;
                    const d = await resp.json();
                    if (!d.success) return;
                    notifSince = d.server_time || notifSince;

                    const orderCountEl = document.getElementById('dash-notif-order-count');
                    const expCountEl = document.getElementById('dash-notif-exp-count');
                    const expiredCountEl = document.getElementById('dash-notif-expired-count');
                    if (orderCountEl) orderCountEl.textContent = d.stats.order_hari_ini;
                    if (expCountEl) expCountEl.textContent = d.stats.expiring_soon;
                    if (expiredCountEl) expiredCountEl.textContent = d.stats.already_expired;

                    const notifWrap = document.getElementById('reverb-toast-wrap');
                    if (!notifWrap) return;

                    (d.orders || []).forEach(o => {
                        if (seenOrderIds.has(o.id)) return;
                        seenOrderIds.add(o.id);
                        const t = document.createElement('div');
                        t.className = 'w-72 rounded-2xl border border-emerald-100 bg-white/95 backdrop-blur px-4 py-3 shadow-xl shadow-emerald-200/40 cursor-pointer hover:shadow-2xl transition-all';
                        t.innerHTML = '<p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Pesanan Baru</p><p class="text-sm font-black text-slate-900 mt-1">' + (o.pelanggan || '-') + '</p><p class="text-xs text-slate-500 mt-0.5">' + o.kode + '</p><p class="text-sm font-black text-emerald-700 mt-1">' + formatRupiah(o.total) + '</p>';
                        t.onclick = () => { window.location.href = '/admin/pesanan/' + o.id; };
                        notifWrap.appendChild(t);
                        setTimeout(() => t.remove(), 6000);
                    });

                    (d.expiring_apar || []).forEach(a => {
                        if (seenExpiringIds.has(a.id)) return;
                        seenExpiringIds.add(a.id);
                        const t = document.createElement('div');
                        t.className = 'w-72 rounded-2xl border border-amber-100 bg-white/95 backdrop-blur px-4 py-3 shadow-xl shadow-amber-200/40 cursor-pointer hover:shadow-2xl transition-all';
                        t.innerHTML = '<p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">APAR Akan Expired</p><p class="text-sm font-black text-slate-900 mt-1">' + a.no_seri + '</p><p class="text-xs text-slate-500 mt-0.5">' + a.pelanggan + '</p><p class="text-sm font-black text-amber-700 mt-1">Exp: ' + a.tgl_expired + ' (' + a.days_left + ' hari)</p>';
                        t.onclick = () => { window.location.href = '/admin/unit-apar/' + a.id; };
                        notifWrap.appendChild(t);
                        setTimeout(() => t.remove(), 6000);
                    });
                } catch(e) {}
            }

            setInterval(pollNotifications, 15000);
        })();
    </script>

</x-app-layout>
