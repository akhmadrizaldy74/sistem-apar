<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Pekerjaan Aktif</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Daftar pekerjaan pesanan produk yang sedang aktif untuk Anda.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4">
            <p class="text-xs font-black uppercase tracking-widest text-red-700">Pesanan Produk</p>
            <p class="text-sm font-semibold text-red-900 mt-1">Halaman ini menampilkan pengantaran atau penanganan pesanan produk. Detail item tetap berasal dari transaksi pelanggan atau input admin.</p>
        </div>

        @include('teknisi.partials.active-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada pekerjaan yang diberikan oleh admin.'
        ])
    </div>
</x-app-layout>
