<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Dashboard Teknisi</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Daftar tugas pengerjaan produk, service, dan refil APAR dari admin.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tugas Aktif</p>
                <p class="text-4xl font-black text-slate-900 mt-3">{{ $tasks->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Sedang Dikerjakan</p>
                <p class="text-4xl font-black text-blue-700 mt-3">{{ $tasks->where('status', 'dikerjakan teknisi')->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Selesai Bulan Ini</p>
                <p class="text-4xl font-black text-emerald-700 mt-3">{{ $summary['selesai_bulan_ini'] }}</p>
            </div>
        </div>

        @include('teknisi.partials.active-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada tugas yang diberikan oleh admin.'
        ])

        <div class="pt-6 border-t border-slate-200">
            <h3 class="text-xl font-black text-slate-900 tracking-tight mb-4">Riwayat Pekerjaan Anda</h3>
            @include('teknisi.partials.history-tasks-table', [
                'tasks' => $historyTasks,
                'emptyMessage' => 'Belum ada riwayat pengerjaan.'
            ])
        </div>
    </div>
</x-app-layout>
