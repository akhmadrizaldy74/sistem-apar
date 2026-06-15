<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900">Riwayat Pekerjaan</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Daftar pekerjaan yang sudah diselesaikan teknisi.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @include('teknisi.partials.history-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada pekerjaan yang diberikan oleh admin.',
        ])
    </div>
</x-app-layout>
