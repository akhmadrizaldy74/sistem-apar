@php
    $productExpiryAlerts = $productExpiryAlerts ?? [];
    $hasIssues = (bool) ($productExpiryAlerts['hasIssues'] ?? false);
    $items = collect($productExpiryAlerts['items'] ?? []);
    $headline = (string) ($productExpiryAlerts['headline'] ?? '');
    $summaryText = (string) ($productExpiryAlerts['summaryText'] ?? '');
    $helperText = (string) ($productExpiryAlerts['helperText'] ?? '');
    $safeMessage = (string) ($productExpiryAlerts['safeMessage'] ?? 'Belum ada stok APAR yang mendekati masa expired.');
    $warningFilter = (string) ($productExpiryAlerts['warningFilter'] ?? 'masa-berlaku');
@endphp

<section class="overflow-hidden rounded-2xl border {{ $hasIssues ? 'border-amber-200 bg-white' : 'border-emerald-200 bg-white' }} shadow-sm">
    <div class="border-b {{ $hasIssues ? 'border-amber-100 bg-amber-50/60' : 'border-emerald-100 bg-emerald-50/60' }} px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base font-black text-slate-900 md:text-lg">Peringatan Masa Berlaku Stok APAR</h3>
                    @if($hasIssues)
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-[11px] font-black text-amber-800">
                            {{ number_format((int) ($productExpiryAlerts['totalIssueCount'] ?? 0), 0, ',', '.') }} produk
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-black text-emerald-800">
                            Aman
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm font-medium leading-6 {{ $hasIssues ? 'text-amber-900/80' : 'text-emerald-900/80' }}">
                    {{ $headline !== '' ? $headline : $safeMessage }}
                </p>
                @if($helperText !== '')
                    <p class="mt-1 text-xs font-semibold leading-5 {{ $hasIssues ? 'text-amber-700/80' : 'text-emerald-700/80' }}">
                        {{ $helperText }}
                    </p>
                @endif
            </div>

            <a href="{{ route('admin.stok.index', ['tab' => 'apar', 'filter' => $warningFilter]) }}" class="inline-flex w-fit items-center rounded-full bg-white px-4 py-2 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:text-red-700 hover:ring-red-200">
                Buka Stok APAR
            </a>
        </div>
    </div>

    @if($hasIssues)
        <div class="p-5 sm:p-6">
            @if($summaryText !== '')
                <p class="text-sm font-semibold leading-6 text-slate-600">{{ $summaryText }}</p>
            @endif

            <div class="mt-4 space-y-3">
                @foreach($items as $item)
                    <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3.5">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-sm font-black text-slate-900">{{ $item['product_name'] ?? $item['name'] ?? 'Produk APAR' }}</p>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-black {{ $item['status_badge_class'] }}">
                                    Status masa berlaku: {{ $item['status_label'] }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Merek: {{ $item['brand'] }} | Ukuran: {{ $item['kapasitas'] }}</p>
                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Stok: {{ $item['stock_total_label'] }}</p>
                            <p class="mt-1 text-xs font-semibold leading-5 {{ $item['status_text_class'] }}">{{ $item['status_detail'] }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Masa Berlaku</p>
                            <p class="mt-1 text-sm font-black {{ $item['expiry_text_class'] }}">{{ $item['expired_at_label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(($productExpiryAlerts['remainingCount'] ?? 0) > 0)
                <p class="mt-3 text-xs font-semibold text-slate-500">
                    +{{ number_format((int) $productExpiryAlerts['remainingCount'], 0, ',', '.') }} produk lain juga ada di daftar stok APAR bermasalah.
                </p>
            @endif
        </div>
    @else
        <div class="px-5 py-5 sm:px-6">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-4 text-sm font-medium leading-6 text-emerald-800">
                {{ $safeMessage }}
            </div>
        </div>
    @endif
</section>
