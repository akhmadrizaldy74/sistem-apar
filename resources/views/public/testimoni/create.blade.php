@extends('layouts.bootstrap')

@section('title', 'Testimoni Pelanggan - PD. ANUGRAH UTAMA')
@section('hero_badge', 'Testimoni Pelanggan')
@section('header_title', 'Testimoni Pelanggan')
@section('header_subtitle', 'Bagikan pengalaman Anda menggunakan layanan kami.')

@section('content')
@php
    $selectedOrder = $selectedOrder ?? null;
    $existingReview = $existingReview ?? null;
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
                $selectedOrder->service_jenis_layanan === 'refill'
                    ? ($selectedOrder->serviceJenisRefill?->nama_label ?: 'Refill APAR')
                    : ($selectedOrder->servicePaket?->nama ?: 'Service APAR'),
                $selectedOrder->service_jenis_apar,
                $selectedOrder->service_ukuran_apar,
                $selectedOrder->service_jumlah_unit ? $selectedOrder->service_jumlah_unit . ' unit' : null,
            ]);
            $transactionSummary = count($parts) ? implode(' | ', $parts) : 'Layanan APAR';
        }
    }

    $selectedRating = (int) old('rating', $existingReview->rating ?? 5);
    $ratingLabels = [
        1 => 'Sangat Buruk',
        2 => 'Kurang Puas',
        3 => 'Cukup',
        4 => 'Puas',
        5 => 'Sangat Puas',
    ];
@endphp

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="feedback-card">
            <div class="feedback-card-head">
                <p class="feedback-kicker">Form Penilaian</p>
                <h2 class="feedback-card-title">Bagikan Pengalaman Anda</h2>
                <p class="feedback-card-subtitle">Ceritakan kesan Anda terhadap kualitas produk dan layanan kami agar pelanggan lain lebih mudah memahami pengalaman nyata bersama kami.</p>
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

            @if($existingReview)
                <div class="feedback-alert feedback-alert--warn">
                    <div class="fw-bold mb-1">Transaksi ini sudah pernah Anda nilai.</div>
                    <div class="small">Status review: {{ strtoupper($existingReview->status) }} - Rating {{ $existingReview->rating }}/5. Jika ada kendala baru, gunakan menu komplain agar admin bisa menindaklanjuti lebih cepat.</div>
                </div>
            @endif

            <form action="{{ route('testimoni.store') }}" method="POST" data-feedback-form>
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
                    <div class="feedback-field-note">Nomor ini dipakai admin jika perlu menghubungi Anda terkait testimoni yang dikirim.</div>
                    @error('no_wa')
                        <span class="feedback-invalid">{{ $message }}</span>
                    @enderror
                </div>

                <div class="feedback-form-group">
                    <label class="feedback-label">
                        Rating Kepuasan
                        <span class="feedback-required">*</span>
                    </label>
                    <div class="feedback-rating">
                        @foreach($ratingLabels as $value => $label)
                            <label class="feedback-rating-option {{ $selectedRating === $value ? 'is-active' : '' }}" data-rating-option="{{ $value }}">
                                <input type="radio" name="rating" value="{{ $value }}" {{ $selectedRating === $value ? 'checked' : '' }} required>
                                <span class="feedback-rating-label">
                                    <span class="feedback-rating-stars" aria-hidden="true">
                                        @for($star = 1; $star <= 5; $star++)
                                            <i class="fa-solid fa-star {{ $star > $value ? 'opacity-25' : '' }}"></i>
                                        @endfor
                                    </span>
                                    <span class="feedback-rating-number">{{ $value }}/5</span>
                                    <span class="feedback-rating-text">{{ $label }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="feedback-rating-hint" id="rating-hint"></div>
                    @error('rating')
                        <span class="feedback-invalid">{{ $message }}</span>
                    @enderror
                </div>

                <div class="feedback-form-group">
                    <label class="feedback-label" for="review">
                        Ulasan Anda
                        <span class="feedback-required">*</span>
                    </label>
                    <textarea
                        id="review"
                        name="review"
                        class="feedback-textarea"
                        rows="6"
                        required
                        maxlength="1000"
                        placeholder="Ceritakan pengalaman Anda terhadap kualitas APAR dan pelayanan kami.">{{ old('review') }}</textarea>
                    @error('review')
                        <span class="feedback-invalid">{{ $message }}</span>
                    @enderror
                    <div class="feedback-counter" id="review-counter">0 karakter</div>
                </div>

                <div class="feedback-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Informasi Penayangan</strong>
                        <span>Testimoni akan ditinjau admin terlebih dahulu sebelum tampil di publik. Jika Anda memiliki kendala serius atau butuh tindak lanjut cepat, silakan gunakan menu komplain.</span>
                    </div>
                </div>

                <div class="mt-4">
                    <button
                        type="submit"
                        class="feedback-submit"
                        data-submit-button
                        data-loading-text="Mengirim Testimoni..."
                        {{ $existingReview ? 'disabled' : '' }}>
                        @if($existingReview)
                            Penilaian Sudah Terkirim
                        @else
                            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Testimoni
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ratingOptions = document.querySelectorAll('[data-rating-option]');
        const ratingHint = document.getElementById('rating-hint');
        const review = document.getElementById('review');
        const reviewCounter = document.getElementById('review-counter');

        const ratingMessages = {
            1: 'Kami turut menyesal jika pengalaman Anda belum sesuai harapan. Jelaskan juga kendala detailnya agar kami bisa mengevaluasi.',
            2: 'Terima kasih atas masukannya. Jika ada masalah serius, gunakan juga menu komplain agar admin bisa follow up lebih cepat.',
            3: 'Terima kasih. Ceritakan bagian yang menurut Anda masih bisa kami tingkatkan.',
            4: 'Senang mendengar layanan kami cukup memuaskan. Ulasan Anda akan sangat membantu pelanggan lain.',
            5: 'Terima kasih atas apresiasinya. Testimoni Anda akan ditinjau admin sebelum tampil di publik.'
        };

        function applyRating(value) {
            ratingOptions.forEach(function (option) {
                option.classList.toggle('is-active', Number(option.dataset.ratingOption) === Number(value));
            });

            if (ratingHint) {
                ratingHint.textContent = ratingMessages[value] || '';
            }
        }

        ratingOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                const input = option.querySelector('input[type="radio"]');
                if (!input) {
                    return;
                }

                input.checked = true;
                applyRating(input.value);
            });
        });

        const checkedRating = document.querySelector('input[name="rating"]:checked');
        applyRating(checkedRating ? checkedRating.value : 5);

        if (review && reviewCounter) {
            const syncCounter = function () {
                reviewCounter.textContent = review.value.length + ' karakter';
            };

            review.addEventListener('input', syncCounter);
            syncCounter();
        }
    });
</script>
@endpush
