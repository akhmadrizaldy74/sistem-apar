<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Tugas Produk</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Daftar tugas aktif untuk pesanan produk dari admin.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
            <p class="text-xs font-black uppercase tracking-widest text-amber-700">Flow</p>
            <p class="text-sm font-semibold text-amber-900 mt-1">Ditugaskan ke teknisi → Dikerjakan teknisi → Selesai oleh teknisi.</p>
        </div>

        @include('teknisi.partials.active-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada tugas produk aktif untuk Anda.'
        ])
    </div>
</x-app-layout>

