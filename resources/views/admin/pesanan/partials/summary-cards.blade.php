@php
    $cards = [
        [
            'label' => 'Total Transaksi',
            'value' => number_format((float) $summary['totalTransaksi'], 0, ',', '.'),
            'hint' => 'Semua transaksi pesanan yang tercatat di sistem.',
            'panel' => 'border-slate-200 bg-white',
            'label_class' => 'text-slate-600',
            'value_class' => 'text-slate-900',
            'hint_class' => 'text-slate-500',
            'badge_class' => 'bg-slate-100 text-slate-600',
            'badge' => 'Pesanan',
        ],
        [
            'label' => 'Pembelian Unit',
            'value' => number_format((float) $summary['totalPembelianUnit'], 0, ',', '.'),
            'hint' => 'Transaksi pembelian unit APAR dari pelanggan.',
            'panel' => 'border-rose-200 bg-rose-50/70',
            'label_class' => 'text-rose-700',
            'value_class' => 'text-rose-700',
            'hint_class' => 'text-rose-700/80',
            'badge_class' => 'bg-rose-100 text-rose-700',
            'badge' => 'Produk',
        ],
        [
            'label' => 'Refill APAR',
            'value' => number_format((float) $summary['totalRefill'], 0, ',', '.'),
            'hint' => 'Permintaan refill yang masuk ke sistem.',
            'panel' => 'border-emerald-200 bg-emerald-50/80',
            'label_class' => 'text-emerald-700',
            'value_class' => 'text-emerald-700',
            'hint_class' => 'text-emerald-700/85',
            'badge_class' => 'bg-emerald-100 text-emerald-700',
            'badge' => 'Refill',
        ],
        [
            'label' => 'Service APAR',
            'value' => number_format((float) $summary['totalService'], 0, ',', '.'),
            'hint' => 'Permintaan service APAR yang tercatat.',
            'panel' => 'border-violet-200 bg-violet-50/80',
            'label_class' => 'text-violet-700',
            'value_class' => 'text-violet-700',
            'hint_class' => 'text-violet-700/85',
            'badge_class' => 'bg-violet-100 text-violet-700',
            'badge' => 'Service',
        ],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
    @foreach(array_slice($cards, 0, 2) as $card)
        <article class="min-h-[138px] rounded-2xl border p-4 shadow-sm sm:p-5 {{ $card['panel'] }}">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-bold {{ $card['label_class'] }}">{{ $card['label'] }}</p>
                    <p class="mt-3 break-words text-2xl font-black leading-tight tracking-tight sm:text-[1.75rem] {{ $card['value_class'] }}">
                        {{ $card['value'] }}
                    </p>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $card['badge_class'] }}">
                    {{ $card['badge'] }}
                </span>
            </div>
            <p class="mt-4 text-sm font-medium leading-6 {{ $card['hint_class'] }}">
                {{ $card['hint'] }}
            </p>
        </article>
    @endforeach

    <article class="min-h-[138px] rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm font-bold text-emerald-700">Penghasilan Pesanan</p>
                <p class="mt-3 break-words text-2xl font-black leading-tight tracking-tight text-emerald-800 sm:text-[1.75rem]">
                    Rp {{ number_format((float) $summary['penghasilanPesanan'], 0, ',', '.') }}
                </p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-2">
                <label for="pesanan-revenue-period" class="sr-only">Periode Penghasilan Pesanan</label>
                <select
                    id="pesanan-revenue-period"
                    class="rounded-full border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                    onchange="const url = new URL(window.location.href); url.searchParams.set('revenue_period', this.value); window.location = url.toString();"
                >
                    <option value="month" @selected(($summary['revenuePeriod'] ?? 'month') === 'month')>Bulan Ini</option>
                    <option value="all" @selected(($summary['revenuePeriod'] ?? 'month') === 'all')>Keseluruhan</option>
                </select>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Pembayaran Valid</span>
            </div>
        </div>
        <p class="mt-4 text-sm font-medium leading-6 text-emerald-700/85">
            Menghitung transaksi dengan pembayaran valid sesuai periode yang dipilih.
        </p>
    </article>

    @foreach(array_slice($cards, 2) as $card)
        <article class="min-h-[138px] rounded-2xl border p-4 shadow-sm sm:p-5 {{ $card['panel'] }}">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-bold {{ $card['label_class'] }}">{{ $card['label'] }}</p>
                    <p class="mt-3 break-words text-2xl font-black leading-tight tracking-tight sm:text-[1.75rem] {{ $card['value_class'] }}">
                        {{ $card['value'] }}
                    </p>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $card['badge_class'] }}">
                    {{ $card['badge'] }}
                </span>
            </div>
            <p class="mt-4 text-sm font-medium leading-6 {{ $card['hint_class'] }}">
                {{ $card['hint'] }}
            </p>
        </article>
    @endforeach
</div>
