@php
    $timelineData = $pesanan->getTimelineData();
    $isRejected = $pesanan->status === \App\Models\Pesanan::STATUS_DITOLAK;
    $totalHarga = $pesanan->payableTotal();
    $unitInfo = $pesanan->getUnitInfo();
    $totalSteps = max(1, count($timelineData));
    $currentStep = min($totalSteps, max(0, $pesanan->getTimelineStep()));
    $progressPercent = $isRejected
        ? 100
        : max(8, min(100, ($currentStep / $totalSteps) * 100));
@endphp

<article
    class="compact-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
    x-data="{ openDetail: false }"
>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-slate-700">
                    {{ $pesanan->trackingTypeLabel() }}
                </span>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-wide {{ $pesanan->publicStatusClasses() }}">
                    {{ $pesanan->publicStatusLabel() }}
                </span>
            </div>

            <h3 class="mt-2 truncate text-base font-black text-slate-950">{{ $pesanan->trackingItemLabel() }}</h3>

            <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2 xl:grid-cols-5">
                <div class="min-w-0 rounded-lg bg-slate-50 px-3 py-2 sm:col-span-2 xl:col-span-1">
                    <dt class="font-bold uppercase tracking-wide text-slate-400">Kode</dt>
                    <dd class="mt-1 break-all font-mono font-bold text-slate-700">{{ $pesanan->orderCode() }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <dt class="font-bold uppercase tracking-wide text-slate-400">Tanggal</dt>
                    <dd class="mt-1 font-bold text-slate-800">{{ optional($pesanan->tanggal)->format('d M Y') ?? '-' }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <dt class="font-bold uppercase tracking-wide text-slate-400">Metode</dt>
                    <dd class="mt-1 font-bold text-slate-800">{{ $pesanan->trackingMethodLabel() }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <dt class="font-bold uppercase tracking-wide text-slate-400">Unit</dt>
                    <dd class="mt-1 font-bold text-slate-800">{{ $unitInfo ? $unitInfo['jumlah'] . ' unit' : '-' }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <dt class="font-bold uppercase tracking-wide text-slate-400">Total</dt>
                    <dd class="mt-1 font-black text-slate-950">Rp {{ number_format($totalHarga, 0, ',', '.') }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex shrink-0 gap-2 lg:flex-col lg:items-end">
            @if($pesanan->canPay())
                <a href="{{ route('order.payment', $pesanan) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-xs font-black text-white transition hover:bg-red-700">
                    <i class="fa-solid fa-credit-card text-[10px]"></i>
                    Bayar
                </a>
            @endif
            <button
                type="button"
                @click="openDetail = !openDetail"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
            >
                <span x-text="openDetail ? 'Tutup' : 'Lihat Detail'">Lihat Detail</span>
                <i class="fa-solid text-[10px]" :class="openDetail ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
    </div>

    <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-3 py-3">
        <div class="flex items-center justify-between gap-3 text-xs font-bold">
            <span class="truncate text-slate-600">{{ $isRejected ? 'Pesanan ditolak' : $pesanan->publicStatusLabel() }}</span>
            <span class="shrink-0 text-slate-400">{{ $isRejected ? 'Ditolak' : 'Tahap ' . $currentStep . '/' . $totalSteps }}</span>
        </div>
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white">
            <div
                class="h-full rounded-full {{ $isRejected ? 'bg-red-500' : 'bg-red-600' }}"
                style="width: {{ $progressPercent }}%"
            ></div>
        </div>
    </div>

    <div x-show="openDetail" x-cloak x-transition.opacity.duration.150ms class="mt-4 border-t border-slate-100 pt-4">
        <dl class="grid gap-2 text-xs sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                <dt class="font-bold uppercase tracking-wide text-slate-400">Pembayaran</dt>
                <dd class="mt-1 font-black text-slate-900">{{ $pesanan->getPaymentMethodLabel() }}</dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                <dt class="font-bold uppercase tracking-wide text-slate-400">Status Bayar</dt>
                <dd class="mt-1 font-black {{ $pesanan->isPaymentConfirmed() ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $pesanan->isPaymentConfirmed() ? 'Lunas' : 'Belum selesai' }}
                </dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                <dt class="font-bold uppercase tracking-wide text-slate-400">Teknisi</dt>
                <dd class="mt-1 font-black text-slate-900">{{ $pesanan->teknisi?->name ?? '-' }}</dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                <dt class="font-bold uppercase tracking-wide text-slate-400">Dibuat</dt>
                <dd class="mt-1 font-black text-slate-900">{{ $pesanan->created_at?->format('d M Y H:i') ?? '-' }}</dd>
            </div>
        </dl>

        @if($pesanan->tipe === 'produk' && $pesanan->details->isNotEmpty())
            <div class="mt-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Item Pembelian</p>
                <div class="mt-2 divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-100 bg-white">
                    @foreach($pesanan->details as $detail)
                        <div class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-black text-slate-900">{{ $detail->produk?->nama ?? 'Produk APAR' }}</p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ $detail->merek ?? '-' }} - {{ $detail->kapasitas ?? '-' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="font-black text-slate-900">x{{ $detail->jumlah }}</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-500">Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($pesanan->tipe === 'service')
            <div class="mt-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Detail Layanan</p>
                <dl class="mt-2 grid gap-2 text-xs sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                        <dt class="font-bold uppercase tracking-wide text-slate-400">Layanan</dt>
                        <dd class="mt-1 font-black text-slate-900">{{ $pesanan->trackingTypeLabel() }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                        <dt class="font-bold uppercase tracking-wide text-slate-400">Jenis APAR</dt>
                        <dd class="mt-1 font-black text-slate-900">{{ $pesanan->service_jenis_apar ?? '-' }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                        <dt class="font-bold uppercase tracking-wide text-slate-400">Ukuran</dt>
                        <dd class="mt-1 font-black text-slate-900">{{ $pesanan->service_ukuran_apar ?? '-' }}</dd>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                        <dt class="font-bold uppercase tracking-wide text-slate-400">Jumlah</dt>
                        <dd class="mt-1 font-black text-slate-900">{{ (int) ($pesanan->service_jumlah_unit ?? 0) }} unit</dd>
                    </div>
                </dl>
            </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-2">
            @if($pesanan->canPay())
                <a href="{{ route('order.payment', $pesanan) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-xs font-black text-white transition hover:bg-red-700">
                    <i class="fa-solid fa-credit-card text-[10px]"></i>
                    Bayar Sekarang
                </a>
            @endif

            @if($pesanan->needsPickup())
                <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6282124716109') }}?text={{ urlencode('Halo, saya siap menjemput pesanan ' . $pesanan->orderCode()) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                    Konfirmasi Pengambilan
                </a>
            @endif
        </div>
    </div>
</article>
