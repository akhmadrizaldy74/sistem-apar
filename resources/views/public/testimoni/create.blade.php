@extends('layouts.bootstrap')

@section('title', 'Kirim Testimoni')
@section('header_title', 'Testimoni Pelanggan')
@section('header_subtitle', 'Bagikan pengalaman Anda menggunakan layanan kami.')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card p-4">
            <h4 class="mb-4 fw-bold">Formulir Testimoni</h4>
            <form action="{{ route('testimoni.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor WhatsApp Anda <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" value="{{ old('no_wa') }}" required placeholder="Contoh: 08123456789">
                    <small class="text-muted">Gunakan nomor WA yang terdaftar di pesanan.</small>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Rating / Nilai Kepuasan <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 fs-3 text-warning rating-stars">
                        @for($i=1; $i<=5; $i++)
                            <label class="cursor-pointer" for="rating{{$i}}">
                                <input type="radio" name="rating" id="rating{{$i}}" value="{{$i}}" class="d-none" required {{$i==5 ? 'checked' : ''}}>
                                <i class="fa-regular fa-star" id="star{{$i}}"></i>
                            </label>
                        @endfor
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Ulasan Anda <span class="text-danger">*</span></label>
                    <textarea name="review" class="form-control @error('review') is-invalid @enderror" rows="5" required placeholder="Bagaimana menurut Anda kualitas APAR & pelayanan kami?">{{ old('review') }}</textarea>
                    @error('review') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm">Kirim Testimoni <i class="fa-solid fa-paper-plane ms-2"></i></button>
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
        for(let i=1; i<=5; i++) {
            stars.push(document.getElementById('star'+i));
        }
        
        function updateStars(val) {
            for(let i=0; i<5; i++) {
                if(i < val) {
                    stars[i].classList.remove('fa-regular');
                    stars[i].classList.add('fa-solid');
                } else {
                    stars[i].classList.remove('fa-solid');
                    stars[i].classList.add('fa-regular');
                }
            }
        }
        
        // Initial set based on checked radio
        updateStars(5);

        document.querySelectorAll('.rating-stars input').forEach((radio) => {
            radio.addEventListener('change', function() {
                updateStars(this.value);
            });
        });
    });
</script>
@endpush
