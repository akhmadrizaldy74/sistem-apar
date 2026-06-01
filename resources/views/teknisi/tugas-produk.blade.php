<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Tugas Produk</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Daftar tugas pesanan produk yang saat ini ditugaskan khusus kepada Anda.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4">
            <p class="text-xs font-black uppercase tracking-widest text-red-700">Pesanan Produk</p>
            <p class="text-sm font-semibold text-red-900 mt-1">Tugas ini menampilkan pengantaran atau penanganan pesanan produk. Detail item tetap berasal dari transaksi pelanggan atau input admin.</p>
        </div>

        @include('teknisi.partials.active-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada tugas pesanan produk untuk Anda.'
        ])
    </div>
</x-app-layout>
