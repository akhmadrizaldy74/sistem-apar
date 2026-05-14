@extends('layouts.bootstrap')

@section('title', 'Kirim Komplain')
@section('header_title', 'Layanan Komplain')
@section('header_subtitle', 'Sampaikan keluhan Anda, kami siap membantu.')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card p-4">
            <h4 class="mb-4 fw-bold">Formulir Komplain</h4>

            @if(isset($selectedOrder) && $selectedOrder)
                <div class="alert alert-warning border-0 rounded-4">
                    <div class="fw-bold mb-1">Komplain untuk transaksi {{ $selectedOrder->orderCode() }}</div>
                    <div class="small text-muted">{{ $selectedOrder->trackingItemLabel() }} • status {{ $selectedOrder->publicStatusLabel() }}</div>
                </div>
            @endif

            @if(isset($existingComplain) && $existingComplain)
                <div class="alert alert-info border-0 rounded-4">
                    <div class="fw-bold mb-1">Komplain untuk transaksi ini sudah pernah dikirim.</div>
                    <div class="small text-muted">Status saat ini: {{ ucfirst($existingComplain->status_penyelesaian) }}. Admin akan menindaklanjuti lewat WhatsApp.</div>
                </div>
            @endif

            <form action="{{ route('complain.store') }}" method="POST">
                @csrf

                @if(isset($selectedOrder) && $selectedOrder)
                    <input type="hidden" name="pesanan_id" value="{{ $selectedOrder->id }}">
                @endif
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor WhatsApp Anda <span class="text-danger">*</span></label>
                    <input type="text" name="no_wa" class="form-control @error('no_wa') is-invalid @enderror" value="{{ old('no_wa', $pelanggan->no_wa ?? '') }}" {{ isset($pelanggan) && $pelanggan ? 'readonly' : 'required' }} placeholder="Contoh: 08123456789">
                    <small class="text-muted">Komplain akan ditindaklanjuti admin melalui WhatsApp ini.</small>
                    @error('no_wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if(!isset($selectedOrder) || !$selectedOrder)
                    <div class="mb-3">
                        <label class="form-label fw-bold">ID Pesanan (Opsional)</label>
                        <input type="number" name="pesanan_id" class="form-control" value="{{ old('pesanan_id', request('pesanan')) }}" placeholder="Contoh: 12">
                        <small class="text-muted">Isi jika komplain terkait transaksi tertentu agar admin lebih cepat follow up.</small>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="form-label fw-bold">Isi Keluhan / Komplain <span class="text-danger">*</span></label>
                    <textarea name="isi_complain" class="form-control @error('isi_complain') is-invalid @enderror" rows="5" required placeholder="Ceritakan kendala yang Anda alami, misalnya unit belum diambil, jadwal mundur, atau hasil service belum sesuai...">{{ old('isi_complain') }}</textarea>
                    @error('isi_complain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm" {{ isset($existingComplain) && $existingComplain ? 'disabled' : '' }}>
                    {{ isset($existingComplain) && $existingComplain ? 'Komplain Sudah Terkirim' : 'Kirim Komplain' }}
                    <i class="fa-solid fa-paper-plane ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
