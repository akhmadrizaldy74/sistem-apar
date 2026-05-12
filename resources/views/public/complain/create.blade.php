@extends('layouts.bootstrap')

@section('title', 'Kirim Komplain')
@section('header_title', 'Layanan Komplain')
@section('header_subtitle', 'Sampaikan keluhan Anda, kami siap membantu.')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card p-4">
            <h4 class="mb-4 fw-bold">Formulir Komplain</h4>
            <form action="{{ route('complain.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor WhatsApp Anda <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" value="{{ old('no_wa') }}" required placeholder="Contoh: 08123456789">
                    <small class="text-muted">Gunakan nomor WA yang sudah pernah melakukan transaksi.</small>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ID Pesanan (Opsional)</label>
                    <input type="number" name="pesanan_id" class="form-control" value="{{ old('pesanan_id', request('pesanan')) }}" placeholder="Contoh: 12">
                    <small class="text-muted">Kosongkan jika Anda tidak mengetahui ID Pesanan.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Isi Keluhan / Komplain <span class="text-danger">*</span></label>
                    <textarea name="isi_complain" class="form-control @error('isi_complain') is-invalid @enderror" rows="5" required placeholder="Ceritakan kendala yang Anda alami...">{{ old('isi_complain') }}</textarea>
                    @error('isi_complain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm">Kirim Komplain <i class="fa-solid fa-paper-plane ms-2"></i></button>
            </form>
        </div>
    </div>
</div>
@endsection
