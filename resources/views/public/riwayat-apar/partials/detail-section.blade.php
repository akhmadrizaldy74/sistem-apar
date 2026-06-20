{{-- Detail Section Partial for Transaction --}}

@php
    $pricingSummary = $pesanan->pricingSummary();
    $totalHarga = $pesanan->payableTotal();
    $ongkir = (float) ($pesanan->ongkir ?: 0);
    $subtotal = $totalHarga - $ongkir;
    $purchasePriceLabel = $pesanan->purchasePriceStatusLabel();
    $normalSubtotal = (float) $pesanan->purchasePriceNormalSubtotal();
    $discountedTotal = (float) $pesanan->purchasePriceDiscountedTotal();
    $initialTotal = (float) $pesanan->purchasePriceInitialTotal();
    $approvedAdjustment = max(0, $initialTotal - (float) ($pricingSummary['totalPembayaran'] ?? 0));
    $adminNote = $pesanan->purchasePriceAdminNote();
@endphp

<div class="grid md:grid-cols-2 gap-6">
    {{-- Left Column: Order Info --}}
    <div class="space-y-4">
        <h4 class="font-bold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-red-500"></i>
            Informasi Pesanan
        </h4>

        <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-3">
            {{-- Payment Method --}}
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card w-4 text-slate-400"></i>
                    Metode Pembayaran
                </span>
                <span class="text-sm font-semibold text-slate-700">
                    {{ ucfirst($pesanan->metode_pembayaran ?? '-') }}
                </span>
            </div>

            {{-- Payment Status --}}
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-check-circle w-4 text-slate-400"></i>
                    Status Pembayaran
                </span>
                <span class="text-sm font-semibold {{ $pesanan->isPaymentConfirmed() ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $pesanan->isPaymentConfirmed() ? 'Sudah Bayar' : 'Belum Bayar' }}
                </span>
            </div>

            {{-- Delivery Method --}}
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-truck w-4 text-slate-400"></i>
                    Metode Pengiriman
                </span>
                <span class="text-sm font-semibold text-slate-700">
                    {{ $pesanan->trackingMethodLabel() }}
                </span>
            </div>

            {{-- Recipient (if available) --}}
            @if($pesanan->nama_penerima)
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-user w-4 text-slate-400"></i>
                    Penerima
                </span>
                <span class="text-sm font-semibold text-slate-700">
                    {{ $pesanan->nama_penerima }}
                </span>
            </div>
            @endif

        </div>

        {{-- Service specific info --}}
        @if($pesanan->tipe === 'service' && $pesanan->service_keluhan)
        <div class="bg-amber-50 rounded-xl border border-amber-200 p-4">
            <p class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-2 flex items-center gap-1">
                <i class="fa-solid fa-exclamation-triangle"></i>
                Keluhan Pelanggan
            </p>
            <p class="text-sm text-slate-700 leading-relaxed">{{ $pesanan->service_keluhan }}</p>
        </div>
        @endif

        {{-- Admin Notes --}}
        @if($pesanan->catatan_admin)
        <div class="bg-purple-50 rounded-xl border border-purple-200 p-4">
            <p class="text-xs font-bold text-purple-600 uppercase tracking-widest mb-2 flex items-center gap-1">
                <i class="fa-solid fa-sticky-note"></i>
                Catatan Admin
            </p>
            <p class="text-sm text-slate-700 leading-relaxed">{{ $pesanan->catatan_admin }}</p>
        </div>
        @endif

        @if($purchasePriceLabel)
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Pengajuan Harga Pembelian</p>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $pesanan->purchasePriceStatusClasses() }}">
                    {{ $purchasePriceLabel }}
                </span>
            </div>
            <div class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total Normal</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($normalSubtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Setelah Diskon</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($discountedTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Ongkir</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($ongkir, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total Awal</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($initialTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Harga Pengajuan</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format((float) ($pricingSummary['hargaPengajuan'] ?? 0), 0, ',', '.') }}</span>
                </div>
                @if($approvedAdjustment > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Penyesuaian Harga Disetujui</span>
                        <span class="font-semibold text-emerald-700">-Rp {{ number_format($approvedAdjustment, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($pesanan->purchasePriceCustomerNote())
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Catatan Pelanggan</p>
                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $pesanan->purchasePriceCustomerNote() }}</p>
                    </div>
                @endif
                @if($adminNote)
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Catatan Admin</p>
                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $adminNote }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column: Items & Summary --}}
    <div class="space-y-4">
        <h4 class="font-bold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-box text-red-500"></i>
            Rincian Pesanan
        </h4>

        <div class="bg-white rounded-xl border border-slate-200 p-4">
            @if($pesanan->tipe === 'service')
                {{-- Service Item --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-start pb-3 border-b border-slate-100">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">
                                @if($pesanan->service_jenis_layanan === 'refill')
                                    Refill APAR
                                @else
                                    {{ $pesanan->servicePaket?->nama ?? 'Paket Service' }}
                                @endif
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                @if($pesanan->service_jenis_layanan === 'refill')
                                    Refill {{ $pesanan->serviceJenisRefill?->nama_label ?? '' }}
                                @else
                                    {{ $pesanan->servicePaket ? ucfirst($pesanan->servicePaket->jenis_layanan) : '-' }}
                                @endif
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">
                            {{ (int) ($pesanan->service_jumlah_unit ?? 0) }} unit
                        </span>
                    </div>

                    @if($pesanan->service_total_kg)
                    <div class="flex justify-between items-center py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Total Berat Refill</span>
                        <span class="text-sm font-semibold text-slate-700">
                            {{ rtrim(rtrim(number_format((float) $pesanan->service_total_kg, 2, ',', '.'), '0'), ',') }} kg
                        </span>
                    </div>
                    @endif

                    @if($pesanan->service_jenis_refill_id)
                    <div class="flex justify-between items-center py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Jenis Refill</span>
                        <span class="text-sm font-semibold text-slate-700">
                            {{ $pesanan->serviceJenisRefill?->nama_label ?? '-' }}
                        </span>
                    </div>
                    @endif

            @if($pesanan->service_estimasi_biaya)
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-sm text-slate-500">Estimasi Biaya</span>
                <span class="text-sm font-semibold text-amber-600">
                    Rp {{ number_format((float) $pesanan->service_estimasi_biaya, 0, ',', '.') }}
                </span>
            </div>
            @endif

            @if($pesanan->isServiceOrder())
                @php
                    $serviceLines = $pesanan->servicePricingBreakdown();
                    $servicePeralatan = $pesanan->servicePeralatanItems();
                @endphp
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Harga Per Unit</p>
                        <div class="space-y-2">
                            @foreach($serviceLines as $line)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                    <p class="text-sm font-semibold text-slate-700">{{ $line['label'] }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ (int) ($line['qty'] ?? 1) }} unit • Rp {{ number_format((float) ($line['total'] ?? 0), 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Peralatan Paket</p>
                        <div class="space-y-2">
                            @forelse($servicePeralatan as $item)
                                <div class="flex justify-between items-center rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                    <span class="text-sm font-semibold text-slate-700">{{ $item['nama'] ?? '-' }}</span>
                                    <span class="text-xs text-slate-400">x{{ (int) ($item['jumlah'] ?? 0) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Tidak ada peralatan terhubung.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>

            @else
                {{-- Product Items --}}
                @forelse($pesanan->details as $detail)
                <div class="flex justify-between items-start py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">{{ $detail->produk?->nama ?? 'Produk' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $detail->produk?->jenisApar?->nama ?? '' }} {{ $detail->produk?->kapasitas ? '• ' . $detail->produk->kapasitas : '' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-semibold text-slate-700">x{{ $detail->jumlah }}</span>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Rp {{ number_format((float) ($detail->harga ?? 0), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">Tidak ada item</p>
                @endforelse
            @endif
        </div>

        {{-- Price Summary --}}
        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
            <div class="flex justify-between items-center py-2">
                <span class="text-sm text-slate-500">Subtotal Produk / Layanan</span>
                <span class="text-sm font-semibold text-slate-700">
                    Rp {{ number_format((float) ($pricingSummary['subtotalProduk'] ?? 0), 0, ',', '.') }}
                </span>
            </div>
            <div class="flex justify-between items-center py-2 border-t border-slate-200">
                <span class="text-sm text-slate-500">Diskon</span>
                <span class="text-sm font-semibold {{ (float) ($pricingSummary['nominalDiskon'] ?? 0) > 0 ? 'text-emerald-700' : 'text-slate-700' }}">
                    {{ (float) ($pricingSummary['nominalDiskon'] ?? 0) > 0 ? '-' : '' }}Rp {{ number_format((float) ($pricingSummary['nominalDiskon'] ?? 0), 0, ',', '.') }}
                </span>
            </div>
            <div class="flex justify-between items-center py-2 border-t border-slate-200">
                <span class="text-sm text-slate-500">Biaya Pengiriman</span>
                <span class="text-sm font-semibold text-slate-700">
                    Rp {{ number_format($ongkir, 0, ',', '.') }}
                </span>
            </div>
            @if($approvedAdjustment > 0)
                <div class="flex justify-between items-center py-2 border-t border-slate-200">
                    <span class="text-sm text-slate-500">Penyesuaian Harga Disetujui</span>
                    <span class="text-sm font-semibold text-emerald-700">
                        -Rp {{ number_format($approvedAdjustment, 0, ',', '.') }}
                    </span>
                </div>
            @endif

            <div class="flex justify-between items-center py-3 border-t border-slate-200 mt-2">
                <span class="text-base font-bold text-slate-900">Total Pembayaran</span>
                <span class="text-lg font-black text-red-600">
                    Rp {{ number_format($totalHarga, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Additional Notes --}}
@if($pesanan->keterangan && $pesanan->tipe !== 'service')
<div class="mt-6 pt-6 border-t border-slate-200">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Keterangan Tambahan</p>
    <p class="text-sm text-slate-600 leading-relaxed bg-slate-50 rounded-lg p-3">{{ $pesanan->keterangan }}</p>
</div>
@endif
