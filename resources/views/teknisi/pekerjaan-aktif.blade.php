<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900">Pekerjaan Aktif</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Daftar pekerjaan aktif dari admin dalam satu halaman.</p>
        </div>
    </x-slot>

    @php
        $tabs = [
            'semua' => ['label' => 'Semua', 'count' => $tabCounts['semua'] ?? 0],
            'produk' => ['label' => 'Pesanan Produk', 'count' => $tabCounts['produk'] ?? 0],
            'service-refill' => ['label' => 'Service / Refill', 'count' => $tabCounts['service-refill'] ?? 0],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap gap-3">
            @foreach($tabs as $key => $tab)
                <a
                    href="{{ $key === 'semua' ? route('teknisi.pekerjaan-aktif') : route('teknisi.pekerjaan-aktif', ['filter' => $key]) }}"
                    class="inline-flex items-center gap-3 rounded-full border px-4 py-2 text-sm font-bold transition {{ $activeFilter === $key ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[11px] {{ $activeFilter === $key ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        @include('teknisi.partials.active-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada pekerjaan yang diberikan oleh admin.',
        ])
    </div>
</x-app-layout>
