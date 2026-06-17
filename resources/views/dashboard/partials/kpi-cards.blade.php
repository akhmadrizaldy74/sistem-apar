@php
    $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
    $cards = [
        [
            'label' => 'Produk',
            'value' => number_format($kpis['totalProduk']),
            'hint' => 'Total produk yang tersedia di katalog.',
            'panel' => 'border-slate-200 bg-white',
            'valueClass' => 'text-slate-900',
            'badgeClass' => 'bg-slate-100 text-slate-600',
            'badge' => 'Data',
        ],
        [
            'label' => 'Pelanggan',
            'value' => number_format($kpis['totalPelanggan']),
            'hint' => 'Pelanggan yang sudah terdaftar di sistem.',
            'panel' => 'border-slate-200 bg-white',
            'valueClass' => 'text-slate-900',
            'badgeClass' => 'bg-slate-100 text-slate-600',
            'badge' => 'Data',
        ],
        [
            'label' => 'Pesanan',
            'value' => number_format($kpis['totalPesanan']),
            'hint' => 'Semua pesanan produk dan layanan yang tercatat.',
            'panel' => 'border-slate-200 bg-white',
            'valueClass' => 'text-slate-900',
            'badgeClass' => 'bg-slate-100 text-slate-600',
            'badge' => 'Transaksi',
        ],
        [
            'label' => 'Komplain',
            'value' => number_format($kpis['totalKomplain']),
            'hint' => 'Laporan komplain pelanggan yang masuk.',
            'panel' => 'border-slate-200 bg-white',
            'valueClass' => 'text-slate-900',
            'badgeClass' => 'bg-slate-100 text-slate-600',
            'badge' => 'Feedback',
        ],
        [
            'label' => 'Unit APAR',
            'value' => number_format($kpis['totalUnitApar']),
            'hint' => 'Unit APAR pelanggan yang sedang dimonitor.',
            'panel' => 'border-blue-100 bg-blue-50/70',
            'valueClass' => 'text-blue-700',
            'badgeClass' => 'bg-blue-100 text-blue-700',
            'badge' => 'Unit',
        ],
        [
            'label' => 'Pengunjung Hari Ini',
            'value' => number_format($visitorStats['hariIni']),
            'hint' => 'Kunjungan website yang tercatat hari ini.',
            'panel' => 'border-violet-200 bg-violet-50/80',
            'valueClass' => 'text-violet-700',
            'badgeClass' => 'bg-violet-100 text-violet-700',
            'badge' => 'Live',
        ],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
    @foreach(array_slice($cards, 0, 2) as $card)
        <div class="min-h-[138px] rounded-2xl border p-4 shadow-sm sm:p-5 {{ $card['panel'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-600">{{ $card['label'] }}</p>
                    <p class="mt-3 break-words text-2xl font-black leading-tight tracking-tight sm:text-[1.75rem] {{ $card['valueClass'] }}">
                        {{ $card['value'] }}
                    </p>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $card['badgeClass'] }}">
                    {{ $card['badge'] }}
                </span>
            </div>
            <p class="mt-4 text-sm font-medium leading-6 text-slate-500">
                {{ $card['hint'] }}
            </p>
        </div>
    @endforeach

    <div
        data-revenue-card
        data-month-value="{{ $formatRupiah($kpis['pendapatanBulanIni'] ?? 0) }}"
        data-overall-value="{{ $formatRupiah($kpis['pendapatanKeseluruhan'] ?? 0) }}"
        data-month-hint="Menghitung transaksi selesai final pada bulan berjalan."
        data-overall-hint="Menghitung semua transaksi selesai final yang tersimpan."
        class="min-h-[138px] rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 shadow-sm sm:p-5"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-bold text-emerald-700">Total Pendapatan</p>
                <p data-revenue-amount class="mt-3 break-words text-2xl font-black leading-tight tracking-tight text-emerald-800 sm:text-[1.75rem]">
                    {{ $formatRupiah($kpis['pendapatanBulanIni'] ?? 0) }}
                </p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-2">
                <label for="dashboard-revenue-period" class="sr-only">Periode Total Pendapatan</label>
                <select
                    id="dashboard-revenue-period"
                    data-revenue-period
                    class="rounded-full border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="month" selected>Bulan Ini</option>
                    <option value="overall">Keseluruhan</option>
                </select>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Final</span>
            </div>
        </div>
        <p data-revenue-hint class="mt-4 text-sm font-medium leading-6 text-emerald-700/85">
            Menghitung transaksi selesai final pada bulan berjalan.
        </p>
    </div>

    @foreach(array_slice($cards, 2) as $card)
        <div class="min-h-[138px] rounded-2xl border p-4 shadow-sm sm:p-5 {{ $card['panel'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-bold {{ str_contains($card['valueClass'], 'blue') ? 'text-blue-700' : (str_contains($card['valueClass'], 'violet') ? 'text-violet-700' : 'text-slate-600') }}">{{ $card['label'] }}</p>
                    <p class="mt-3 break-words text-2xl font-black leading-tight tracking-tight sm:text-[1.75rem] {{ $card['valueClass'] }}">
                        {{ $card['value'] }}
                    </p>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $card['badgeClass'] }}">
                    {{ $card['badge'] }}
                </span>
            </div>
            <p class="mt-4 text-sm font-medium leading-6 {{ str_contains($card['valueClass'], 'blue') ? 'text-blue-700/80' : (str_contains($card['valueClass'], 'violet') ? 'text-violet-700/80' : 'text-slate-500') }}">
                {{ $card['hint'] }}
            </p>
        </div>
    @endforeach
</div>
