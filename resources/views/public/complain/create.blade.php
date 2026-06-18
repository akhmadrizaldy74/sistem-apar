@extends('layouts.bootstrap')

@section('title', 'Layanan Komplain - PD. ANUGRAH UTAMA')
@section('hero_badge', 'Komplain Pelanggan')
@section('header_title', 'Layanan Komplain')
@section('header_subtitle', 'Sampaikan keluhan Anda, kami siap membantu.')

@section('content')
@php
    $selectedOrder = $selectedOrder ?? null;
    $existingComplain = $existingComplain ?? null;
    $pelanggan = $pelanggan ?? null;

    $statusClass = 'feedback-status-badge--neutral';
    $statusText = $selectedOrder?->publicStatusLabel() ?? '-';

    if ($selectedOrder) {
        $statusClass = match ((string) $selectedOrder->status) {
            'selesai', 'selesai final', 'selesai oleh teknisi' => 'feedback-status-badge--success',
            'pending', 'menunggu', 'menunggu pembayaran' => 'feedback-status-badge--warning',
            default => 'feedback-status-badge--info',
        };
    }

    $transactionSummary = '-';
    if ($selectedOrder) {
        if ($selectedOrder->isProductOrder()) {
            $firstDetail = $selectedOrder->details->first();
            $totalUnit = (int) $selectedOrder->details->sum('jumlah');
            $transactionSummary = trim(($firstDetail?->produk?->nama ?? 'Pesanan Produk') . ($totalUnit > 0 ? ' | ' . $totalUnit . ' unit' : ''));
        } else {
            $parts = array_filter([
                $selectedOrder->service_jenis_apar,
                $selectedOrder->service_ukuran_apar,
                $selectedOrder->service_jumlah_unit ? $selectedOrder->service_jumlah_unit . ' unit' : null,
            ]);
            $transactionSummary = count($parts) ? implode(' | ', $parts) : 'Layanan APAR';
        }
    }
@endphp

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="feedback-card">
            <div class="feedback-card-head">
                <p class="feedback-kicker">Form Bantuan</p>
                <h2 class="feedback-card-title">Sampaikan Kendala Anda</h2>
                <p class="feedback-card-subtitle">Tuliskan kendala Anda secara jelas agar tim admin lebih mudah mengecek transaksi dan menindaklanjuti lewat WhatsApp.</p>
            </div>

            @if($selectedOrder)
                <div class="feedback-transaction">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                        <div>
                            <span class="feedback-status-badge {{ $statusClass }}">{{ $statusText }}</span>
                            <div class="mt-3">
                                <div class="feedback-meta-label">Ringkasan Transaksi</div>
                                <div class="feedback-meta-value">{{ $transactionSummary }}</div>
                            </div>
                        </div>
                        <div class="text-md-end">
                            <div class="feedback-meta-label">Referensi</div>
                            <div class="feedback-meta-value">{{ $selectedOrder->orderCode() }}</div>
                        </div>
                    </div>

                    <div class="feedback-transaction-grid">
                        <div class="feedback-meta-item">
                            <span class="feedback-meta-label">Jenis Transaksi</span>
                            <div class="feedback-meta-value">{{ $selectedOrder->transactionDisplayName() }}</div>
                        </div>
                        <div class="feedback-meta-item">
                            <span class="feedback-meta-label">Tanggal Transaksi</span>
                            <div class="feedback-meta-value">{{ $selectedOrder->displayTransactionDateTime() }}</div>
                        </div>
                        <div class="feedback-meta-item">
                            <span class="feedback-meta-label">Metode Penanganan</span>
                            <div class="feedback-meta-value feedback-meta-value--muted">{{ $selectedOrder->trackingMethodLabel() }}</div>
                        </div>
                        <div class="feedback-meta-item">
                            <span class="feedback-meta-label">Metode Pembayaran</span>
                            <div class="feedback-meta-value feedback-meta-value--muted">{{ $selectedOrder->getPaymentMethodLabel() }}</div>
                        </div>
                    </div>
                </div>
            @endif

            @if($existingComplain)
                <div class="feedback-alert feedback-alert--info">
                    <div class="fw-bold mb-1">Komplain untuk transaksi ini sudah pernah dikirim.</div>
                    <div class="small">Status saat ini: {{ ucfirst($existingComplain->status_penyelesaian) }}. Admin akan menindaklanjuti lewat WhatsApp yang terdaftar.</div>
                </div>
            @endif

            <form action="{{ route('complain.store') }}" method="POST" data-feedback-form>
                @csrf

                @if($selectedOrder)
                    <input type="hidden" name="pesanan_id" value="{{ $selectedOrder->id }}">
                @endif

                <div class="feedback-form-group">
                    <label class="feedback-label" for="no_wa">
                        Nomor WhatsApp
                        <span class="feedback-required">*</span>
                    </label>
                    <input
                        id="no_wa"
                        type="text"
                        name="no_wa"
                        class="feedback-input"
                        value="{{ old('no_wa', $pelanggan->no_wa ?? '') }}"
                        {{ $pelanggan ? 'readonly' : 'required' }}
                        placeholder="Contoh: 08123456789">
                    <div class="feedback-field-note">Komplain akan ditindaklanjuti admin melalui nomor WhatsApp akun pelanggan yang sedang login.</div>
                    @error('no_wa')
                        <span class="feedback-invalid">{{ $message }}</span>
                    @enderror
                </div>

                @if(!$selectedOrder)
                    <div class="feedback-form-group">
                        <label class="feedback-label" for="pesanan_id">Referensi Transaksi (Opsional)</label>
                        <input
                            id="pesanan_id"
                            type="number"
                            name="pesanan_id"
                            class="feedback-input"
                            value="{{ old('pesanan_id', request('pesanan')) }}"
                            placeholder="Contoh: 12">
                        <div class="feedback-field-note">Isi jika komplain terkait transaksi tertentu agar admin lebih cepat mengecek riwayatnya.</div>
                    </div>
                @endif

                <div class="feedback-form-group">
                    <label class="feedback-label" for="isi_complain">
                        Isi Keluhan / Komplain
                        <span class="feedback-required">*</span>
                    </label>
                    <textarea
                        id="isi_complain"
                        name="isi_complain"
                        class="feedback-textarea"
                        rows="6"
                        required
                        placeholder="Ceritakan kendala yang Anda alami, misalnya hasil service belum sesuai, jadwal berubah, atau unit belum diterima.">{{ old('isi_complain') }}</textarea>
                    @error('isi_complain')
                        <span class="feedback-invalid">{{ $message }}</span>
                    @enderror
                </div>

                <div class="feedback-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Informasi Tindak Lanjut</strong>
                        <span>Komplain akan diterima admin terlebih dahulu, lalu ditindaklanjuti melalui WhatsApp agar proses penanganan lebih cepat dan jelas.</span>
                    </div>
                </div>

                <div class="mt-4">
                    <button
                        type="submit"
                        class="feedback-submit"
                        data-submit-button
                        data-loading-text="Mengirim Komplain..."
                        {{ $existingComplain ? 'disabled' : '' }}>
                        @if($existingComplain)
                            Komplain Sudah Terkirim
                        @else
                            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Komplain
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
