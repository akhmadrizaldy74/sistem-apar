<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Riwayat Tugas</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Tugas yang sudah diselesaikan oleh teknisi dan status lanjutannya di admin.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4">
            <p class="text-xs font-black uppercase tracking-widest text-emerald-700">Riwayat</p>
            <p class="text-sm font-semibold text-emerald-900 mt-1">Data tidak hilang setelah selesai. Semua tugas otomatis dipindahkan ke menu riwayat.</p>
        </div>

        @include('teknisi.partials.history-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada riwayat tugas untuk akun ini.'
        ])
    </div>
</x-app-layout>

