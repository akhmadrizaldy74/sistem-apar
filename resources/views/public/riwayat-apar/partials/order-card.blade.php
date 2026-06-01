@php
    $isRejected = $pesanan->status === \App\Models\Pesanan::STATUS_DITOLAK;
    $totalHarga = $pesanan->payableTotal();
    $unitInfo = $pesanan->getUnitInfo();
    $linkedTestimoni = $pesanan->linkedTestimoni ?? null;
    $canReview = $pesanan->canGiveReview() || ($pesanan->isCompleted() && !$linkedTestimoni);
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

        @if($linkedTestimoni)
            @php
                $reviewStatusClass = match($linkedTestimoni->status) {
                    'approved' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-red-50 text-red-700',
                    default => 'bg-amber-50 text-amber-700',
                };
            @endphp
            <div class="mt-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Penilaian Anda</p>
                <div class="mt-2 rounded-xl border border-slate-100 bg-white px-4 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            @for($i = 0; $i < $linkedTestimoni->rating; $i++)
                                <i class="fa-solid fa-star"></i>
                            @endfor
                            @for($i = $linkedTestimoni->rating; $i < 5; $i++)
                                <i class="fa-regular fa-star text-slate-300"></i>
                            @endfor
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $reviewStatusClass }}">{{ $linkedTestimoni->status }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-700">{{ $linkedTestimoni->review }}</p>
                    @if($linkedTestimoni->admin_note)
                        <div class="mt-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-3">
                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Balasan Admin</p>
                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $linkedTestimoni->admin_note }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($pesanan->complain)
            @php
                $complain = $pesanan->complain;
                $complainStatusClass = match($complain->status_penyelesaian) {
                    'selesai' => 'bg-emerald-50 text-emerald-700',
                    'diproses' => 'bg-amber-50 text-amber-700',
                    default => 'bg-red-50 text-red-700',
                };
                $complainStatusText = match($complain->status_penyelesaian) {
                    'selesai' => 'Komplain sudah diselesaikan. Jika masih ada kendala, Anda bisa kirim komplain baru lewat admin.',
                    'diproses' => 'Komplain sedang ditangani. Admin biasanya menindaklanjuti detailnya lewat WhatsApp.',
                    default => 'Komplain sudah tercatat dan menunggu follow up dari admin via WhatsApp.',
                };
            @endphp
            <div class="mt-4">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Status Komplain</p>
                <div class="mt-2 rounded-xl border border-slate-100 bg-white px-4 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $complainStatusClass }}">{{ $complain->status_penyelesaian }}</span>
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
            @if($pesanan->isPaymentConfirmed() || $pesanan->isCompleted() || in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin', 'telepon'], true))
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
                <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6282124716109') }}?text={{ urlencode('Halo, saya siap menjemput ' . strtolower($pesanan->transactionDisplayName()) . ' pada ' . $pesanan->displayTransactionDateTime()) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                    Konfirmasi Pengambilan
                </a>
            @endif

            @if($canReview)
                <a href="{{ route('testimoni.create', ['pesanan' => $pesanan->id]) }}" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-black text-amber-700 transition hover:bg-amber-100">
                    <i class="fa-solid fa-star text-[10px]"></i>
                    Beri Penilaian
                </a>
            @endif

            <a href="{{ route('complain.create', ['pesanan' => $pesanan->id]) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                <i class="fa-solid fa-headset text-[10px] text-red-500"></i>
                {{ $pesanan->complain ? 'Lihat Komplain' : 'Butuh Bantuan / Komplain' }}
            </a>

        </div>
    </div>
</article>
