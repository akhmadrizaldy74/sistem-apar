@php
    $isRejected = $pesanan->status === \App\Models\Pesanan::STATUS_DITOLAK;
    $totalHarga = $pesanan->payableTotal();
    $unitInfo = $pesanan->getUnitInfo();
    $linkedTestimoni = $pesanan->linkedTestimoni ?? null;
    $linkedTestimoniStatus = (string) ($linkedTestimoni?->status ?? 'pending');
    $linkedTestimoniRating = max(0, min((int) ($linkedTestimoni?->rating ?? 0), 5));
    $reviewStatusClass = match($linkedTestimoniStatus) {
        'approved' => 'bg-emerald-50 text-emerald-700',
        'rejected' => 'bg-red-50 text-red-700',
        default => 'bg-amber-50 text-amber-700',
    };
    $complain = $pesanan->complain;
    $complainStatus = (string) ($complain?->status_penyelesaian ?? 'menunggu');
    $complainStatusClass = match($complainStatus) {
        'selesai' => 'bg-emerald-50 text-emerald-700',
        'diproses' => 'bg-amber-50 text-amber-700',
        default => 'bg-red-50 text-red-700',
    };
    $complainStatusText = match($complainStatus) {
        'selesai' => 'Komplain sudah diselesaikan. Jika masih ada kendala, Anda bisa kirim komplain baru lewat admin.',
        'diproses' => 'Komplain sedang ditangani. Admin biasanya menindaklanjuti detailnya lewat WhatsApp.',
        default => 'Komplain sudah tercatat dan menunggu follow up dari admin via WhatsApp.',
    };
    $canViewInvoice = $pesanan->canViewInvoice();
    $canConfirmReceived = $pesanan->canCustomerConfirmReceived();
    $hasConfirmed = $pesanan->hasCustomerConfirmed();
    $hasReviewed = $pesanan->hasSubmittedTestimonial() || (bool) $linkedTestimoni;
    $canReview = $pesanan->canSubmitCustomerReview();
    $purchasePriceLabel = $pesanan->purchasePriceStatusLabel();
    $paymentStateLabel = $pesanan->hasPendingPurchasePriceRequest()
        ? 'Menunggu Persetujuan Harga'
        : ($pesanan->isPaymentConfirmed() ? 'Lunas' : ($pesanan->canPay() ? 'Siap Dibayar' : 'Belum selesai'));
    $paymentStateClass = $pesanan->hasPendingPurchasePriceRequest()
        ? 'text-amber-700'
        : ($pesanan->isPaymentConfirmed() ? 'text-emerald-700' : 'text-amber-700');
    $pickupWaUrl = \App\Support\WhatsApp::companyLink(
        'Halo PD Anugrah Utama, saya siap menjemput ' . strtolower($pesanan->transactionDisplayName()) . ' pada ' . $pesanan->displayTransactionDateTime() . '.'
    );
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
                @if($purchasePriceLabel)
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $pesanan->purchasePriceStatusClasses() }}">
                        {{ $purchasePriceLabel }}
                    </span>
                @endif
            </div>

            <h3 class="mt-2 truncate text-base font-black text-slate-950">{{ $pesanan->trackingItemLabel() }}</h3>

            <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2 xl:grid-cols-5">
                <div class="min-w-0 rounded-lg bg-slate-50 px-3 py-2 sm:col-span-2 xl:col-span-1">
                    <dt class="font-bold uppercase tracking-wide text-slate-400">Waktu Transaksi</dt>
                    <dd class="mt-1 font-bold text-slate-800">{{ $pesanan->displayTransactionDateTime() }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <dt class="font-bold uppercase tracking-wide text-slate-400">Jenis Transaksi</dt>
                    <dd class="mt-1 font-bold text-slate-800">{{ $pesanan->transactionDisplayName() }}</dd>
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

    <div x-show="openDetail" x-cloak x-transition.opacity.duration.150ms class="mt-4 border-t border-slate-100 pt-4">
        <dl class="grid gap-2 text-xs sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                <dt class="font-bold uppercase tracking-wide text-slate-400">Pembayaran</dt>
                <dd class="mt-1 font-black text-slate-900">{{ $pesanan->getPaymentMethodLabel() }}</dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                <dt class="font-bold uppercase tracking-wide text-slate-400">Status Bayar</dt>
                <dd class="mt-1 font-black {{ $paymentStateClass }}">
                    {{ $paymentStateLabel }}
                </dd>
            </div>
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                <dt class="font-bold uppercase tracking-wide text-slate-400">Dibuat</dt>
                <dd class="mt-1 font-black text-slate-900">{{ $pesanan->displayTransactionDateTime() }}</dd>
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
            @php($serviceLines = $pesanan->servicePricingBreakdown())
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

                @if(!empty($serviceLines))
                    <div class="mt-3 divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-100 bg-white">
                        @foreach($serviceLines as $line)
                            <div class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm">
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-900">{{ $line['display_label'] ?? $line['label'] ?? 'Layanan APAR' }}</p>
                                    <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                        {{ (int) ($line['qty'] ?? 1) }} unit
                                        @if(!empty($line['ukuran']))
                                            - {{ $line['ukuran'] }}
                                        @endif
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="font-black text-slate-900">Rp {{ number_format((float) ($line['total'] ?? 0), 0, ',', '.') }}</p>
                                    @if(!empty($line['unit_price']))
                                        <p class="mt-0.5 text-xs font-bold text-slate-500">Rp {{ number_format((float) $line['unit_price'], 0, ',', '.') }}/unit</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if($linkedTestimoni)
            <div class="mt-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Penilaian Anda</p>
                <div class="mt-2 rounded-xl border border-slate-100 bg-white px-4 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            @for($i = 0; $i < $linkedTestimoniRating; $i++)
                                <i class="fa-solid fa-star"></i>
                            @endfor
                            @for($i = $linkedTestimoniRating; $i < 5; $i++)
                                <i class="fa-regular fa-star text-slate-300"></i>
                            @endfor
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $reviewStatusClass }}">{{ $linkedTestimoniStatus }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-700">{{ $linkedTestimoni?->review ?? 'Ulasan sudah tercatat, tetapi detailnya belum tersedia.' }}</p>
                    @if(filled($linkedTestimoni?->admin_note))
                        <div class="mt-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-3">
                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Balasan Admin</p>
                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $linkedTestimoni?->admin_note }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($complain)
            <div class="mt-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Status Komplain</p>
                <div class="mt-2 rounded-xl border border-slate-100 bg-white px-4 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $complainStatusClass }}">{{ $complainStatus }}</span>
                        <span class="text-xs font-bold text-slate-400">{{ $complain->displaySubmittedDateTime() }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-700">{{ $complain->isi_complain }}</p>
                    <div class="mt-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-3">
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Update</p>
                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $complainStatusText }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-2">
            @if($pesanan->hasPendingPurchasePriceRequest())
                <div class="w-full rounded-lg border border-amber-200 bg-amber-50 px-3 py-3 text-xs font-semibold leading-5 text-amber-800">
                    Pengajuan harga masih menunggu keputusan admin. Invoice detail tetap bisa dilihat sebagai dokumen transaksi, tetapi tombol bayar dan upload bukti pembayaran baru aktif setelah admin menyetujui atau menolak pengajuan ini.
                </div>
            @endif

            @if($canViewInvoice)
                <a href="{{ route('invoice.show', $pesanan) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-xs font-black text-white transition hover:bg-red-700">
                    <i class="fa-solid fa-file-invoice text-[10px]"></i>
                    Lihat Invoice
                </a>
            @endif

            @if($pesanan->canPay())
                <a href="{{ route('order.payment', $pesanan) }}" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-xs font-black text-white transition hover:bg-red-700">
                    <i class="fa-solid fa-credit-card text-[10px]"></i>
                    Bayar Sekarang
                </a>
            @endif

            @if($pesanan->needsPickup())
                <a href="{{ $pickupWaUrl }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                    Konfirmasi Pengambilan
                </a>
            @endif

            @if($canConfirmReceived)
                <button type="button" @click.prevent="openConfirmModal({{ $pesanan->id }}, '{{ addslashes($pesanan->trackingItemLabel()) }}', '{{ addslashes($pesanan->displayTransactionDateTime()) }}', 'Rp{{ number_format($pesanan->payableTotal(), 0, ',', '.') }}', '{{ addslashes($pesanan->publicStatusLabel()) }}', '{{ addslashes($pesanan->trackingTypeLabel()) }}')" class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 transition hover:bg-blue-100">
                    <i class="fa-solid fa-box-open text-[10px]"></i>
                    Konfirmasi Pesanan Diterima
                </button>
            @elseif($hasConfirmed)
                <span class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    Pesanan Sudah Dikonfirmasi
                </span>
            @endif

            @if($canReview)
                <button type="button" @click.prevent="openTestimoniModal({{ $pesanan->id }}, '{{ addslashes($pesanan->trackingItemLabel()) }}', '{{ addslashes($pesanan->displayTransactionDateTime()) }}', 'Rp{{ number_format($pesanan->payableTotal(), 0, ',', '.') }}', '{{ addslashes($pesanan->publicStatusLabel()) }}', '{{ addslashes($pesanan->trackingTypeLabel()) }}')" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-black text-amber-700 transition hover:bg-amber-100">
                    <i class="fa-solid fa-star text-[10px]"></i>
                    Isi Ulasan
                </button>
            @elseif($hasReviewed)
                <span class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    Ulasan Terkirim
                </span>
            @endif

            @if($pesanan->complain)
                <button type="button" disabled class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black text-slate-400">
                    <i class="fa-solid fa-headset text-[10px] text-slate-400"></i>
                    Komplain Terkirim
                </button>
            @else
                <button type="button" @click.prevent="openComplainModal({{ $pesanan->id }}, '{{ addslashes($pesanan->trackingItemLabel()) }}', '{{ addslashes($pesanan->displayTransactionDateTime()) }}', 'Rp{{ number_format($pesanan->payableTotal(), 0, ',', '.') }}', '{{ addslashes($pesanan->publicStatusLabel()) }}', '{{ addslashes($pesanan->trackingTypeLabel()) }}')" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                    <i class="fa-solid fa-headset text-[10px] text-red-500"></i>
                    Butuh Bantuan / Komplain
                </button>
            @endif

        </div>
    </div>
</article>
