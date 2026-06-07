<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Pekerjaan Aktif</h2>
            <p class="text-sm font-semibold text-slate-500 mt-1">Daftar pekerjaan Service / Refill APAR dari admin yang siap dikerjakan teknisi.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4">
            <p class="text-xs font-black uppercase tracking-widest text-blue-700">Catatan</p>
            <p class="text-sm font-semibold text-blue-900 mt-1">Komunikasi ke pelanggan dilakukan oleh admin. Teknisi fokus pengerjaan dan catatan hasil lapangan.</p>
        </div>

        @include('teknisi.partials.active-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada pekerjaan yang diberikan oleh admin.'
        ])
    </div>
</x-app-layout>
