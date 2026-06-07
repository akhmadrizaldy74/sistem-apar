<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Riwayat Pekerjaan</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Riwayat seluruh pekerjaan pesanan, service, dan refill yang sudah Anda selesaikan.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4">
            <p class="text-xs font-black uppercase tracking-widest text-emerald-700">Selesai Final</p>
            <p class="text-sm font-semibold text-emerald-900 mt-1">Urutan riwayat ditampilkan berdasarkan transaksi yang paling baru selesai agar teknisi lebih mudah mengecek pekerjaan terakhir.</p>
        </div>

        @include('teknisi.partials.history-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada pekerjaan yang diberikan oleh admin.'
        ])
    </div>
</x-app-layout>
