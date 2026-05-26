@extends('layouts.bootstrap')

@section('title', 'Kirim Testimoni')
@section('header_title', 'Testimoni Pelanggan')
@section('header_subtitle', 'Bagikan pengalaman Anda menggunakan layanan kami.')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card p-4">
            <h4 class="mb-4 fw-bold">Formulir Testimoni</h4>

            @if(isset($selectedOrder) && $selectedOrder)
                <div class="alert alert-info border-0 rounded-4">
                    <div class="fw-bold mb-1">{{ $selectedOrder->transactionDisplayName() }}</div>
                    <div class="small text-muted">{{ $selectedOrder->displayTransactionDateTime() }} • status {{ $selectedOrder->publicStatusLabel() }}</div>
                </div>
            @endif

            @if(isset($existingReview) && $existingReview)
                <div class="alert alert-warning border-0 rounded-4">
                    <div class="fw-bold mb-1">Transaksi ini sudah pernah Anda nilai.</div>
                    <div class="small text-muted">Status review: {{ strtoupper($existingReview->status) }} • Rating {{ $existingReview->rating }}/5</div>
                </div>
            @endif

            <form action="{{ route('testimoni.store') }}" method="POST">
                @csrf

                @if(isset($selectedOrder) && $selectedOrder)
                    <input type="hidden" name="pesanan_id" value="{{ $selectedOrder->id }}">
                @endif

                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor WhatsApp Anda <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" value="{{ old('no_wa', $pelanggan->no_wa ?? '') }}" {{ isset($pelanggan) && $pelanggan ? 'readonly' : 'required' }} placeholder="Contoh: 08123456789">
                    <small class="text-muted">Admin bisa membalas testimoni Anda seperti model review marketplace.</small>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Rating / Nilai Kepuasan <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 fs-3 text-warning rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer" for="rating{{ $i }}">
                                <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" class="d-none" required {{ $i == 5 ? 'checked' : '' }}>
                                <i class="fa-regular fa-star" id="star{{ $i }}"></i>
                            </label>
                        @endfor
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Ulasan Anda <span class="text-danger">*</span></label>
                    <textarea name="review" class="form-control @error('review') is-invalid @enderror" rows="5" required placeholder="Bagaimana menurut Anda kualitas APAR & pelayanan kami?">{{ old('review') }}</textarea>
                    @error('review') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="mt-2 d-block text-muted">Jika ada kendala serius, tetap gunakan menu komplain agar admin follow up lewat WhatsApp.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm" {{ isset($existingReview) && $existingReview ? 'disabled' : '' }}>
                    {{ isset($existingReview) && $existingReview ? 'Penilaian Sudah Terkirim' : 'Kirim Testimoni' }}
                    <i class="fa-solid fa-paper-plane ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .cursor-pointer { cursor: pointer; }
    .fa-solid.fa-star { color: #ffc107; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = [];
        const reviewHint = document.createElement('div');
        reviewHint.className = 'small mt-2';
        document.querySelector('.rating-stars').parentElement.appendChild(reviewHint);

        for (let i = 1; i <= 5; i++) {
            stars.push(document.getElementById('star' + i));
        }

        function updateStars(val) {
            for (let i = 0; i < 5; i++) {
                if (i < val) {
                    stars[i].classList.remove('fa-regular');
                    stars[i].classList.add('fa-solid');
                } else {
                    stars[i].classList.remove('fa-solid');
                    stars[i].classList.add('fa-regular');
                }
            }

            if (Number(val) <= 3) {
                reviewHint.className = 'small mt-2 text-danger';
                reviewHint.textContent = 'Kalau ada kendala, admin tetap bisa membalas review Anda. Untuk penanganan lebih cepat, gunakan juga fitur komplain.';
            } else {
                reviewHint.className = 'small mt-2 text-muted';
                reviewHint.textContent = 'Review positif Anda akan direview admin terlebih dahulu sebelum tampil ke publik.';
            }
        }

        updateStars(5);

        document.querySelectorAll('.rating-stars input').forEach((radio) => {
            radio.addEventListener('change', function() {
                updateStars(this.value);
            });
        });
    });
</script>
@endpush
