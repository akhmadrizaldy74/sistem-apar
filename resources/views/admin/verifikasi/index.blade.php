<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Verifikasi & Pengujian Sistem</h2>
            <p class="text-sm text-gray-500 font-medium mt-1">Halaman pengujian dan validasi seluruh modul sistem APAR secara otomatis dan manual.</p>
        </div>
    </x-slot>

    @php
        $statusColors = [
            'passed' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'icon' => 'check-circle'],
            'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'icon' => 'exclamation'],
            'failed' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'icon' => 'x-circle'],
        ];
    @endphp

    <div class="space-y-10">

        {{-- System Stats Overview --}}
        <div>
            <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-3">
                <span class="w-8 h-8 bg-red-100 text-red-700 rounded-xl flex items-center justify-center text-sm font-black">📊</span>
                Statistik Data Sistem
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_pelanggan'] }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_produk'] }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit APAR</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_unit_apar'] }}</p>
                    <p class="text-[10px] font-bold text-emerald-600 mt-1">{{ $stats['unit_aktif'] }} aktif</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pesanan</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_pesanan'] }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Service</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_service'] }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Refill</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_refill'] }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Komplain</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_complain'] }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Testimoni</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_testimoni'] }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Aktivitas Log</p>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $recentActivity->count() }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                    <p class="text-[10px] font-black text-red-400 uppercase tracking-widest">Expired APAR</p>
                    <p class="text-3xl font-black text-red-700 mt-2">{{ $stats['unit_expired'] }}</p>
                </div>
            </div>
        </div>

        {{-- Monthly Activity --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-8">
            <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center text-sm font-black">📅</span>
                Aktivitas Bulan Ini ({{ now()->format('F Y') }})
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 text-center">
                    <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Pesanan Baru</p>
                    <p class="text-3xl font-black text-blue-800 mt-2">{{ $dashboardStats['pesanan_bulan_ini'] }}</p>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-5 text-center">
                    <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">Service</p>
                    <p class="text-3xl font-black text-red-800 mt-2">{{ $dashboardStats['service_bulan_ini'] }}</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5 text-center">
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Refill</p>
                    <p class="text-3xl font-black text-emerald-800 mt-2">{{ $dashboardStats['refill_bulan_ini'] }}</p>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 text-center">
                    <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Pelanggan Baru</p>
                    <p class="text-3xl font-black text-amber-800 mt-2">{{ $dashboardStats['pelanggan_bulan_ini'] }}</p>
                </div>
            </div>
        </div>

        {{-- Module Testing Grid --}}
        <div>
            <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-3">
                <span class="w-8 h-8 bg-violet-100 text-violet-700 rounded-xl flex items-center justify-center text-sm font-black">🧪</span>
                Pengujian Modul Sistem
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{
                results: {},
                async runTest(key) {
                    this.results[key] = { status: 'running', message: 'Menjalankan pengujian...' };
                    try {
                        const r = await fetch('/admin/verifikasi/test?key=' + key);
                        const data = await r.json();
                        this.results[key] = data.result;
                    } catch(e) {
                        this.results[key] = { status: 'failed', message: 'Gagal: ' + e.message };
                    }
                },
                async runAll() {
                    const keys = ['produk_crud','pelanggan_crud','pesanan_flow','unit_apar_sync','activity_log','complain_flow','testimoni_flow','stok_fifo'];
                    for (const key of keys) await this.runTest(key);
                }
            }">
                @foreach($moduleTests as $test)
                    <div class="bg-white rounded-2xl border border-gray-100 p-7 hover:shadow-lg transition"
                         :class="results['{{ $test['key'] }}']?.status === 'failed' ? 'ring-2 ring-red-200' : results['{{ $test['key'] }}']?.status === 'passed' ? 'ring-2 ring-emerald-200' : ''">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                                    <span class="text-lg">@switch($test['key'])
                                        @case('produk_crud') 🔧 @break
                                        @case('pelanggan_crud') 👥 @break
                                        @case('pesanan_flow') 📦 @break
                                        @case('unit_apar_sync') 🔄 @break
                                        @case('activity_log') 📋 @break
                                        @case('complain_flow') ⚠️ @break
                                        @case('testimoni_flow') ⭐ @break
                                        @case('stok_fifo') 📦 @break
                                        @default ❓ @endswitch</span>
                                </div>
                                <div>
                                    <h4 class="text-base font-black text-gray-900">{{ $test['label'] }}</h4>
                                    <p class="text-xs text-gray-500 font-medium mt-1 leading-relaxed">{{ $test['desc'] }}</p>
                                </div>
                            </div>
                            <div x-show="results['{{ $test['key'] }}']?.status === 'running'" x-cloak>
                                <svg class="w-6 h-6 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </div>
                            <div x-show="results['{{ $test['key'] }}']?.status === 'passed'" x-cloak class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div x-show="results['{{ $test['key'] }}']?.status === 'warning'" x-cloak class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div x-show="results['{{ $test['key'] }}']?.status === 'failed'" x-cloak class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>
                        <div x-show="results['{{ $test['key'] }}']?.message" x-cloak class="text-xs text-gray-600 bg-gray-50 rounded-xl px-4 py-3 mb-4 font-medium leading-relaxed" x-text="results['{{ $test['key'] }}']?.message || ''"></div>
                        <button @click="runTest('{{ $test['key'] }}')" class="px-5 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition">
                            Jalankan Pengujian
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <button @click="runAll()" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/25 text-sm uppercase tracking-widest">
                    🔬 Jalankan Semua Pengujian
                </button>
            </div>
        </div>

        {{-- Recent Activity Log --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Log Sistem</p>
                    <h3 class="text-xl font-black text-gray-900 mt-1">Aktivitas Terbaru</h3>
                </div>
                <span class="px-4 py-2 bg-emerald-50 text-emerald-700 text-xs font-black rounded-xl">{{ $recentActivity->count() }} recent</span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentActivity as $log)
                    <div class="px-8 py-4 hover:bg-gray-50/40 transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 text-xs font-black mt-0.5">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900">{{ $log->description }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        @if($log->user)
                                            {{ $log->user->name }} &bull;
                                        @endif
                                        {{ $log->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <span class="shrink-0 px-3 py-1 bg-slate-50 text-slate-500 text-[10px] font-black uppercase rounded-lg">
                                {{ $log->event }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-8 py-10 text-center text-gray-400 font-medium text-sm">Belum ada aktivitas.</div>
                @endforelse
            </div>
        </div>

        {{-- System Summary --}}
        <div class="bg-gradient-to-br from-gray-900 to-slate-800 rounded-3xl p-8 text-white">
            <h3 class="text-2xl font-black tracking-tight mb-2">Ringkasan Pengujian Sistem</h3>
            <p class="text-gray-400 font-medium text-sm mb-8">Seluruh modul sistem telah diuji secara otomatis dan manual oleh teknisi.</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <p class="text-4xl font-black text-emerald-400">{{ count($moduleTests) }}</p>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Modul Diuji</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black text-blue-400">{{ $stats['total_pelanggan'] + $stats['total_produk'] + $stats['total_unit_apar'] }}</p>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Total Record Data</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black text-amber-400">{{ $stats['unit_aktif'] }}</p>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">APAR Aktif</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black text-red-400">{{ $stats['unit_expired'] }}</p>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">APAR Expired</p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>