<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900">Pekerjaan Aktif</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Daftar pekerjaan yang sedang ditangani teknisi.</p>
        </div>
    </x-slot>

    @php
        $tabs = [
            'semua' => ['label' => 'Semua', 'count' => $tabCounts['semua'] ?? 0],
            'produk' => ['label' => 'Pesanan Produk', 'count' => $tabCounts['produk'] ?? 0],
            'service-refill' => ['label' => 'Service / Refill', 'count' => $tabCounts['service-refill'] ?? 0],
        ];
    @endphp

    <div class="mx-auto max-w-5xl space-y-5">
        <div class="flex flex-wrap gap-2">
            @foreach($tabs as $key => $tab)
                <a
                    href="{{ $key === 'semua' ? route('teknisi.pekerjaan-aktif') : route('teknisi.pekerjaan-aktif', ['filter' => $key]) }}"
                    class="inline-flex items-center gap-2 rounded-full border px-3.5 py-2 text-xs font-black uppercase tracking-widest transition sm:px-4 {{ $activeFilter === $key ? 'border-slate-900 bg-slate-900 text-white shadow-lg shadow-slate-900/10' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeFilter === $key ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        @include('teknisi.partials.active-tasks-table', [
            'tasks' => $tasks,
            'emptyMessage' => 'Belum ada pekerjaan aktif.',
            'emptyDescription' => 'Pekerjaan dari admin akan muncul di halaman ini.',
        ])
    </div>
</x-app-layout>
