@php
    $stockAlerts = $stockAlerts ?? [];
    $audience = $audience ?? 'admin';
    $groups = collect($stockAlerts['groups'] ?? []);
    $hasIssues = (bool) ($stockAlerts['hasIssues'] ?? false);
    $helperText = (string) ($stockAlerts['helperText'] ?? '');
    $safeMessage = (string) ($stockAlerts['safeMessage'] ?? 'Semua stok dalam kondisi aman');
@endphp

<section class="overflow-hidden rounded-2xl border {{ $hasIssues ? 'border-amber-200 bg-white' : 'border-emerald-200 bg-white' }} shadow-sm">
    <div class="border-b {{ $hasIssues ? 'border-amber-100 bg-amber-50/60' : 'border-emerald-100 bg-emerald-50/60' }} px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base font-black text-slate-900 md:text-lg">Peringatan Stok</h3>
                    @if($hasIssues)
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-[11px] font-black text-amber-800">
                            {{ number_format((int) ($stockAlerts['totalIssueCount'] ?? 0), 0, ',', '.') }} item
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-black text-emerald-800">
                            Aman
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm font-medium leading-6 {{ $hasIssues ? 'text-amber-900/80' : 'text-emerald-900/80' }}">
                    {{ $stockAlerts['headline'] ?? $safeMessage }}
                </p>
                @if($helperText !== '')
                    <p class="mt-1 text-xs font-semibold leading-5 {{ $hasIssues ? 'text-amber-700/80' : 'text-emerald-700/80' }}">
                        {{ $helperText }}
                    </p>
                @endif
            </div>

            @if($audience === 'admin')
                <a href="{{ route('admin.stok.index') }}" class="inline-flex w-fit items-center rounded-full bg-white px-4 py-2 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:text-red-700 hover:ring-red-200">
                    Lihat Manajemen Stok
                </a>
            @endif
        </div>
    </div>

    @if($hasIssues)
        <div class="grid gap-4 p-5 sm:p-6 {{ $groups->count() > 1 ? 'lg:grid-cols-2' : '' }}">
            @foreach($groups as $group)
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-sm font-black text-slate-900">{{ $group['label'] }}</h4>
                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">{{ $group['description'] }}</p>
                        </div>

                        @if($audience === 'admin')
                            <a href="{{ route('admin.stok.index', ['tab' => $group['tab']]) }}" class="shrink-0 text-xs font-black text-red-600 hover:text-red-700">
                                Buka
                            </a>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if(($group['emptyCount'] ?? 0) > 0)
                            <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-[11px] font-black text-red-700">
                                {{ number_format((int) $group['emptyCount'], 0, ',', '.') }} kosong
                            </span>
                        @endif
                        @if(($group['lowCount'] ?? 0) > 0)
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-[11px] font-black text-amber-800">
                                {{ number_format((int) $group['lowCount'], 0, ',', '.') }} menipis
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach($group['items'] as $item)
                            <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 px-3.5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900">{{ $item['name'] }}</p>
                                    @if(!empty($item['meta']))
                                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">{{ $item['meta'] }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-black {{ $item['status'] === 'empty' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $item['statusLabel'] }}
                                    </span>
                                    <p class="mt-2 text-sm font-black text-slate-900">{{ $item['stockLabel'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(($group['remainingCount'] ?? 0) > 0)
                        <p class="mt-3 text-xs font-semibold text-slate-500">
                            +{{ number_format((int) $group['remainingCount'], 0, ',', '.') }} item lain perlu perhatian.
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    @else
        <div class="px-5 py-5 sm:px-6">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-4 text-sm font-medium leading-6 text-emerald-800">
                {{ $safeMessage }}{{ $audience === 'teknisi' ? ' Lanjutkan pekerjaan seperti biasa dan tetap koordinasi ke admin bila ada perubahan di lapangan.' : '' }}
            </div>
        </div>
    @endif
</section>
