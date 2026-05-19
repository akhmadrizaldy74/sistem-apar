@extends('layouts.public')

@section('title', 'Form Pemesanan APAR - PD. Anugrah Utama')

@section('styles')
<style>
    .order-section-card{background:#fff;border:1px solid #f1f5f9;border-radius:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,0.04),0 4px 16px rgba(0,0,0,0.04);overflow:hidden;transition:box-shadow .3s ease}
    .order-section-card:hover{box-shadow:0 2px 8px rgba(0,0,0,0.06),0 8px 32px rgba(0,0,0,0.06)}
    .order-input{width:100%;padding:.625rem .875rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.875rem;font-size:.875rem;font-weight:600;color:#1e293b;transition:all .2s ease;outline:none}
    .order-input:focus{border-color:#dc2626;background:#fff;box-shadow:0 0 0 3px rgba(220,38,38,0.08)}
    .order-input::placeholder{color:#94a3b8;font-weight:500}
    .order-label{display:block;font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:.4rem}
    .order-label span{color:#dc2626}
    .step-pill{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;background:#fef2f2;border:1px solid #fecaca;border-radius:9999px;font-size:.65rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#dc2626}
    .step-dot{width:6px;height:6px;border-radius:50%;background:#dc2626;animation:pulse-dot 2s infinite}
    @keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.4}}
    .section-icon-wrap{width:2.5rem;height:2.5rem;border-radius:.875rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .layanan-card{border:2px solid #e2e8f0;border-radius:1.25rem;padding:1rem 1.25rem;background:#fff;cursor:pointer;transition:all .2s ease;display:flex;align-items:center;gap:.875rem;text-align:left;width:100%}
    .layanan-card:hover{border-color:#cbd5e1;background:#f8fafc}
    .layanan-card.active-beli{border-color:#dc2626;background:linear-gradient(135deg,#fef2f2 0%,#fee2e2 100%)}
    .layanan-card.active-service{border-color:#2563eb;background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%)}
    .layanan-icon{width:3rem;height:3rem;border-radius:.875rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .2s ease}
    .layanan-icon.beli{background:#fee2e2;color:#dc2626}
    .layanan-icon.beli.active{background:#dc2626;color:#fff}
    .layanan-icon.service{background:#dbeafe;color:#2563eb}
    .layanan-icon.service.active{background:#2563eb;color:#fff}
    .summary-card{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:1.25rem;padding:1.25rem}
    .summary-row{display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;font-size:.875rem}
    .summary-row.total{border-top:1.5px dashed #cbd5e1;padding-top:.75rem;margin-top:.25rem}
    .nego-toggle{display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;background:#fffbeb;border:1.5px solid #fde68a;border-radius:.875rem;cursor:pointer;transition:all .2s ease;width:100%}
    .nego-toggle:hover{background:#fef3c7}
    .nego-toggle.active{background:#fffbeb;border-color:#d97706;box-shadow:0 0 0 3px rgba(217,119,6,0.1)}
    .nego-box{background:#fffbeb;border:1.5px solid #fde68a;border-radius:.875rem;padding:1rem}
    .deal-box{background:linear-gradient(135deg,#ecfdf5 0%,#d1fae5 100%);border:1.5px solid #6ee7b7;border-radius:.875rem;padding:.875rem 1rem}
    .kode-nego-input{flex:1;padding:.625rem .875rem;background:#fff;border:1.5px solid #e2e8f0;border-radius:.75rem;font-size:.875rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#1e293b;outline:none;transition:all .2s ease}
    .kode-nego-input:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,0.1)}
    .btn-nego-check{padding:.625rem 1.25rem;background:#10b981;color:#fff;border-radius:.75rem;font-size:.8rem;font-weight:800;border:none;cursor:pointer;transition:all .2s ease;white-space:nowrap}
    .btn-nego-check:hover{background:#059669}
    .btn-nego-check:disabled{opacity:.5;cursor:not-allowed}
    .shipping-btn{flex:1;padding:.625rem;border-radius:.75rem;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;border:2px solid;cursor:pointer;transition:all .2s ease;text-align:center}
    .shipping-btn.pickup{border-color:#94a3b8;background:#fff;color:#475569}
    .shipping-btn.pickup.active{border-color:#1e293b;background:#1e293b;color:#fff}
    .shipping-btn.diantar{border-color:#10b981;background:#fff;color:#059669}
    .shipping-btn.diantar.active{border-color:#10b981;background:#10b981;color:#fff}
    .address-suggestion-item{display:block;width:100%;text-align:left;padding:.7rem .85rem;border-bottom:1px solid #f1f5f9;background:#fff;transition:all .2s ease}
    .address-suggestion-item:last-child{border-bottom:0}
    .address-suggestion-item:hover{background:#fef2f2}
    .address-suggestion-title{display:block;font-size:.9rem;font-weight:700;color:#0f172a;line-height:1.35}
    .address-suggestion-subtitle{display:block;font-size:.72rem;font-weight:600;color:#64748b;line-height:1.35;margin-top:.15rem}
    .bank-option{width:100%;padding:1rem;border-radius:1rem;border:1.5px solid #e2e8f0;background:#fff;text-align:left;transition:all .2s ease;cursor:pointer}
    .bank-option:hover{border-color:#cbd5e1;background:#f8fafc}
    .bank-option.active{border-color:#dc2626;background:linear-gradient(135deg,#fff1f2 0%,#ffe4e6 100%);box-shadow:0 0 0 3px rgba(220,38,38,0.08)}
    .bank-option-label{display:flex;align-items:center;justify-content:space-between;gap:.75rem}
    .bank-option-dot{width:1rem;height:1rem;border-radius:9999px;border:2px solid #cbd5e1;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
    .bank-option.active .bank-option-dot{border-color:#dc2626}
    .bank-option-dot::after{content:'';width:.4rem;height:.4rem;border-radius:9999px;background:#dc2626;opacity:0;transition:opacity .2s ease}
    .bank-option.active .bank-option-dot::after{opacity:1}
    .btn-primary-action{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;border-radius:1rem;font-size:.875rem;font-weight:800;border:none;cursor:pointer;transition:all .2s ease}
    .btn-primary-action.wa{background:#16a34a;color:#fff;box-shadow:0 4px 14px rgba(22,163,74,0.35)}
    .btn-primary-action.wa:hover{background:#15803d;transform:translateY(-1px);box-shadow:0 6px 20px rgba(22,163,74,0.4)}
    .btn-primary-action.submit{background:#1e293b;color:#fff;box-shadow:0 4px 14px rgba(30,41,59,0.3)}
    .btn-primary-action.submit:hover{background:#0f172a;transform:translateY(-1px)}
    .btn-primary-action.submit.deal{background:#059669;color:#fff;box-shadow:0 4px 14px rgba(5,150,105,0.4)}
    .btn-primary-action.submit.deal:hover{background:#047857}
    .btn-primary-action.submit.nego{background:#d97706;color:#fff;box-shadow:0 4px 14px rgba(217,119,6,0.4)}
    .btn-primary-action.submit.nego:hover{background:#b45309}
    .btn-primary-action.submit.service{background:#dc2626;color:#fff;box-shadow:0 4px 14px rgba(220,38,38,0.35)}
    .btn-primary-action.submit.service:hover{background:#b91c1c}
    .btn-add-item{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#fef2f2;border:1.5px solid #fecaca;border-radius:.75rem;color:#dc2626;font-size:.8rem;font-weight:800;cursor:pointer;transition:all .2s ease}
    .btn-add-item:hover{background:#dc2626;color:#fff;border-color:#dc2626}
    .invoice-header{display:grid;grid-template-columns:3fr 2fr 2fr 2fr 1fr 2fr;gap:.5rem;padding:.5rem .75rem;background:#f8fafc;border-radius:.75rem;margin-bottom:.5rem}
    @media(max-width:768px){.invoice-header{display:none}}
    .item-card{border:1.5px solid #f1f5f9;border-radius:1rem;padding:1rem;background:#fff;margin-bottom:.75rem;transition:border-color .2s ease}
    .item-card:hover{border-color:#e2e8f0}
    .item-card select,.item-card input{width:100%;padding:.5rem .75rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.625rem;font-size:.8rem;font-weight:600;color:#334155;outline:none;transition:all .2s ease}
    .item-card select:focus,.item-card input:focus{border-color:#dc2626;background:#fff}
    .item-card select:disabled,.item-card input:disabled{background:#f1f5f9;color:#94a3b8;border-color:#e2e8f0}
    .map-container{border-radius:1rem;overflow:hidden;border:1.5px solid #e2e8f0}
    .coord-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .625rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9999px;font-size:.7rem;font-weight:700;color:#16a34a}
    .map-hint{display:inline-flex;align-items:center;gap:.375rem;padding:.3rem .75rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:9999px;font-size:.7rem;font-weight:700;color:#2563eb}
    .btn-delete-item{width:2rem;height:2rem;border-radius:.625rem;background:#fef2f2;border:1.5px solid #fecaca;color:#dc2626;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s ease;flex-shrink:0}
    .btn-delete-item:hover{background:#dc2626;color:#fff;border-color:#dc2626}
    .btn-delete-item:disabled{opacity:.3;cursor:not-allowed}
    .error-msg{background:#fef2f2;border:1.5px solid #fecaca;border-radius:.75rem;padding:.625rem .875rem;font-size:.8rem;font-weight:700;color:#dc2626;margin-top:.5rem}
    .success-msg{background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:.75rem;padding:.625rem .875rem;font-size:.8rem;font-weight:700;color:#16a34a;margin-top:.5rem}
    .empty-items{text-align:center;padding:2rem;color:#94a3b8;font-size:.875rem;font-weight:600}
    .flow-step-card{position:relative;padding:1.15rem;border:1.5px solid #e2e8f0;border-radius:1.25rem;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .flow-step-number{width:2rem;height:2rem;border-radius:.875rem;display:flex;align-items:center;justify-content:center;background:#dc2626;color:#fff;font-size:.85rem;font-weight:900;box-shadow:0 10px 24px rgba(220,38,38,.2)}
    .flow-cta{display:inline-flex;align-items:center;justify-content:center;gap:.55rem;padding:.9rem 1.25rem;border-radius:1rem;font-size:.82rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase;transition:all .2s ease}
    .flow-cta.primary{background:#dc2626;color:#fff;box-shadow:0 12px 28px rgba(220,38,38,.22)}
    .flow-cta.primary:hover{background:#b91c1c;transform:translateY(-1px)}
    .flow-cta.secondary{background:#fff;border:1.5px solid #cbd5e1;color:#0f172a}
    .flow-cta.secondary:hover{background:#f8fafc;border-color:#94a3b8}
    .cart-preview-item{display:flex;align-items:center;gap:.9rem;padding:.85rem 0;border-bottom:1px solid #e2e8f0}
    .cart-preview-item:last-child{border-bottom:0;padding-bottom:0}
    .cart-preview-thumb{width:4.25rem;height:4.25rem;border-radius:1rem;overflow:hidden;background:#f1f5f9;flex-shrink:0}
    .cart-preview-thumb img{width:100%;height:100%;object-fit:cover}
    .preview-note{margin-top:.8rem;padding:.75rem .9rem;border:1px solid #dbeafe;border-radius:1rem;background:#eff6ff;font-size:.76rem;font-weight:700;line-height:1.45;color:#1d4ed8}
    .preview-pill-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
    .preview-pill{display:flex;align-items:center;justify-content:center;padding:.85rem 1rem;border-radius:1rem;border:1.5px solid #cbd5e1;background:#fff;color:#334155;font-size:.78rem;font-weight:900;letter-spacing:.05em;text-transform:uppercase}
    .preview-pill.active{background:#1e293b;border-color:#1e293b;color:#fff;box-shadow:0 10px 24px rgba(30,41,59,.18)}
    .preview-pill.soft-green{border-color:#86efac;background:#f0fdf4;color:#15803d}
    .preview-bank-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
    .preview-bank-card{padding:.95rem .8rem;border-radius:1.1rem;border:1.5px solid #dbe3ee;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .preview-bank-card p:first-child{font-size:.58rem;font-weight:900;letter-spacing:.16em;text-transform:uppercase;color:#94a3b8}
    .preview-bank-card p:last-child{margin-top:.35rem;font-size:.95rem;font-weight:900;color:#0f172a}
    .preview-product-mini{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.75rem 0;border-bottom:1px solid #e2e8f0}
    .preview-product-mini:last-child{border-bottom:0;padding-bottom:0}
    @media(max-width:640px){.btn-primary-action{padding:.75rem 1rem;font-size:.8rem}}
    #section-service-inline.hidden{display:none!important}
</style>
@endsection

@section('content')

<script>
    const PRODUK_DB = {!! json_encode($produks->load('jenisApar')) !!};
    const CHECK_NEGO_CODE_URL = '{{ route('order.check-nego-code') }}';
    const ASK_WHATSAPP_URL = '{{ route('order.ask-whatsapp') }}';
    const SHIPPING_QUOTE_URL = '{{ route('order.shipping.quote') }}';
    const ADDRESS_SUGGEST_URL = '{{ route('order.address.suggest') }}';
    const WA_NUMBER = '{{ preg_replace("/^0/", "62", env("WHATSAPP_CONTACT", "082124716109")) }}';
    const PRODUCT_PAGE_URL = '{{ route('produk.index') }}';
    const LOGIN_PAGE_URL = '{{ route('login') }}';
    const CHECKOUT_PAGE_URL = '{{ route('order.create') }}';
    const CART_PAGE_URL = '{{ route('keranjang.index') }}';
    const CART_ORDER_ITEMS = {!! json_encode(($cartItems ?? collect())->map(function ($item) {
        return [
            'produk_id' => (int) $item->produk_id,
            'jumlah' => (int) $item->qty,
            'harga' => (float) $item->harga,
            'nama' => (string) ($item->produk?->nama ?? 'Produk'),
            'jenis' => (string) ($item->produk?->jenisApar?->nama ?? 'APAR'),
            'kapasitas' => (string) ($item->produk?->kapasitas ?? '-'),
            'merek' => (string) ($item->produk?->merek ?? 'SAFETY'),
        ];
    })->values()) !!};
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const STORE_LAT = {{ (float) env('STORE_LAT', -6.494778) }};
    const STORE_LNG = {{ (float) env('STORE_LNG', 106.816635) }};
    const IS_AUTHENTICATED = {{ auth()->check() ? 'true' : 'false' }};
    const USE_AUTHENTICATED_CUSTOMER = {{ !empty($useAuthenticatedCustomer) ? 'true' : 'false' }};
    const CAN_USE_CART_CHECKOUT = {{ !empty($canUseCartCheckout) ? 'true' : 'false' }};
    const CART_HAS_ITEMS = {{ !empty($cartItemCount) ? 'true' : 'false' }};
    const PREFILLED_ORDER_ITEMS = {!! json_encode(($prefilledOrderItems ?? collect())->values()) !!};
    const USING_DIRECT_PRODUCT_SELECTION = {{ !empty($prefillFromProduct) ? 'true' : 'false' }};
    const JENIS_REFILL_DB = {!! json_encode(($jenisRefills ?? collect())->map(function ($jenisRefill) {
        return [
            'id' => (int) $jenisRefill->id,
            'nama' => (string) $jenisRefill->nama,
            'nama_label' => (string) $jenisRefill->nama_label,
            'stok' => (float) $jenisRefill->stok,
            'stok_minimum' => (float) $jenisRefill->stok_minimum,
            'harga' => (float) $jenisRefill->harga,
            'satuan_label' => (string) $jenisRefill->satuan_label,
            'service_price_rules' => collect($jenisRefill->service_price_rules_json ?? [])->map(function ($rule) {
                return [
                    'ukuran' => (string) ($rule['ukuran'] ?? ''),
                    'harga' => (float) ($rule['harga'] ?? 0),
                ];
            })->values(),
        ];
    })->values()) !!};
    const REGISTERED_UNIT_APAR_DB = {!! json_encode(($registeredUnitApars ?? collect())->map(function ($unitApar) {
        $unitApar->loadMissing('produk.jenisApar');
        $produk = $unitApar->produk;
        $jenisApar = (string) ($produk?->jenisApar?->nama ?: $unitApar->bahan ?: '');
        $ukuran = (string) ($unitApar->ukuran ?: $produk?->kapasitas ?: '');
        $kode = (string) ($unitApar->no_seri ?: 'UNIT-' . $unitApar->id);
        $produkNama = (string) ($produk?->nama ?: 'Produk APAR');
        $purchaseDate = $unitApar->tgl_beli ? $unitApar->tgl_beli->translatedFormat('d F Y') : 'Tanpa tanggal';
        $purchaseKey = $unitApar->tgl_beli ? $unitApar->tgl_beli->toDateString() : 'tanpa-tanggal';
        $purchaseLabel = $purchaseDate;
        $label = collect([$kode, $produkNama, $jenisApar, $ukuran])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->implode(' - ');

        return [
            'id' => (int) $unitApar->id,
            'kode' => $kode,
            'produk_nama' => $produkNama,
            'jenis_apar' => $jenisApar,
            'ukuran' => $ukuran,
            'tgl_beli' => $purchaseDate,
            'masa_berlaku' => $unitApar->tgl_expired ? $unitApar->tgl_expired->translatedFormat('d F Y') : '-',
            'status_unit' => (string) ($unitApar->kondisi_awal ?: '-'),
            'label' => $label,
            'purchase_key' => $purchaseKey,
            'purchase_label' => $purchaseLabel,
        ];
    })->values()) !!};
    const OLD_SELECTED_UNIT_APAR_IDS = {!! json_encode(collect(old('service_unit_apar_ids', old('service_unit_apar_id') ? [old('service_unit_apar_id')] : []))->map(fn ($id) => (int) $id)->filter()->values()) !!};
    const SERVICE_PAKET_DB = {!! json_encode(($servicePakets ?? collect())->map(function ($servicePaket) {
        return [
            'id' => (int) $servicePaket->id,
            'label' => (string) ($servicePaket->label ?? ''),
            'nama' => (string) $servicePaket->nama,
            'harga' => (float) $servicePaket->harga,
            'jenis_refill_id' => $servicePaket->jenis_refill_id ? (int) $servicePaket->jenis_refill_id : null,
            'refill_ratio' => (float) ($servicePaket->refill_ratio ?? 0),
            'rincian' => $servicePaket->rincian_list,
            'peralatans' => $servicePaket->peralatans->map(function ($peralatan) {
                return [
                    'nama' => (string) $peralatan->nama,
                    'jumlah' => (int) ($peralatan->pivot->jumlah_estimasi ?? 0),
                ];
            })->values(),
        ];
    })->values()) !!};
    const SERVICE_UKURAN_OPTIONS = {!! json_encode(array_values($serviceUkuranOptions ?? [])) !!};
</script>

<div class="bg-gradient-to-br from-slate-50 to-red-50/20 min-h-screen py-10">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        $profile = $customerProfile ?? null;
        $profileIncomplete = !empty($useAuthenticatedCustomer) && empty($profile['is_complete']);
        $cartItems = $cartItems ?? collect();
        $cartHasItems = $cartItems->isNotEmpty();
        $prefilledOrderItems = collect($prefilledOrderItems ?? []);
        $prefilledOrderTotal = (float) $prefilledOrderItems->sum(fn ($item) => ((float) ($item['harga'] ?? 0)) * ((int) ($item['jumlah'] ?? 0)));
        $prefilledOrderQty = (int) $prefilledOrderItems->sum(fn ($item) => (int) ($item['jumlah'] ?? 0));
    @endphp

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="step-pill mb-4">
            <span class="step-dot"></span>
            Form Pemesanan Online
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">Form Pemesanan APAR</h1>
        <p class="mt-2 text-slate-500 font-medium text-sm max-w-2xl mx-auto">Isi data pemesanan, cek ringkasan pesanan, lalu lanjutkan konfirmasi pembayaran sesuai alur pemesanan.</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-6 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="mb-6 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                @if(session('error'))<p class="font-bold text-sm">{{ session('error') }}</p>@endif
                @if($errors->any())
                    <p class="font-bold text-sm mb-1">Mohon periksa isian berikut:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('order.store') }}" id="order-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="inp-submit-source" name="submit_source" value="normal">
    <input type="hidden" id="inp-tipe-harga" name="tipe_harga" value="normal">
    <input type="hidden" id="inp-is-nego-deal" name="is_nego_deal" value="0">
    <input type="hidden" id="inp-harga-deal" name="harga_deal" value="">
    <input type="hidden" id="inp-metode-pengiriman" name="metode_pengiriman" value="pickup">
    <input type="hidden" id="inp-bank" name="bank" value="{{ old('bank') }}">
    <input type="hidden" id="inp-ongkir" name="ongkir" value="0">
    <input type="hidden" id="inp-shipping-distance" name="shipping_distance_km" value="0">
    <input type="hidden" id="inp-use-cart-checkout" name="use_cart_checkout" value="{{ !empty($canUseCartCheckout) && $cartHasItems ? '1' : '0' }}">

    <div id="section-beli" class="mb-5">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 items-start">
            <div class="lg:col-span-3 space-y-5">

    {{-- ════════════════════════════════════════
         STEP 1: INFORMASI PELANGGAN
    ════════════════════════════════════════ --}}
    <div class="order-section-card p-5 md:p-6" id="section-customer">
        <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100">
            <div class="section-icon-wrap bg-red-50 text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <h2 class="font-black text-slate-900 text-lg leading-none">Informasi Pelanggan</h2>
            </div>
        </div>
        @if(!empty($useAuthenticatedCustomer))
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600">Otomatis dari profil pelanggan</p>
                        <h3 class="mt-1 text-lg font-black text-slate-900">{{ $profile['nama'] ?: '-' }}</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-600">{{ $profile['no_wa'] ?: '-' }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-red-700 transition hover:bg-red-50">
                        Ubah di Profil
                    </a>
                </div>

                @if($profileIncomplete)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                        Alamat pelanggan di profil belum lengkap. Lengkapi dulu di halaman profil sebelum membuat pesanan.
                    </div>
                @endif

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <p class="order-label">Alamat Tersimpan</p>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                            {{ $profile['alamat_maps'] ?: '-' }}
                        </div>
                    </div>
                    <div>
                        <p class="order-label">Detail / Patokan</p>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                            {{ $profile['alamat_detail'] ?: '-' }}
                        </div>
                    </div>
                    <div>
                        <p class="order-label">Provinsi</p>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                            {{ $profile['alamat_provinsi'] ?: '-' }}
                        </div>
                    </div>
                    <div>
                        <p class="order-label">Kota / Kabupaten</p>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                            {{ $profile['alamat_kota'] ?: '-' }}
                        </div>
                    </div>
                    <div>
                        <p class="order-label">Kecamatan</p>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                            {{ $profile['alamat_kecamatan'] ?: '-' }}
                        </div>
                    </div>
                    <div>
                        <p class="order-label">Kode Pos</p>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                            {{ $profile['alamat_kode_pos'] ?: '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="nama" id="inp-nama" value="{{ old('nama', $profile['nama']) }}">
            <input type="hidden" name="no_wa" id="inp-nowa" value="{{ old('no_wa', $profile['no_wa']) }}">
            <input type="hidden" id="inp-perusahaan" value="{{ old('perusahaan', $profile['perusahaan']) }}">
            <input type="hidden" name="alamat_provinsi" id="inp-provinsi" value="{{ old('alamat_provinsi', $profile['alamat_provinsi']) }}">
            <input type="hidden" name="alamat_kota" id="inp-kota" value="{{ old('alamat_kota', $profile['alamat_kota']) }}">
            <input type="hidden" name="alamat_kecamatan" id="inp-kecamatan" value="{{ old('alamat_kecamatan', $profile['alamat_kecamatan']) }}">
            <input type="hidden" name="alamat_kode_pos" id="inp-kodepos" value="{{ old('alamat_kode_pos', $profile['alamat_kode_pos']) }}">
            <input type="hidden" id="inp-alamat-maps" name="alamat_maps" value="{{ old('alamat_maps', $profile['alamat_maps']) }}">
            <input type="hidden" id="inp-alamat-detail" name="alamat_detail" value="{{ old('alamat_detail', $profile['alamat_detail']) }}">
            <input type="hidden" name="alamat" id="inp-alamat-combined" value="{{ old('alamat') }}">
            <input type="hidden" name="alamat_lat" id="inp-alamat-lat" value="{{ old('alamat_lat', $profile['alamat_lat']) }}">
            <input type="hidden" name="alamat_lng" id="inp-alamat-lng" value="{{ old('alamat_lng', $profile['alamat_lng']) }}">
            <div id="address-suggestions" class="hidden"></div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nama --}}
                <div>
                    <label class="order-label">Nama Lengkap <span>*</span></label>
                    <input type="text" name="nama" id="inp-nama" value="{{ old('nama') }}" required
                        placeholder="Masukkan nama lengkap Anda"
                        class="order-input">
                </div>
                {{-- Perusahaan --}}
                <div>
                    <label class="order-label">Nama Perusahaan <span class="text-slate-300 font-normal normal-case tracking-normal">(Opsional)</span></label>
                    <input type="text" id="inp-perusahaan" placeholder="PT / CV / Instansi / Sekolah"
                        class="order-input">
                </div>
                {{-- WhatsApp --}}
                <div>
                    <label class="order-label">Nomor WhatsApp <span>*</span></label>
                    <input type="text" name="no_wa" id="inp-nowa" value="{{ old('no_wa') }}" required
                        placeholder="08xxxxxxxxxx"
                        class="order-input">
                </div>
                {{-- Provinsi --}}
                <div>
                    <label class="order-label">Provinsi</label>
                    <input type="text" name="alamat_provinsi" id="inp-provinsi" value="{{ old('alamat_provinsi') }}"
                        class="order-input" placeholder="Provinsi">
                </div>
                {{-- Kota / Kab --}}
                <div>
                    <label class="order-label">Kota / Kabupaten</label>
                    <input type="text" name="alamat_kota" id="inp-kota" value="{{ old('alamat_kota') }}"
                        class="order-input" placeholder="Kota / Kabupaten">
                </div>
                {{-- Kecamatan --}}
                <div>
                    <label class="order-label">Kecamatan</label>
                    <input type="text" name="alamat_kecamatan" id="inp-kecamatan" value="{{ old('alamat_kecamatan') }}"
                        class="order-input" placeholder="Kecamatan">
                </div>
                {{-- Kode Pos --}}
                <div>
                    <label class="order-label">Kode Pos</label>
                    <input type="text" name="alamat_kode_pos" id="inp-kodepos" value="{{ old('alamat_kode_pos') }}"
                        class="order-input" placeholder="Kode Pos">
                </div>
                {{-- Alamat Maps --}}
                <div class="relative md:col-span-2">
                    <label class="order-label">Alamat (Cari di OpenStreetMap) <span>*</span></label>
                    <input type="text" id="inp-alamat-maps" name="alamat_maps" value="{{ old('alamat_maps') }}" required
                        placeholder="Ketik alamat, pilih dari saran..."
                        class="order-input pr-10">
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-[38px] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <div id="address-suggestions" class="hidden mt-2 w-full max-h-56 overflow-auto rounded-xl border-2 border-red-500 bg-white shadow-xl"></div>
                </div>
            </div>

            <div class="mt-5" id="order-map-wrapper">
                    <label class="order-label">Konfirmasi Titik Lokasi (Geser pin atau klik peta untuk koreksi)</label>
                <div class="map-container">
                    <div id="order-map" style="height: 200px; width: 100%;"></div>
                </div>
                <div class="mt-2.5 flex flex-wrap items-center gap-3">
                    <div class="coord-badge">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Lat: <span id="order-map-lat" class="font-mono">-</span>
                    </div>
                    <div class="coord-badge">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Lng: <span id="order-map-lng" class="font-mono">-</span>
                    </div>
                    <div class="map-hint ml-auto">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Geser pin merah untuk koreksi
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label class="order-label">Detail Alamat / Patokan <span>*</span></label>
                <textarea id="inp-alamat-detail" name="alamat_detail" required rows="2"
                    placeholder="Contoh: Blok A2 No.10, patokan dekat minimarket, lantai 2, warna bangunan merah"
                    class="order-input resize-none">{{ old('alamat_detail') }}</textarea>
            </div>

            <input type="hidden" name="alamat" id="inp-alamat-combined" value="{{ old('alamat') }}">
            <input type="hidden" name="alamat_lat" id="inp-alamat-lat" value="{{ old('alamat_lat') }}">
            <input type="hidden" name="alamat_lng" id="inp-alamat-lng" value="{{ old('alamat_lng') }}">
        @endif
    </div>

    {{-- ════════════════════════════════════════
         STEP 2: PILIH LAYANAN
    ════════════════════════════════════════ --}}
    <div class="order-section-card p-5 md:p-6" id="section-layanan">
        <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100">
            <div class="section-icon-wrap bg-blue-50 text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <h2 class="font-black text-slate-900 text-lg leading-none">Pilih Jenis Layanan</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl">
            <button type="button" id="card-beli" onclick="switchTab('beli')" class="layanan-card active-beli">
                <div class="layanan-icon beli active" id="icon-beli">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <p class="font-black text-slate-900 text-sm">Beli Produk APAR</p>
                    <p class="text-[11px] text-slate-500 font-semibold mt-0.5">Beli APAR baru + aksesoris</p>
                </div>
            </button>
            <button type="button" id="card-service" onclick="switchTab('service')" class="layanan-card">
                <div class="layanan-icon service" id="icon-service">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <p class="font-black text-slate-900 text-sm">Layanan APAR</p>
                    <p class="text-[11px] text-slate-500 font-semibold mt-0.5">Perawatan, perbaikan, &amp; isi ulang APAR</p>
                </div>
            </button>
        </div>
        <input type="hidden" name="tipe_layanan" id="inp-tipe" value="{{ old('tipe_layanan', 'beli') }}">
    </div>

    {{-- ════════════════════════════════════════
         STEP 3A: BELI PRODUK
    ════════════════════════════════════════ --}}
        <div class="order-section-card p-5 md:p-6" id="section-beli-items">
            <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100">
                <div class="section-icon-wrap bg-red-50 text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="font-black text-slate-900 text-lg leading-none">Produk Dipesan</h2>
                </div>
            </div>
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                @if(!empty($canUseCartCheckout) && $cartHasItems)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        Produk di bawah ini siap diproses pada pemesanan ini. Lengkapi data pemesanan dan lanjutkan checkout pada ringkasan di sebelah kanan.
                    </div>

                    <div class="mt-5 space-y-3">
                        @foreach($cartItems as $item)
                            <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="cart-preview-thumb">
                                    @if($item->produk?->gambar)
                                        <img src="{{ asset('storage/' . $item->produk->gambar) }}" alt="{{ $item->produk->nama }}">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-black text-slate-900">{{ $item->produk?->nama ?? 'Produk' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item->produk?->jenisApar?->nama ?? 'APAR' }} - {{ $item->produk?->kapasitas ?? '-' }} - {{ $item->produk?->merek ?? 'SAFETY' }}</p>
                                    <p class="mt-2 text-xs font-semibold text-slate-400">Qty {{ $item->qty }} - @ Rp {{ number_format($item->harga, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-400">Subtotal</p>
                                    <p class="text-lg font-black text-red-600">Rp {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Ringkasan Produk</p>
                            <p class="mt-1 text-sm font-semibold text-slate-600">{{ $cartItemCount }} unit siap diproses di halaman ini.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Total Belanja</p>
                            <p class="mt-1 text-2xl font-black text-red-600">Rp {{ number_format($cartTotal, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('produk.index') }}" class="flow-cta secondary flex-1">
                            Kembali ke Katalog
                        </a>
                        <a href="#bank-options" class="flow-cta primary flex-1">Lanjut Checkout</a>
                    </div>
                @elseif($prefilledOrderItems->isNotEmpty())
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        Produk yang Anda pilih dari detail produk sudah dimasukkan ke alur pemesanan. Lengkapi data dan lanjutkan checkout pada ringkasan di sebelah kanan.
                    </div>

                    <div class="mt-5 space-y-3">
                        @foreach($prefilledOrderItems as $index => $item)
                            <input type="hidden" name="items[{{ $index }}][produk_id]" value="{{ (int) ($item['produk_id'] ?? 0) }}">
                            <input type="hidden" name="items[{{ $index }}][jumlah]" value="{{ (int) ($item['jumlah'] ?? 0) }}">

                            <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="cart-preview-thumb">
                                    @if(!empty($item['gambar']))
                                        <img src="{{ asset('storage/' . $item['gambar']) }}" alt="{{ $item['nama'] ?? 'Produk' }}">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-black text-slate-900">{{ $item['nama'] ?? 'Produk' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item['jenis'] ?? 'APAR' }} - {{ $item['kapasitas'] ?? '-' }} - {{ $item['merek'] ?? 'SAFETY' }}</p>
                                    <p class="mt-2 text-xs font-semibold text-slate-400">Qty {{ (int) ($item['jumlah'] ?? 0) }} - @ Rp {{ number_format((float) ($item['harga'] ?? 0), 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-400">Subtotal</p>
                                    <p class="text-lg font-black text-red-600">Rp {{ number_format(((float) ($item['harga'] ?? 0)) * ((int) ($item['jumlah'] ?? 0)), 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Ringkasan Produk</p>
                            <p class="mt-1 text-sm font-semibold text-slate-600">{{ $prefilledOrderQty }} unit siap diproses pada halaman ini.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Total Belanja</p>
                            <p class="mt-1 text-2xl font-black text-red-600">Rp {{ number_format($prefilledOrderTotal, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ !empty($selectedOrderProduct) ? route('produk.show', $selectedOrderProduct) : route('produk.index') }}" class="flow-cta secondary flex-1">
                            Ubah Jumlah
                        </a>
                        <a href="#bank-options" class="flow-cta primary flex-1">Lanjut Checkout</a>
                    </div>
                @elseif(!empty($canUseCartCheckout))
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-slate-100 text-slate-500 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">Belum ada produk yang dipilih</h3>
                        <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-500">Pilih produk dari katalog terlebih dahulu, lalu lanjutkan ke proses pemesanan.</p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-center">
                            <a href="{{ route('produk.index') }}" class="flow-cta primary">Pilih Produk</a>
                            <a href="{{ route('produk.index') }}" class="flow-cta secondary">Lihat Katalog</a>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-slate-100 text-slate-500 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c0-4.418 3.134-8 7-8m0 0v5m0-5h-5M5 21h14a2 2 0 002-2v-5a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">Login pelanggan untuk lanjut pesan</h3>
                        <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-500">Pelanggan perlu login terlebih dahulu agar sistem bisa menampilkan kembali halaman pemesanan sesuai activity diagram.</p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-center">
                            <a href="{{ route('login') }}" class="flow-cta primary">Masuk Pelanggan</a>
                            <a href="{{ route('produk.index') }}" class="flow-cta secondary">Lihat Katalog</a>
                        </div>
                    </div>
                @endif
            </div>

            <div id="items-container" class="hidden"></div>
            <p id="empty-items-msg" class="hidden empty-items"></p>
        </div>

        <div id="section-service-inline" class="order-section-card p-5 md:p-6 hidden">
            @php
                $serviceKategoriOld = old('service_jenis_layanan', 'refill');
                $metodeOld = old('service_metode_penanganan', 'dijemput');
            @endphp
            <input type="hidden" name="service_jenis_apar" id="service-jenis-apar-hidden" value="{{ old('service_jenis_apar') }}">

            <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100">
                <div class="section-icon-wrap bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="font-black text-slate-900 text-lg leading-none">Layanan APAR</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Alurnya dibuat seperti checkout produk: pilih layanan, isi kebutuhan utama, lalu cek ringkasan otomatis.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="order-label">Kategori Layanan <span>*</span></label>
                    <select name="service_jenis_layanan" id="service-jenis-layanan" class="order-input">
                        <option value="refill" {{ $serviceKategoriOld === 'refill' ? 'selected' : '' }}>Refill APAR</option>
                        <option value="service" {{ $serviceKategoriOld === 'service' ? 'selected' : '' }}>Service APAR</option>
                    </select>
                </div>
                @php
                    $unitStatusOld = old('service_unit_status', (old('service_unit_apar_id') || old('service_unit_apar_ids')) ? 'terdaftar' : 'belum_terdaftar');
                    $unitStatusOld = in_array($unitStatusOld, ['terdaftar', 'belum_terdaftar'], true) ? $unitStatusOld : 'belum_terdaftar';
                    $registeredUnitApars = $registeredUnitApars ?? collect();
                @endphp
                <div id="service-unit-status-fields" class="md:col-span-2">
                    <label class="order-label mb-3">Status Unit APAR <span>*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="service_unit_status" value="terdaftar" {{ $unitStatusOld === 'terdaftar' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-blue-600">
                            <span>
                                <span class="block text-sm font-black text-slate-800">APAR Terdaftar</span>
                                <span class="block mt-0.5 text-xs font-semibold leading-relaxed text-slate-500">Pilih riwayat pembelian, lalu centang Unit APAR yang ingin diproses.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="service_unit_status" value="belum_terdaftar" {{ $unitStatusOld === 'belum_terdaftar' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-blue-600">
                            <span>
                                <span class="block text-sm font-black text-slate-800">APAR Belum Terdaftar</span>
                                <span class="block mt-0.5 text-xs font-semibold leading-relaxed text-slate-500">Gunakan form manual seperti biasa untuk APAR yang belum masuk data unit.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div id="service-registered-unit-fields" class="hidden md:col-span-2">
                    <label class="order-label">Pilih Tanggal Pembelian APAR <span>*</span></label>
                    <select name="service_purchase_group" id="service-purchase-group" class="order-input">
                        <option value="">-- Pilih Tanggal Pembelian APAR --</option>
                        @foreach($registeredUnitApars->groupBy(fn ($unitApar) => $unitApar->tgl_beli ? $unitApar->tgl_beli->toDateString() : 'tanpa-tanggal') as $purchaseKey => $units)
                            @php
                                $firstUnit = $units->first();
                                $purchaseDate = $firstUnit?->tgl_beli ? $firstUnit->tgl_beli->translatedFormat('d F Y') : 'Tanpa tanggal pembelian';
                            @endphp
                            <option value="{{ $purchaseKey }}" {{ old('service_purchase_group') === $purchaseKey ? 'selected' : '' }}>
                                {{ $purchaseDate }} - {{ $units->count() }} Unit APAR
                            </option>
                        @endforeach
                    </select>
                    @if($registeredUnitApars->isEmpty())
                        <p class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold leading-relaxed text-amber-800">
                            Belum ada Unit APAR terdaftar. Silakan gunakan opsi APAR Belum Terdaftar atau hubungi admin.
                        </p>
                    @endif
                    <p class="mt-2 text-xs font-semibold leading-relaxed text-slate-500">
                        Setelah tanggal pembelian dipilih, centang Unit APAR yang ingin diproses. Unit yang tidak diproses cukup hapus centangnya.
                    </p>
                    <div id="service-registered-empty-note" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-500">
                        Pilih tanggal pembelian terlebih dahulu untuk melihat daftar Unit APAR.
                    </div>
                    <div id="service-registered-unit-list" class="mt-4 space-y-3"></div>
                    <p id="service-registered-count-note" class="hidden mt-3 text-xs font-black uppercase tracking-[0.18em] text-blue-600"></p>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                        <button type="button" id="btn-select-all-units" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-red-700 hover:bg-red-100 transition">
                            Centang Semua
                        </button>
                        <button type="button" id="btn-clear-all-units" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-slate-600 hover:bg-slate-100 transition">
                            Hapus Semua Centang
                        </button>
                    </div>
                </div>

                <div id="service-manual-size-field">
                    <label class="order-label">Ukuran APAR <span>*</span></label>
                    <select name="service_ukuran_apar" id="service-ukuran-apar" class="order-input">
                        <option value="">-- Pilih Ukuran APAR --</option>
                        @foreach($serviceUkuranOptions as $ukuran)
                            <option value="{{ $ukuran }}" {{ old('service_ukuran_apar') === $ukuran ? 'selected' : '' }}>{{ $ukuran }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="service-refill-fields" class="contents">
                    <div>
                        <label class="order-label">Jenis Refil <span>*</span></label>
                        <select name="service_jenis_refill_id" id="service-jenis-refill-id" class="order-input">
                            <option value="">-- Pilih Jenis Refil --</option>
                            @foreach($jenisRefills as $jenisRefill)
                                <option value="{{ $jenisRefill->id }}" {{ (string) old('service_jenis_refill_id') === (string) $jenisRefill->id ? 'selected' : '' }}>{{ $jenisRefill->nama_label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Harga Standar Refil</p>
                        <p id="service-refill-price-note" class="mt-2 text-sm font-semibold leading-relaxed text-slate-700">Harga standar refil akan muncul otomatis saat jenis refil dan ukuran APAR dipilih.</p>
                    </div>
                </div>

                <div id="service-service-fields" class="contents hidden">
                    <div>
                        <label class="order-label">Paket Service <span>*</span></label>
                        <select name="service_paket_id" id="service-paket-id" class="order-input">
                            <option value="">-- Pilih Paket Service --</option>
                            @foreach($servicePakets as $servicePaket)
                                <option value="{{ $servicePaket->id }}" {{ (string) old('service_paket_id') === (string) $servicePaket->id ? 'selected' : '' }}>{{ $servicePaket->label ?: 'Paket' }} - {{ $servicePaket->nama }} - Rp {{ number_format($servicePaket->harga, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="service-paket-info" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Ringkasan Paket</p>
                        <div id="service-paket-info-content" class="mt-3 space-y-2 text-sm font-semibold leading-relaxed text-slate-700"></div>
                    </div>
                </div>

                <div id="service-quantity-field">
                    <label class="order-label">Jumlah Unit <span>*</span></label>
                    <input type="number" name="service_jumlah_unit" id="service-jumlah-unit" min="1" value="{{ old('service_jumlah_unit', 1) }}" class="order-input">
                </div>
                <div>
                    <label class="order-label">Upload Foto APAR <span class="text-slate-300 font-normal normal-case tracking-normal">(Opsional)</span></label>
                    <input type="file" name="service_foto" id="service-foto" accept=".jpg,.jpeg,.png,.webp" class="order-input text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-600">
                    <p class="text-[11px] font-semibold text-slate-400 mt-1">Foto membantu admin melakukan pemeriksaan awal.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="order-label mb-3">Metode Penanganan <span>*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="service_metode_penanganan" value="dijemput" {{ $metodeOld === 'dijemput' ? 'checked' : '' }} class="w-4 h-4 text-blue-600">
                            <span class="text-sm font-bold text-slate-700">Dijemput</span>
                        </label>
                        <label class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="service_metode_penanganan" value="antar sendiri" {{ $metodeOld === 'antar sendiri' ? 'checked' : '' }} class="w-4 h-4 text-blue-600">
                            <span class="text-sm font-bold text-slate-700">Antar Sendiri</span>
                        </label>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="order-label">Catatan / Keluhan</label>
                    <textarea name="service_keluhan" id="service-keluhan" rows="3" placeholder="Contoh: tabung perlu refill, minta pengecekan valve, atau ingin dijemput hari kerja." class="order-input resize-none">{{ old('service_keluhan', old('keterangan_service')) }}</textarea>
                </div>
            </div>
        </div>

            </div>

            <div class="lg:col-span-2 lg:sticky lg:top-24">

                <div id="section-beli-sidebar" class="hidden space-y-5">
                    <div class="order-section-card p-6">
                        <div class="flex items-center gap-3">
                            <div class="section-icon-wrap bg-blue-50 text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Ringkasan Layanan</p>
                                <h3 class="text-lg font-black text-slate-900">Checkout layanan yang sederhana</h3>
                            </div>
                        </div>
                        <p class="mt-4 text-sm font-semibold leading-relaxed text-slate-500">Pilih jenis layanan, cek estimasi harga, lalu kirim pesanan. Sistem akan menghitung kebutuhan refill dan menampilkan kondisi stok otomatis.</p>
                    </div>

                    <div class="order-section-card p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Ringkasan Pesanan</p>
                                <p class="text-[11px] font-semibold text-slate-500">Selalu ikut berubah saat form diisi.</p>
                            </div>
                        </div>
                        <div class="summary-card space-y-3">
                            <div class="summary-row">
                                <span class="text-slate-500 font-semibold">Kategori</span>
                                <span id="service-summary-category" class="font-black text-slate-800">Refill APAR</span>
                            </div>
                            <div id="service-summary-status-row" class="summary-row">
                                <span class="text-slate-500 font-semibold">Status Unit</span>
                                <span id="service-summary-status" class="font-black text-slate-800">APAR Belum Terdaftar</span>
                            </div>
                            <div id="service-summary-unit-row" class="summary-row hidden">
                                <span class="text-slate-500 font-semibold">Unit APAR</span>
                                <span id="service-summary-unit" class="font-black text-slate-800 text-right">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-slate-500 font-semibold">Layanan Dipilih</span>
                                <span id="service-summary-item" class="font-black text-slate-800">Belum dipilih</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-slate-500 font-semibold">Ukuran APAR</span>
                                <span id="service-summary-size" class="font-black text-slate-800">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-slate-500 font-semibold">Jumlah Unit</span>
                                <span id="service-summary-qty" class="font-black text-slate-800">1 unit</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-slate-500 font-semibold">Kebutuhan Refill</span>
                                <span id="service-summary-kg" class="font-black text-slate-800">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-slate-500 font-semibold">Metode Penanganan</span>
                                <span id="service-summary-method" class="font-black text-slate-800">Dijemput</span>
                            </div>
                            <div class="summary-row total">
                                <span class="text-slate-500 font-semibold">Estimasi Harga</span>
                                <span id="service-summary-price" class="text-xl font-black text-blue-600">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="order-section-card p-6 space-y-4">
                        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Stok Saat Ini</p>
                            <p id="service-stock-current" class="mt-2 text-lg font-black text-slate-900">Pilih jenis refil untuk melihat stok.</p>
                            <p id="service-stock-after" class="mt-1 text-xs font-semibold text-slate-500">Sisa stok setelah transaksi akan tampil di sini.</p>
                        </div>

                        <div id="service-stock-warning" class="hidden rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700"></div>
                        <div id="service-low-stock-warning" class="hidden rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800"></div>

                        <div id="service-package-note" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Rincian Paket</p>
                            <div id="service-package-rincian" class="mt-3 space-y-2 text-sm font-semibold text-slate-700"></div>
                        </div>

                        <button type="submit" id="btn-service-submit" class="btn-primary-action submit service w-full justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span id="btn-service-submit-label">Lanjut ke Pembayaran</span>
                        </button>

                        <p class="text-[11px] text-slate-400 font-semibold text-center">Harga layanan memakai standar sistem. Setelah form dikirim, pelanggan langsung lanjut ke pembayaran dan status akan mengikuti alur pickup atau pengiriman.</p>
                    </div>
                </div>
                <div id="section-service-sidebar" class="space-y-5">

                {{-- Shipping Method --}}
                <div class="order-section-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        </div>
                        <p class="text-sm font-black text-slate-800">Metode Pengiriman</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="btn-pickup" class="shipping-btn pickup active">
                            Ambil Sendiri
                        </button>
                        <button type="button" id="btn-lalamove" class="shipping-btn lalamove">
                            Diantar
                        </button>
                    </div>
                    <button type="button" id="btn-check-ongkir"
                        class="hidden mt-3 w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition">
                        <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Hitung Ongkir Ekspedisi
                    </button>
                    <p id="shipping-status-note" class="hidden mt-2 text-xs font-bold rounded-lg px-3 py-2"></p>
                </div>

                <div class="order-section-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2m-2 0h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/></svg>
                        </div>
                        <p class="text-sm font-black text-slate-800">Pilih Bank Tujuan</p>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-500 leading-relaxed mb-4">
                        Pembayaran dilakukan melalui transfer ke rekening resmi perusahaan. Detail rekening akan ditampilkan setelah pesanan dibuat.
                    </p>
                    <div id="bank-options" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <button type="button" class="bank-option" data-bank="bca">
                            <div class="bank-option-label">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Transfer Bank</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">Bank BCA</p>
                                </div>
                                <span class="bank-option-dot" aria-hidden="true"></span>
                            </div>
                        </button>
                        <button type="button" class="bank-option" data-bank="mandiri">
                            <div class="bank-option-label">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Transfer Bank</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">Bank Mandiri</p>
                                </div>
                                <span class="bank-option-dot" aria-hidden="true"></span>
                            </div>
                        </button>
                        <button type="button" class="bank-option" data-bank="bri">
                            <div class="bank-option-label">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Transfer Bank</p>
                                    <p class="text-sm font-black text-slate-900 mt-1">Bank BRI</p>
                                </div>
                                <span class="bank-option-dot" aria-hidden="true"></span>
                            </div>
                        </button>
                    </div>
                    <p id="bank-selection-error" class="error-msg hidden"></p>
                </div>

                {{-- Negosiasi tetap tersedia di bawah bank tujuan sesuai alur sistem yang sudah ada --}}
                <div class="order-section-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800">Penyesuaian Harga</p>
                            <p class="text-[11px] font-semibold text-slate-500">Alur negosiasi sesuai proposal skripsi.</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-black text-amber-900">Butuh harga proyek, pembelian banyak, atau penawaran khusus?</p>
                        <p class="mt-1 text-xs font-semibold leading-relaxed text-amber-700">Pelanggan bisa diskusi dulu via WhatsApp, lalu kirim harga usulan di sistem. Setelah pemilik/admin menyetujui, pelanggan lanjut transaksi dengan kode penawaran.</p>

                        <div class="mt-4 grid gap-2 rounded-2xl border border-amber-200 bg-white/80 p-3 text-[11px] font-semibold text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-white">1</span>
                                <span>Tanya atau nego awal lewat WhatsApp.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-white">2</span>
                                <span>Masukkan harga usulan Anda ke sistem.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-black text-white">3</span>
                                <span>Jika disetujui, masukkan kode penawaran lalu lanjut checkout.</span>
                            </div>
                        </div>

                        <button type="button" id="btn-ask-whatsapp" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#25D366] px-4 py-3 text-sm font-black text-white shadow-lg shadow-[#25D366]/20 transition hover:brightness-110">
                            <i class="fa-brands fa-whatsapp text-base"></i>
                            Tanya Harga via WhatsApp
                        </button>

                        <div class="mt-4 rounded-2xl border border-amber-200 bg-white p-4">
                            <label class="order-label mb-2">Ajukan Harga Usulan Anda</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-amber-600">Rp</span>
                                <input type="text" id="inp-display-harga-usulan" placeholder="Contoh: 450000"
                                    class="w-full pl-9 pr-4 py-3 bg-white border border-amber-200 rounded-xl text-sm font-black text-slate-900 focus:outline-none focus:border-amber-500 transition">
                            </div>
                            <p class="mt-2 text-[11px] font-semibold leading-relaxed text-amber-700">Harga usulan ini akan masuk ke sistem dan menunggu persetujuan pemilik/admin.</p>
                            <p id="nego-minimum-note" class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-bold text-amber-800">
                                Harga usulan hanya bisa diajukan jika total pembelian minimal 10 unit.
                            </p>
                            <button type="button" id="btn-submit-nego" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-amber-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16m-7-7l7 7-7 7"/></svg>
                                Ajukan Harga ke Sistem
                            </button>
                        </div>

                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-sm font-black text-emerald-900">Sudah di-ACC pemilik?</p>
                            <p class="mt-1 text-xs font-semibold leading-relaxed text-emerald-700">Masukkan kode penawaran yang dikirim pemilik/admin untuk melanjutkan transaksi dengan harga deal.</p>
                        </div>
                    </div>

                    {{-- Kode Negosiasi --}}
                    <div class="mt-4">
                        <label class="order-label mb-1.5">Masukkan Kode Penawaran</label>
                        <div class="flex gap-2">
                            <input type="text" id="inp-kode-nego" name="kode_nego"
                                placeholder="ANUTA-123"
                                class="kode-nego-input">
                            <button type="button" id="btn-check-nego-code" class="btn-nego-check">
                                Cek Kode
                            </button>
                        </div>
                        <p id="nego-code-error" class="error-msg hidden"></p>
                        <div id="deal-status-note" class="deal-box hidden mt-2">
                            <p class="text-sm font-bold text-emerald-800">
                                Kode <span id="deal-code-label" class="font-black">-</span> valid — Harga Khusus:
                                <span id="deal-price-label" class="font-black text-base">Rp 0</span>
                            </p>
                        </div>
                        <p id="normal-price-note" class="text-[11px] text-slate-400 font-semibold mt-2 italic">
                            Kode ini diberikan setelah harga usulan Anda disetujui pemilik/admin.
                        </p>
                    </div>
                </div>

                <div class="order-section-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-black text-slate-800">Ringkasan Pesanan</p>
                    </div>

                    <div class="summary-card">
                        <div class="summary-row">
                            <span class="text-slate-500 font-semibold">Subtotal</span>
                            <span id="lbl-subtotal" class="font-bold text-slate-700">Rp 0</span>
                        </div>
                        <div class="summary-row text-emerald-700" id="discount-row">
                            <span class="font-semibold">Potongan</span>
                            <span id="lbl-discount" class="font-bold">- Rp 0</span>
                        </div>
                        <div class="summary-row text-emerald-700 hidden" id="ongkir-row">
                            <span class="font-semibold">Ongkir Ekspedisi</span>
                            <span id="lbl-ongkir" class="font-bold">Rp 0</span>
                        </div>
                        <div class="summary-row total">
                            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Total Pembayaran</span>
                            <span id="lbl-total" class="text-xl font-black text-red-600">Rp 0</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-4 space-y-2.5">
                        <button type="submit" id="btn-submit" class="btn-primary-action submit w-full justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span id="btn-submit-label">Checkout</span>
                        </button>
                    </div>

                    <p class="text-[10px] text-slate-400 font-semibold text-center mt-3">Pastikan data, pengiriman, dan bank tujuan sudah sesuai sebelum melanjutkan.</p>
                </div>
                </div>
            </div>
        </div>

    </form>
</div>
</div>

{{-- ════════════════════════════════════════
     INLINE ROW TEMPLATE
════════════════════════════════════════ --}}
<template id="tmpl-row">
    <div class="item-card item-row" data-harga="0" data-nama="">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-2.5 items-start">
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jenis</label>
                <select class="sel-jenis">
                    <option value="">Pilih</option>
                </select>
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Kapasitas</label>
                <select class="sel-kapasitas" disabled>
                    <option>-</option>
                </select>
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Merek</label>
                <select class="sel-merek" disabled>
                    <option>-</option>
                </select>
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 text-right">Harga</label>
                <p class="lbl-harga text-sm font-bold text-slate-600 text-right mt-2 pr-2">Rp 0</p>
                <input type="hidden" class="inp-produk-id">
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 text-center">Qty</label>
                <input type="number" class="inp-qty text-center" value="1" min="1" disabled>
            </div>
            <div class="flex flex-col items-end gap-2">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Subtotal</label>
                <p class="lbl-subtotal text-sm font-black text-red-600 mt-2">Rp 0</p>
                <button type="button" class="btn-hapus btn-delete-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    </div>
</template>

{{-- Leaflet Map Init (loaded from public.blade.php CDN) --}}
<script>
(function() {
    'use strict';

    let currentTab = 'beli';
    let dealValidated = false;
    let validatedDealPrice = 0;
    let validatedNegoCode = '';
    let negotiatedNormalReferenceTotal = 0;
    let dealPriceIncludesShipping = true;
    let normalTotal = 0;
    let shippingMethod = 'pickup';
    let shippingCost = 0;
    let shippingDistanceKm = 0;
    let shippingQuoteReady = true;
    let rowIndex = 0;

    const itemsContainer = document.getElementById('items-container');
    const tmplRow = document.getElementById('tmpl-row');
    const lblTotal = document.getElementById('lbl-total');
    const lblSubtotal = document.getElementById('lbl-subtotal');
    const discountRow = document.getElementById('discount-row');
    const lblDiscount = document.getElementById('lbl-discount');
    const ongkirRow = document.getElementById('ongkir-row');
    const lblOngkir = document.getElementById('lbl-ongkir');
    const btnSubmitLabel = document.getElementById('btn-submit-label');
    const btnSubmit = document.getElementById('btn-submit');
    const btnAskWhatsApp = document.getElementById('btn-ask-whatsapp');
    const btnSubmitNego = document.getElementById('btn-submit-nego');
    const btnPickup = document.getElementById('btn-pickup');
    const btnLalamove = document.getElementById('btn-lalamove');
    const btnCheckOngkir = document.getElementById('btn-check-ongkir');
    const bankOptions = [...document.querySelectorAll('.bank-option')];
    const inpBank = document.getElementById('inp-bank');
    const bankSelectionError = document.getElementById('bank-selection-error');
    const shippingStatusNote = document.getElementById('shipping-status-note');
    const inpKodeNego = document.getElementById('inp-kode-nego');
    const inpHargaDeal = document.getElementById('inp-harga-deal');
    const inpIsNegoDeal = document.getElementById('inp-is-nego-deal');
    const inpMetodePengiriman = document.getElementById('inp-metode-pengiriman');
    const inpOngkir = document.getElementById('inp-ongkir');
    const inpShippingDistance = document.getElementById('inp-shipping-distance');
    const normalPriceNote = document.getElementById('normal-price-note');
    const dealStatusNote = document.getElementById('deal-status-note');
    const dealPriceLabel = document.getElementById('deal-price-label');
    const dealCodeLabel = document.getElementById('deal-code-label');
    const negoCodeError = document.getElementById('nego-code-error');
    const btnCheckNegoCode = document.getElementById('btn-check-nego-code');
    const inpDisplayHargaUsulan = document.getElementById('inp-display-harga-usulan');
    const negoMinimumNote = document.getElementById('nego-minimum-note');
    const orderForm = document.getElementById('order-form');
    const inpSubmitSource = document.getElementById('inp-submit-source');
    const inpTipeHarga = document.getElementById('inp-tipe-harga');
    const inpNama = document.getElementById('inp-nama');
    const inpNoWa = document.getElementById('inp-nowa');
    const inpAlamatMaps = document.getElementById('inp-alamat-maps');
    const inpAlamatDetail = document.getElementById('inp-alamat-detail');
    const inpAlamatCombined = document.getElementById('inp-alamat-combined');
    const inpAlamatLat = document.getElementById('inp-alamat-lat');
    const inpAlamatLng = document.getElementById('inp-alamat-lng');
    const inpPerusahaan = document.getElementById('inp-perusahaan');
    const addressHelper = document.getElementById('address-suggestions');
    const btnTambahItem = document.getElementById('btn-tambah-item');
    const orderMapLatEl = document.getElementById('order-map-lat');
    const orderMapLngEl = document.getElementById('order-map-lng');
    const orderMapEl = document.getElementById('order-map');
    const emptyItemsMsg = document.getElementById('empty-items-msg');
    const serviceJenisLayanan = document.getElementById('service-jenis-layanan');
    const serviceJenisRefill = document.getElementById('service-jenis-refill-id');
    const servicePaketId = document.getElementById('service-paket-id');
    const serviceUkuranApar = document.getElementById('service-ukuran-apar');
    const serviceJumlahUnit = document.getElementById('service-jumlah-unit');
    const serviceQuantityField = document.getElementById('service-quantity-field');
    const serviceKeluhan = document.getElementById('service-keluhan');
    const serviceUnitStatusFields = document.getElementById('service-unit-status-fields');
    const serviceUnitStatusRadios = [...document.querySelectorAll('input[name="service_unit_status"]')];
    const serviceRegisteredUnitFields = document.getElementById('service-registered-unit-fields');
    const servicePurchaseGroup = document.getElementById('service-purchase-group');
    const serviceRegisteredEmptyNote = document.getElementById('service-registered-empty-note');
    const serviceRegisteredUnitList = document.getElementById('service-registered-unit-list');
    const serviceRegisteredCountNote = document.getElementById('service-registered-count-note');
    const btnSelectAllUnits = document.getElementById('btn-select-all-units');
    const btnClearAllUnits = document.getElementById('btn-clear-all-units');
    const serviceJenisAparHidden = document.getElementById('service-jenis-apar-hidden');
    const serviceManualSizeField = document.getElementById('service-manual-size-field');
    const serviceRefillFields = document.getElementById('service-refill-fields');
    const serviceServiceFields = document.getElementById('service-service-fields');
    const serviceRefillPriceNote = document.getElementById('service-refill-price-note');
    const servicePaketInfo = document.getElementById('service-paket-info');
    const servicePaketInfoContent = document.getElementById('service-paket-info-content');
    const serviceSummaryCategory = document.getElementById('service-summary-category');
    const serviceSummaryStatusRow = document.getElementById('service-summary-status-row');
    const serviceSummaryStatus = document.getElementById('service-summary-status');
    const serviceSummaryUnitRow = document.getElementById('service-summary-unit-row');
    const serviceSummaryUnit = document.getElementById('service-summary-unit');
    const serviceSummaryItem = document.getElementById('service-summary-item');
    const serviceSummarySize = document.getElementById('service-summary-size');
    const serviceSummaryQty = document.getElementById('service-summary-qty');
    const serviceSummaryKg = document.getElementById('service-summary-kg');
    const serviceSummaryMethod = document.getElementById('service-summary-method');
    const serviceSummaryPrice = document.getElementById('service-summary-price');
    const serviceStockCurrent = document.getElementById('service-stock-current');
    const serviceStockAfter = document.getElementById('service-stock-after');
    const serviceStockWarning = document.getElementById('service-stock-warning');
    const serviceLowStockWarning = document.getElementById('service-low-stock-warning');
    const servicePackageNote = document.getElementById('service-package-note');
    const servicePackageRincian = document.getElementById('service-package-rincian');
    const serviceMethodRadios = [...document.querySelectorAll('input[name="service_metode_penanganan"]')];
    let lastRenderedPurchaseGroup = null;
    let addressSearchTimer = null;
    let addressSuggestionItems = [];
    let orderMap = null;
    let orderMarker = null;

    function fmt(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function enableSelect(sel) {
        sel.disabled = false;
        sel.classList.remove('bg-slate-100', 'text-slate-400');
        sel.classList.add('bg-slate-50', 'text-slate-800');
    }

    function disableSelect(sel) {
        sel.disabled = true;
        sel.classList.remove('bg-slate-50', 'text-slate-800');
        sel.classList.add('bg-slate-100', 'text-slate-400');
    }

    function hasSelectedProduct() {
        if (CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS) {
            return CART_ORDER_ITEMS.length > 0;
        }
        if (PREFILLED_ORDER_ITEMS.length > 0) {
            return true;
        }
        return [...itemsContainer.querySelectorAll('.inp-produk-id')].some((el) => el.value);
    }

    function normalizePhone(phone) {
        return String(phone || '').replace(/\D+/g, '');
    }

    function getSelectedBankName() {
        const map = {
            bca: 'Bank BCA',
            mandiri: 'Bank Mandiri',
            bri: 'Bank BRI',
        };

        return map[inpBank.value] || '';
    }

    function hideBankError() {
        if (!bankSelectionError) return;
        bankSelectionError.classList.add('hidden');
        bankSelectionError.textContent = '';
    }

    function showBankError(message) {
        if (!bankSelectionError) return;
        bankSelectionError.textContent = message;
        bankSelectionError.classList.remove('hidden');
    }

    function setSelectedBank(bank) {
        inpBank.value = bank || '';
        bankOptions.forEach((option) => {
            option.classList.toggle('active', option.dataset.bank === inpBank.value);
        });
        hideBankError();
    }

    function updateCombinedAddress() {
        const maps = (inpAlamatMaps.value || '').trim();
        const detail = (inpAlamatDetail.value || '').trim();
        const combined = [maps, detail].filter(Boolean).join(' | Detail: ');
        inpAlamatCombined.value = combined;
    }

    function setShippingStatus(message, type) {
        if (!message) {
            shippingStatusNote.classList.add('hidden');
            shippingStatusNote.textContent = '';
            return;
        }
        shippingStatusNote.classList.remove('hidden');
        shippingStatusNote.textContent = message;
        if (type === 'error') {
            shippingStatusNote.className = 'mt-2 text-xs font-bold rounded-lg px-3 py-2 bg-red-50 border border-red-200 text-red-700';
        } else if (type === 'success') {
            shippingStatusNote.className = 'mt-2 text-xs font-bold rounded-lg px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700';
        } else {
            shippingStatusNote.className = 'mt-2 text-xs font-bold rounded-lg px-3 py-2 bg-blue-50 border border-blue-200 text-blue-700';
        }
    }

    function applyShippingModeVisual() {
        const isPickup = shippingMethod === 'pickup';
        btnPickup.className = 'shipping-btn pickup' + (isPickup ? ' active' : '');
        btnLalamove.className = 'shipping-btn lalamove' + (!isPickup ? ' active' : '');
        btnCheckOngkir.classList.toggle('hidden', isPickup);
    }

    function setShippingMethod(method) {
        shippingMethod = method === 'diantar' ? 'diantar' : 'pickup';
        inpMetodePengiriman.value = shippingMethod;

        if (shippingMethod === 'pickup') {
            shippingCost = 0;
            shippingDistanceKm = 0;
            shippingQuoteReady = true;
            inpOngkir.value = '0';
            inpShippingDistance.value = '0';
            setShippingStatus('Metode: Ambil sendiri — tanpa biaya ongkir.', 'info');
        } else {
            shippingCost = 0;
            shippingDistanceKm = 0;
            shippingQuoteReady = false;
            inpOngkir.value = '0';
            inpShippingDistance.value = '0';
            setShippingStatus('Klik "Hitung Ongkir Ekspedisi" untuk menghitung biaya pengiriman.', 'info');
        }

        applyShippingModeVisual();
        syncDisplayedTotal();
    }

    function invalidateShippingQuote(message) {
        if (shippingMethod !== 'diantar') return;
        shippingQuoteReady = false;
        shippingCost = 0;
        shippingDistanceKm = 0;
        inpOngkir.value = '0';
        inpShippingDistance.value = '0';
        setShippingStatus(message || 'Alamat berubah — silakan hitung ongkir lagi.', 'info');
        syncDisplayedTotal();
    }

    function hideAddressSuggestions() {
        if (orderMap && orderMap.closePopup) {
            orderMap.closePopup();
        }
        addressHelper.classList.add('hidden');
        addressHelper.innerHTML = '';
        addressSuggestionItems = [];
    }

    function renderAddressSuggestions(items) {
        if (!Array.isArray(items) || !items.length) {
            hideAddressSuggestions();
            return;
        }
        addressSuggestionItems = items;
        if (orderMap && orderMap.closePopup) {
            orderMap.closePopup();
        }
        addressHelper.innerHTML = '';
        items.forEach((item, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'address-suggestion-item';
            btn.dataset.addressIndex = String(idx);
            btn.title = '';

            const title = document.createElement('span');
            title.className = 'address-suggestion-title';
            title.textContent = String(item.display_name || '');

            const subtitle = document.createElement('span');
            subtitle.className = 'address-suggestion-subtitle';
            subtitle.textContent = `Lat ${Number(item.lat || 0).toFixed(5)} • Lng ${Number(item.lng || item.lon || 0).toFixed(5)}`;

            btn.appendChild(title);
            btn.appendChild(subtitle);
            addressHelper.appendChild(btn);
        });
        addressHelper.classList.remove('hidden');
    }

    function updateOrderCoord(lat, lng) {
        inpAlamatLat.value = Number(lat).toFixed(8);
        inpAlamatLng.value = Number(lng).toFixed(8);
        if (orderMapLatEl) orderMapLatEl.textContent = Number(lat).toFixed(6);
        if (orderMapLngEl) orderMapLngEl.textContent = Number(lng).toFixed(6);
        updateCombinedAddress();
    }

    function initLeafletMap(lat, lng) {
        if (!orderMapEl) return;
        if (orderMap) {
            orderMap.remove();
            orderMap = null;
        }
        orderMap = L.map('order-map', { zoomControl: true, scrollWheelZoom: false }).setView([lat, lng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(orderMap);

        var redIcon = L.divIcon({
            html: '<div style="background:#dc2626;width:36px;height:36px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 4px 12px rgba(220,38,38,0.4);"></div>',
            iconAnchor: [18, 36],
            popupAnchor: [0, -36],
            className: ''
        });

        orderMarker = L.marker([lat, lng], { icon: redIcon, draggable: true }).addTo(orderMap)
            .bindPopup('Lokasi Pesanan');

        orderMarker.on('dragend', function(e) {
            var pos = e.target.getLatLng();
            updateOrderCoord(pos.lat, pos.lng);
            invalidateShippingQuote();
        });

        orderMap.on('click', function(e) {
            orderMarker.setLatLng(e.latlng);
            updateOrderCoord(e.latlng.lat, e.latlng.lng);
            invalidateShippingQuote();
        });
    }

    function selectAddressSuggestion(displayName, lat, lng, item = null) {
        inpAlamatMaps.value = displayName;
        inpAlamatLat.value = String(lat);
        inpAlamatLng.value = String(lng);
        if (item) {
            const inpProvinsi = document.getElementById('inp-provinsi');
            const inpKota = document.getElementById('inp-kota');
            const inpKecamatan = document.getElementById('inp-kecamatan');
            const inpKodePos = document.getElementById('inp-kodepos');
            if (inpProvinsi) inpProvinsi.value = item.provinsi || '';
            if (inpKota) inpKota.value = item.kota || '';
            if (inpKecamatan) inpKecamatan.value = item.kecamatan || '';
            if (inpKodePos) inpKodePos.value = item.kode_pos || '';
        }
        updateCombinedAddress();
        invalidateShippingQuote();
        hideAddressSuggestions();
        if (lat && lng) initLeafletMap(Number(lat), Number(lng));
    }

    async function fetchAddressSuggestions(query) {
        try {
            const response = await fetch(`${ADDRESS_SUGGEST_URL}?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Gagal mengambil saran alamat.');
            renderAddressSuggestions(data.data || []);
        } catch (error) {
            hideAddressSuggestions();
        }
    }

    function scheduleAddressSuggestSearch() {
        const query = (inpAlamatMaps.value || '').trim();
        inpAlamatLat.value = '';
        inpAlamatLng.value = '';
        updateCombinedAddress();
        invalidateShippingQuote();

        if (addressSearchTimer) clearTimeout(addressSearchTimer);

        if (query.length < 3) {
            hideAddressSuggestions();
            return;
        }

        addressSearchTimer = setTimeout(() => {
            fetchAddressSuggestions(query);
        }, 350);
    }

    async function checkShippingQuote() {
        if (shippingMethod !== 'diantar') return;

        const mapsAddress = (inpAlamatMaps.value || '').trim();
        const detailAddress = (inpAlamatDetail.value || '').trim();
        const lat = Number(inpAlamatLat.value || 0);
        const lng = Number(inpAlamatLng.value || 0);
        const items = getSelectedItems().map((item) => ({ jumlah: item.jumlah }));

        if (!mapsAddress || !detailAddress) {
            setShippingStatus('Alamat OpenStreetMap dan detail alamat wajib diisi sebelum cek ongkir.', 'error');
            return;
        }
        if (!lat || !lng) {
            setShippingStatus('Pilih alamat dari saran OpenStreetMap agar koordinat terbaca.', 'error');
            return;
        }
        if (!items.length) {
            setShippingStatus('Pilih minimal satu produk sebelum cek ongkir.', 'error');
            return;
        }

        btnCheckOngkir.disabled = true;
        btnCheckOngkir.textContent = 'Menghitung Ongkir...';
        setShippingStatus('', 'info');

        try {
            const response = await fetch(SHIPPING_QUOTE_URL, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ metode_pengiriman: 'diantar', alamat_maps: mapsAddress, alamat_detail: detailAddress, alamat_lat: lat, alamat_lng: lng, items }),
            });

            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Gagal menghitung ongkir.');

            shippingCost = Number(data.data?.ongkir || 0);
            shippingDistanceKm = Number(data.data?.distance_km || 0);
            shippingQuoteReady = shippingCost >= 0;
            inpOngkir.value = String(shippingCost);
            inpShippingDistance.value = String(shippingDistanceKm);

            setShippingStatus(`Ongkir Ekspedisi: ${fmt(shippingCost)} (Jarak ${shippingDistanceKm.toFixed(2)} km)`, 'success');
            syncDisplayedTotal();
        } catch (error) {
            invalidateShippingQuote(error.message || 'Gagal menghitung ongkir.');
        } finally {
            btnCheckOngkir.disabled = false;
            btnCheckOngkir.textContent = 'Hitung Ongkir Ekspedisi';
        }
    }

    function getSelectedItems() {
        if (CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS) {
            return CART_ORDER_ITEMS.map((item) => ({
                produk_id: Number(item.produk_id || 0),
                jumlah: Number(item.jumlah || 0),
                jenis: item.jenis || 'APAR',
                kapasitas: item.kapasitas || '-',
                merek: item.merek || 'SAFETY',
                harga: Number(item.harga || 0),
                nama: item.nama || 'Produk',
            })).filter((item) => item.produk_id > 0 && item.jumlah > 0);
        }

        if (PREFILLED_ORDER_ITEMS.length > 0) {
            return PREFILLED_ORDER_ITEMS.map((item) => ({
                produk_id: Number(item.produk_id || 0),
                jumlah: Number(item.jumlah || 0),
                jenis: item.jenis || 'APAR',
                kapasitas: item.kapasitas || '-',
                merek: item.merek || 'SAFETY',
                harga: Number(item.harga || 0),
                nama: item.nama || 'Produk',
            })).filter((item) => item.produk_id > 0 && item.jumlah > 0);
        }

        const rows = [...itemsContainer.querySelectorAll('.item-row')];
        return rows.map((row) => {
            const produkId = Number(row.querySelector('.inp-produk-id')?.value || 0);
            const qty = Number(row.querySelector('.inp-qty')?.value || 0);
            const jenis = row.querySelector('.sel-jenis')?.value || '-';
            const kapasitas = row.querySelector('.sel-kapasitas')?.value || '-';
            const merek = row.querySelector('.sel-merek')?.value || '-';
            const harga = Number(row.dataset.harga || 0);
            return { produk_id: produkId, jumlah: qty, jenis, kapasitas, merek, harga };
        }).filter((item) => item.produk_id > 0 && item.jumlah > 0);
    }

    function getSelectedItemQuantityTotal() {
        return getSelectedItems().reduce((totalQty, item) => totalQty + Number(item.jumlah || 0), 0);
    }

    function syncNegotiationEligibility() {
        const totalQty = getSelectedItemQuantityTotal();
        const eligible = totalQty >= 10;

        if (inpDisplayHargaUsulan) {
            inpDisplayHargaUsulan.disabled = !eligible;
            inpDisplayHargaUsulan.classList.toggle('bg-white', eligible);
            inpDisplayHargaUsulan.classList.toggle('bg-slate-100', !eligible);
            inpDisplayHargaUsulan.classList.toggle('text-slate-900', eligible);
            inpDisplayHargaUsulan.classList.toggle('text-slate-400', !eligible);
            inpDisplayHargaUsulan.classList.toggle('cursor-not-allowed', !eligible);
            if (!eligible) {
                inpDisplayHargaUsulan.value = '';
                inpHargaDeal.value = '';
            }
        }

        if (btnSubmitNego) {
            btnSubmitNego.disabled = !eligible;
            btnSubmitNego.classList.toggle('opacity-50', !eligible);
            btnSubmitNego.classList.toggle('cursor-not-allowed', !eligible);
        }

        if (negoMinimumNote) {
            negoMinimumNote.textContent = eligible
                ? `Harga usulan aktif. Total pembelian Anda ${totalQty} unit, jadi sudah memenuhi minimal 10 unit.`
                : `Harga usulan hanya bisa diajukan jika total pembelian minimal 10 unit. Saat ini total pembelian Anda ${totalQty} unit.`;
            negoMinimumNote.className = eligible
                ? 'mt-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] font-bold text-emerald-700'
                : 'mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-bold text-amber-800';
        }
    }

    function syncDisplayedTotal() {
        const totalOngkir = shippingMethod === 'diantar' ? shippingCost : 0;
        const normalGrandTotal = normalTotal + totalOngkir;
        const referenceNormalTotal = dealValidated && negotiatedNormalReferenceTotal > 0
            ? negotiatedNormalReferenceTotal
            : normalGrandTotal;

        const finalTotal = dealValidated
            ? (dealPriceIncludesShipping ? validatedDealPrice : (validatedDealPrice + totalOngkir))
            : normalGrandTotal;

        const discountValue = dealValidated ? Math.max(0, referenceNormalTotal - finalTotal) : 0;
        lblSubtotal.textContent = fmt(normalTotal);
        if (lblDiscount) lblDiscount.textContent = '- ' + fmt(discountValue);
        if (lblOngkir) lblOngkir.textContent = fmt(totalOngkir);
        if (ongkirRow) {
            ongkirRow.style.display = shippingMethod === 'diantar' ? 'flex' : 'none';
        }
        lblTotal.textContent = fmt(finalTotal);
        lblTotal.classList.toggle('text-emerald-600', dealValidated);
        lblTotal.classList.toggle('text-red-600', !dealValidated);

        if (dealValidated) {
            btnSubmitLabel.textContent = 'Checkout';
            btnSubmit.className = 'btn-primary-action submit deal w-full justify-center';
        } else {
            btnSubmitLabel.textContent = 'Checkout';
            btnSubmit.className = 'btn-primary-action submit w-full justify-center';
        }

        if (discountRow) {
            discountRow.classList.toggle('text-emerald-700', discountValue > 0);
            discountRow.classList.toggle('text-emerald-700', discountValue <= 0);
            discountRow.className = 'summary-row text-emerald-700';
        }

        if (dealValidated) {
            dealPriceLabel.textContent = fmt(validatedDealPrice);
            dealCodeLabel.textContent = validatedNegoCode || '-';
            dealStatusNote.classList.remove('hidden');
            if (normalPriceNote) normalPriceNote.classList.add('hidden');
        } else {
            dealStatusNote.classList.add('hidden');
            if (normalPriceNote) normalPriceNote.classList.remove('hidden');
        }
    }

    function resetDealValidation(clearError) {
        dealValidated = false;
        validatedDealPrice = 0;
        validatedNegoCode = '';
        negotiatedNormalReferenceTotal = 0;
        dealPriceIncludesShipping = true;
        inpHargaDeal.value = '';
        inpIsNegoDeal.value = '0';
        inpTipeHarga.value = 'normal';
        btnSubmitLabel.textContent = 'Checkout';
        btnSubmit.className = 'btn-primary-action submit w-full justify-center';
        if (clearError !== false) {
            negoCodeError.classList.add('hidden');
            negoCodeError.textContent = '';
        }
        syncDisplayedTotal();
    }

    function invalidateDealByItemChange() {
        if (!dealValidated) return;
        resetDealValidation(false);
        negoCodeError.textContent = 'Produk berubah. Silakan cek ulang kode penawaran.';
        negoCodeError.classList.remove('hidden');
    }

    function invalidatePricingByItemChange() {
        invalidateDealByItemChange();
        invalidateShippingQuote();
    }

    async function checkNegoCode() {
        const kode = inpKodeNego.value.trim().toUpperCase();
        const noWa = normalizePhone(inpNoWa.value);
        const items = getSelectedItems().map((item) => ({ produk_id: item.produk_id, jumlah: item.jumlah }));

        if (!hasSelectedProduct() || normalTotal <= 0) {
            resetDealValidation(false);
            negoCodeError.textContent = 'Pilih produk terlebih dahulu.';
            negoCodeError.classList.remove('hidden');
            return;
        }
        if (!noWa) {
            resetDealValidation(false);
            negoCodeError.textContent = 'Nomor WhatsApp wajib diisi.';
            negoCodeError.classList.remove('hidden');
            return;
        }
        if (!kode) {
            resetDealValidation(false);
            negoCodeError.textContent = 'Masukkan kode penawaran.';
            negoCodeError.classList.remove('hidden');
            return;
        }
        if (!/^ANUTA-\d{3}$/i.test(kode)) {
            resetDealValidation(false);
            negoCodeError.textContent = 'Format: ANUTA-xxx (contoh: ANUTA-123).';
            negoCodeError.classList.remove('hidden');
            return;
        }

        btnCheckNegoCode.disabled = true;
        btnCheckNegoCode.textContent = 'Mengecek...';
        negoCodeError.classList.add('hidden');

        try {
            const response = await fetch(CHECK_NEGO_CODE_URL, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify({ kode_nego: kode, no_wa: noWa, items }),
            });

            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Kode tidak valid.');

            const dealPrice = Number(data.data?.harga_deal || 0);
            if (dealPrice <= 0) throw new Error('Kode valid tapi harga deal belum tersedia.');

            const normalTotalFromServer = Number(data.data?.total_normal || 0);
            const normalGrandCurrent = normalTotal + (shippingMethod === 'diantar' ? shippingCost : 0);
            if (dealPrice > normalGrandCurrent) throw new Error('Harga deal (' + fmt(dealPrice) + ') lebih mahal dari harga normal (' + fmt(normalGrandCurrent) + ').');

            dealValidated = true;
            validatedDealPrice = dealPrice;
            validatedNegoCode = kode;
            negotiatedNormalReferenceTotal = normalTotalFromServer || normalGrandCurrent;
            dealPriceIncludesShipping = Boolean(data.data?.deal_includes_shipping ?? true);
            inpKodeNego.value = kode;
            inpHargaDeal.value = String(dealPrice);
            inpIsNegoDeal.value = '1';
            inpTipeHarga.value = 'deal';
            btnSubmitLabel.textContent = 'Checkout';
            btnSubmit.className = 'btn-primary-action submit deal w-full justify-center';
            syncDisplayedTotal();
        } catch (error) {
            resetDealValidation(false);
            negoCodeError.textContent = error.message || 'Gagal memvalidasi kode.';
            negoCodeError.classList.remove('hidden');
        } finally {
            btnCheckNegoCode.disabled = false;
            btnCheckNegoCode.textContent = 'Cek Kode';
        }
    }

    function formatKg(value) {
        const number = Number(value || 0);
        if (!Number.isFinite(number)) {
            return '0';
        }

        return number.toLocaleString('id-ID', {
            minimumFractionDigits: Number.isInteger(number) ? 0 : 2,
            maximumFractionDigits: 2,
        });
    }

    function parseServiceSizeKg(value) {
        const matched = String(value || '').match(/(\d+(?:[.,]\d+)?)/);
        if (!matched) {
            return 0;
        }

        return Number(String(matched[1]).replace(',', '.')) || 0;
    }

    function getSelectedServiceMethod() {
        const selected = serviceMethodRadios.find((radio) => radio.checked);
        return selected ? selected.value : 'dijemput';
    }

    function getServiceUnitStatus() {
        const selected = serviceUnitStatusRadios.find((radio) => radio.checked);
        return selected && selected.value === 'terdaftar' ? 'terdaftar' : 'belum_terdaftar';
    }

    function normalizeMatchText(value) {
        return String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function getUnitsByPurchaseGroup(groupKey) {
        return REGISTERED_UNIT_APAR_DB.filter((unit) => unit.purchase_key === groupKey);
    }

    function getSelectedRegisteredUnitIds() {
        if (!serviceRegisteredUnitList) {
            return [];
        }

        return [...serviceRegisteredUnitList.querySelectorAll('.service-unit-checkbox:checked')]
            .map((checkbox) => Number(checkbox.value || 0))
            .filter((id) => id > 0);
    }

    function getSelectedRegisteredUnits() {
        const selectedIds = getSelectedRegisteredUnitIds();

        return selectedIds
            .map((id) => REGISTERED_UNIT_APAR_DB.find((unit) => Number(unit.id) === Number(id)))
            .filter(Boolean);
    }

    function suggestRefillForUnits(units) {
        if (!units.length) {
            return null;
        }

        const source = normalizeMatchText(units.map((unit) => `${unit.jenis_apar || ''} ${unit.produk_nama || ''}`).join(' '));
        if (!source) {
            return null;
        }

        return JENIS_REFILL_DB.find((refill) => {
            const nama = normalizeMatchText(refill.nama);
            const label = normalizeMatchText(refill.nama_label);

            return (nama && (source.includes(nama) || nama.includes(source)))
                || (label && source.includes(label));
        }) || null;
    }

    function getRegisteredUnitSubtotal(unit) {
        const kategori = serviceJenisLayanan && serviceJenisLayanan.value === 'service' ? 'service' : 'refill';

        if (kategori === 'service') {
            const paket = findServicePaketById(servicePaketId?.value);
            return Number(paket?.harga || 0);
        }

        const refill = findServiceRefillById(serviceJenisRefill?.value);
        const ukuranKg = parseServiceSizeKg(unit.ukuran);

        return refill && ukuranKg > 0 ? ukuranKg * Number(refill.harga || 0) : 0;
    }

    function renderRegisteredUnitChecklist(options = {}) {
        if (!servicePurchaseGroup || !serviceRegisteredUnitList) {
            return;
        }

        const groupKey = servicePurchaseGroup.value || '';
        const units = getUnitsByPurchaseGroup(groupKey);
        const shouldReset = Boolean(options.resetSelection) || groupKey !== lastRenderedPurchaseGroup;
        const unitIdsInGroup = new Set(units.map((unit) => Number(unit.id)));
        const oldIdsInGroup = OLD_SELECTED_UNIT_APAR_IDS.filter((id) => unitIdsInGroup.has(Number(id)));
        const currentSelectedIds = shouldReset
            ? new Set(oldIdsInGroup.length ? oldIdsInGroup : units.map((unit) => Number(unit.id)))
            : new Set(getSelectedRegisteredUnitIds());

        serviceRegisteredUnitList.innerHTML = '';
        lastRenderedPurchaseGroup = groupKey;

        if (!groupKey || units.length === 0) {
            if (serviceRegisteredEmptyNote) {
                serviceRegisteredEmptyNote.classList.remove('hidden');
                serviceRegisteredEmptyNote.textContent = groupKey
                    ? 'Tidak ada Unit APAR pada tanggal pembelian ini.'
                    : 'Pilih tanggal pembelian terlebih dahulu untuk melihat daftar Unit APAR.';
            }
            if (serviceRegisteredCountNote) serviceRegisteredCountNote.classList.add('hidden');
            return;
        }

        if (serviceRegisteredEmptyNote) serviceRegisteredEmptyNote.classList.add('hidden');

        const suggestedRefill = suggestRefillForUnits(units);
        if (suggestedRefill && serviceJenisRefill && (shouldReset || !serviceJenisRefill.value)) {
            serviceJenisRefill.value = String(suggestedRefill.id);
        } else if (serviceJenisRefill && !serviceJenisRefill.value && JENIS_REFILL_DB.length === 1) {
            serviceJenisRefill.value = String(JENIS_REFILL_DB[0].id);
        }

        units.forEach((unit) => {
            const row = document.createElement('label');
            row.className = 'block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 has-[:checked]:border-blue-400 has-[:checked]:bg-blue-50';

            const subtotal = getRegisteredUnitSubtotal(unit);
            const subtotalText = subtotal > 0 ? fmt(subtotal) : (serviceJenisLayanan?.value === 'service' ? 'Pilih paket service' : 'Pilih jenis refil');
            const kode = escapeHtml(unit.kode || '-');
            const produkNama = escapeHtml(unit.produk_nama || '-');
            const jenisApar = escapeHtml(unit.jenis_apar || '-');
            const ukuran = escapeHtml(unit.ukuran || '-');
            const tglBeli = escapeHtml(unit.tgl_beli || '-');
            const masaBerlaku = escapeHtml(unit.masa_berlaku || '-');
            const statusUnit = escapeHtml(unit.status_unit || '-');

            row.innerHTML = `
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="service_unit_apar_ids[]" value="${unit.id}" class="service-unit-checkbox mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                            <p class="font-black text-slate-900">${kode}</p>
                            <p class="text-sm font-black text-blue-700">${subtotalText}</p>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-slate-700">${produkNama}</p>
                        <div class="mt-3 grid grid-cols-1 gap-2 text-xs font-semibold text-slate-500 sm:grid-cols-2">
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Jenis APAR:</span> ${jenisApar}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Ukuran:</span> ${ukuran}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Tanggal Beli:</span> ${tglBeli}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Tanggal Expired:</span> ${masaBerlaku}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Status:</span> ${statusUnit}</p>
                        </div>
                    </div>
                </div>
            `;

            const checkbox = row.querySelector('.service-unit-checkbox');
            checkbox.checked = currentSelectedIds.has(Number(unit.id));
            checkbox.addEventListener('change', updateServiceSummary);
            serviceRegisteredUnitList.appendChild(row);
        });
    }

    function findServiceRefillById(id) {
        return JENIS_REFILL_DB.find((item) => Number(item.id) === Number(id)) || null;
    }

    function findServicePaketById(id) {
        return SERVICE_PAKET_DB.find((item) => Number(item.id) === Number(id)) || null;
    }

    function syncRegisteredAutomaticChoices() {
        const isBeli = currentTab === 'beli';
        const isRefill = !serviceJenisLayanan || serviceJenisLayanan.value !== 'service';

        if (isBeli || getServiceUnitStatus() !== 'terdaftar') {
            return;
        }

        if (isRefill && serviceJenisRefill && !serviceJenisRefill.value) {
            const units = getSelectedRegisteredUnits().length
                ? getSelectedRegisteredUnits()
                : getUnitsByPurchaseGroup(servicePurchaseGroup?.value || '');
            const suggestedRefill = suggestRefillForUnits(units);

            if (suggestedRefill) {
                serviceJenisRefill.value = String(suggestedRefill.id);
            } else if (JENIS_REFILL_DB.length === 1) {
                serviceJenisRefill.value = String(JENIS_REFILL_DB[0].id);
            }
        }

        if (!isRefill && servicePaketId && !servicePaketId.value && SERVICE_PAKET_DB.length) {
            servicePaketId.value = String(SERVICE_PAKET_DB[0].id);
        }
    }

    function setServiceAlert(element, message) {
        if (!element) {
            return;
        }

        if (!message) {
            element.classList.add('hidden');
            element.textContent = '';
            return;
        }

        element.textContent = message;
        element.classList.remove('hidden');
    }

    function renderServicePackageDetails(paket, qty = 1) {
        if (!servicePackageNote || !servicePackageRincian) {
            return;
        }

        servicePackageRincian.innerHTML = '';

        if (!paket || currentTab === 'beli') {
            servicePackageNote.classList.add('hidden');
            return;
        }

        const detailTitle = document.createElement('p');
        detailTitle.className = 'text-sm font-black text-slate-900';
        detailTitle.textContent = `${paket.label ? paket.label + ' - ' : ''}${paket.nama} - ${fmt(paket.harga)} / unit`;
        servicePackageRincian.appendChild(detailTitle);

        const workTitle = document.createElement('p');
        workTitle.className = 'pt-2 text-xs font-black uppercase tracking-[0.18em] text-slate-400';
        workTitle.textContent = 'Pekerjaan Dalam Paket';
        servicePackageRincian.appendChild(workTitle);

        (paket.rincian || []).forEach((item) => {
            const row = document.createElement('p');
            row.className = 'text-sm font-semibold text-slate-700';
            row.textContent = '- ' + item;
            servicePackageRincian.appendChild(row);
        });

        if ((paket.peralatans || []).length) {
            const peralatanTitle = document.createElement('p');
            peralatanTitle.className = 'pt-2 text-xs font-black uppercase tracking-[0.18em] text-slate-400';
            peralatanTitle.textContent = `Estimasi Barang Dipakai (${qty} unit)`;
            servicePackageRincian.appendChild(peralatanTitle);

            (paket.peralatans || []).forEach((item) => {
                const row = document.createElement('p');
                row.className = 'text-sm font-semibold text-slate-700';
                const totalQty = Number(item.jumlah || 0) * Math.max(1, Number(qty || 1));
                row.textContent = `${item.nama} (${totalQty} pcs total)`;
                servicePackageRincian.appendChild(row);
            });
        }

        servicePackageNote.classList.remove('hidden');
    }

    function buildServiceState() {
        const kategori = serviceJenisLayanan && serviceJenisLayanan.value === 'service' ? 'service' : 'refill';
        const unitStatus = getServiceUnitStatus();
        const registeredUnits = unitStatus === 'terdaftar' ? getSelectedRegisteredUnits() : [];
        const qty = unitStatus === 'terdaftar' ? registeredUnits.length : Math.max(1, Number(serviceJumlahUnit?.value || 1));
        const ukuran = registeredUnits.length
            ? [...new Set(registeredUnits.map((unit) => String(unit.ukuran || '').trim()).filter(Boolean))].join(', ')
            : String(serviceUkuranApar?.value || '').trim();
        const ukuranKg = registeredUnits.length
            ? registeredUnits.reduce((total, unit) => total + parseServiceSizeKg(unit.ukuran), 0)
            : parseServiceSizeKg(ukuran);
        const metode = getSelectedServiceMethod();
        const refill = kategori === 'refill' ? findServiceRefillById(serviceJenisRefill?.value) : null;
        const paket = kategori === 'service' ? findServicePaketById(servicePaketId?.value) : null;
        const linkedRefill = kategori === 'service' && paket?.jenis_refill_id
            ? findServiceRefillById(paket.jenis_refill_id)
            : null;

        const state = {
            kategori,
            unitStatus,
            registeredUnits,
            qty,
            ukuran,
            ukuranKg,
            metode,
            refill,
            paket,
            linkedRefill,
            itemLabel: 'Belum dipilih',
            unitPrice: 0,
            totalPrice: 0,
            totalKg: 0,
            stockUnit: linkedRefill?.satuan_label || refill?.satuan_label || 'Kg',
            insufficientStock: false,
            lowStock: false,
            currentStockLabel: 'Pilih jenis refil untuk melihat stok.',
            afterStockLabel: 'Sisa stok setelah transaksi akan tampil di sini.',
        };

        if (kategori === 'refill') {
            state.itemLabel = refill?.nama_label || 'Belum dipilih';
            state.unitPrice = Number(refill?.harga || 0);
            state.totalKg = ukuranKg > 0 ? (registeredUnits.length ? ukuranKg : ukuranKg * qty) : 0;
            state.totalPrice = state.totalKg > 0 ? state.unitPrice * state.totalKg : 0;

            if (refill) {
                const currentStock = Number(refill.stok || 0);
                const remainingStock = currentStock - state.totalKg;
                state.currentStockLabel = `${refill.nama_label}: ${formatKg(currentStock)} ${refill.satuan_label}`;
                state.afterStockLabel = state.totalKg > 0
                    ? `Perkiraan sisa stok setelah pesanan: ${formatKg(remainingStock)} ${refill.satuan_label}`
                    : 'Jumlah kebutuhan refill akan muncul setelah ukuran dan unit dipilih.';
                state.insufficientStock = state.totalKg > 0 && remainingStock < 0;
                state.lowStock = state.totalKg > 0 && remainingStock <= Number(refill.stok_minimum || 0);
            }
        } else {
            state.itemLabel = paket
                ? `${paket.label ? paket.label + ' - ' : ''}${paket.nama}`
                : 'Belum dipilih';
            state.unitPrice = Number(paket?.harga || 0);
            state.totalPrice = state.unitPrice * qty;
            state.totalKg = linkedRefill && ukuranKg > 0 && Number(paket?.refill_ratio || 0) > 0
                ? (registeredUnits.length ? ukuranKg : ukuranKg * qty) * Number(paket.refill_ratio || 0)
                : 0;

            if (linkedRefill) {
                const currentStock = Number(linkedRefill.stok || 0);
                const remainingStock = currentStock - state.totalKg;
                state.currentStockLabel = `Estimasi ${linkedRefill.nama_label}: ${formatKg(currentStock)} ${linkedRefill.satuan_label}`;
                state.afterStockLabel = state.totalKg > 0
                    ? `Estimasi kebutuhan paket: ${formatKg(state.totalKg)} ${linkedRefill.satuan_label}. Stok perlengkapan dipotong saat service selesai.`
                    : 'Paket ini menggunakan perlengkapan sesuai standar service.';
                state.lowStock = state.totalKg > 0 && remainingStock <= Number(linkedRefill.stok_minimum || 0);
                state.insufficientStock = state.totalKg > 0 && remainingStock < 0;
            } else {
                state.currentStockLabel = 'Paket service akan memakai perlengkapan sesuai standar pekerjaan.';
                state.afterStockLabel = 'Stok perlengkapan akan berkurang saat admin mengonfirmasi service selesai.';
            }
        }

        return state;
    }

    function updateServiceSummary() {
        const state = buildServiceState();
        const metodeLabel = state.metode === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput';

        if (state.unitStatus === 'terdaftar' && serviceJumlahUnit) {
            serviceJumlahUnit.value = String(state.qty);
        }

        if (serviceJenisAparHidden) {
            serviceJenisAparHidden.value = state.registeredUnits.length
                ? [...new Set(state.registeredUnits.map((unit) => unit.jenis_apar || 'APAR'))].join(', ')
                : (state.ukuran ? `APAR ${state.ukuran}` : '');
        }

        if (serviceRefillPriceNote) {
            if (state.kategori === 'refill') {
                serviceRefillPriceNote.textContent = state.refill && state.unitPrice > 0
                    ? `Harga standar ${state.refill.nama_label}: ${fmt(state.unitPrice)} per ${state.refill.satuan_label}. Total = ukuran APAR x jumlah unit x harga standar.`
                    : 'Harga standar refil akan muncul otomatis saat jenis refil dan ukuran APAR dipilih.';
            } else {
                serviceRefillPriceNote.textContent = 'Paket service memakai harga standar per unit APAR dan rincian pekerjaan yang tetap.';
            }
        }

        if (servicePaketInfo && servicePaketInfoContent) {
            if (state.kategori === 'service' && state.paket) {
                servicePaketInfoContent.innerHTML = '';

                const heading = document.createElement('p');
                heading.className = 'text-sm font-black text-slate-900';
                heading.textContent = `${state.paket.label ? state.paket.label + ' - ' : ''}${state.paket.nama}`;
                servicePaketInfoContent.appendChild(heading);

                const pekerjaan = document.createElement('p');
                pekerjaan.className = 'text-sm font-semibold text-slate-700';
                pekerjaan.textContent = `Paket ini mencakup ${Math.max((state.paket.rincian || []).length, 1)} langkah pekerjaan standar untuk ${state.qty} unit APAR.`;
                servicePaketInfoContent.appendChild(pekerjaan);

                if ((state.paket.peralatans || []).length) {
                    const perlengkapan = document.createElement('p');
                    perlengkapan.className = 'text-sm font-semibold text-slate-700';
                    perlengkapan.textContent = 'Estimasi barang dipakai: ' + (state.paket.peralatans || [])
                        .map((item) => `${item.nama} (${Number(item.jumlah || 0) * state.qty} pcs total)`)
                        .join(', ') + '.';
                    servicePaketInfoContent.appendChild(perlengkapan);
                } else {
                    const fallback = document.createElement('p');
                    fallback.className = 'text-sm font-semibold text-slate-700';
                    fallback.textContent = 'Paket ini dominan berisi inspeksi dan pembersihan tanpa penggantian barang standar.';
                    servicePaketInfoContent.appendChild(fallback);
                }

                servicePaketInfo.classList.remove('hidden');
            } else {
                servicePaketInfo.classList.add('hidden');
                servicePaketInfoContent.innerHTML = '';
            }
        }

        renderServicePackageDetails(state.kategori === 'service' ? state.paket : null, state.qty);

        if (serviceSummaryCategory) serviceSummaryCategory.textContent = state.kategori === 'service' ? 'Service APAR' : 'Refill APAR';
        if (serviceSummaryStatusRow) serviceSummaryStatusRow.classList.toggle('hidden', false);
        if (serviceSummaryStatus) serviceSummaryStatus.textContent = state.unitStatus === 'terdaftar' ? 'APAR Terdaftar' : 'APAR Belum Terdaftar';
        if (serviceSummaryUnitRow) serviceSummaryUnitRow.classList.toggle('hidden', state.unitStatus !== 'terdaftar');
        if (serviceSummaryUnit) {
            const purchaseLabel = state.registeredUnits[0]?.purchase_label || '-';
            serviceSummaryUnit.textContent = state.registeredUnits.length
                ? `${state.registeredUnits.length} unit dari ${purchaseLabel}`
                : '-';
        }
        if (serviceSummaryItem) serviceSummaryItem.textContent = state.itemLabel;
        if (serviceSummarySize) serviceSummarySize.textContent = state.ukuran || '-';
        if (serviceSummaryQty) serviceSummaryQty.textContent = `${state.qty} unit`;
        if (serviceSummaryKg) {
            serviceSummaryKg.textContent = state.totalKg > 0
                ? `${formatKg(state.totalKg)} ${state.stockUnit}`
                : '-';
        }
        if (serviceSummaryMethod) serviceSummaryMethod.textContent = metodeLabel;
        if (serviceSummaryPrice) serviceSummaryPrice.textContent = fmt(state.totalPrice || 0);
        if (serviceStockCurrent) serviceStockCurrent.textContent = state.currentStockLabel;
        if (serviceStockAfter) serviceStockAfter.textContent = state.afterStockLabel;
        if (serviceRegisteredCountNote) {
            if (state.unitStatus === 'terdaftar' && state.registeredUnits.length) {
                serviceRegisteredCountNote.textContent = `${state.registeredUnits.length} Unit APAR dipilih - Total ${fmt(state.totalPrice || 0)}`;
                serviceRegisteredCountNote.classList.remove('hidden');
            } else {
                serviceRegisteredCountNote.classList.add('hidden');
            }
        }

        if (currentTab === 'beli') {
            setServiceAlert(serviceStockWarning, '');
            setServiceAlert(serviceLowStockWarning, '');
            return;
        }

        if (state.kategori === 'refill' && state.insufficientStock) {
            setServiceAlert(serviceStockWarning, `Stok refill ${state.refill?.nama_label || 'yang dipilih'} tidak mencukupi.`);
        } else {
            setServiceAlert(serviceStockWarning, '');
        }

        if (state.lowStock) {
            const targetName = state.kategori === 'service'
                ? (state.linkedRefill?.nama_label || 'refill terkait')
                : (state.refill?.nama_label || 'refill yang dipilih');
            setServiceAlert(serviceLowStockWarning, `Stok refill ${targetName} hampir habis.`);
        } else {
            setServiceAlert(serviceLowStockWarning, '');
        }
    }

    function updateServiceFormState() {
        const isBeli = currentTab === 'beli';
        const isRefill = !serviceJenisLayanan || serviceJenisLayanan.value !== 'service';
        const unitStatus = getServiceUnitStatus();
        const isRegisteredService = !isBeli && unitStatus === 'terdaftar';
        const showRefillFields = !isBeli && isRefill;
        const showServiceFields = !isBeli && !isRefill;

        if (serviceRefillFields) {
            serviceRefillFields.classList.toggle('hidden', !showRefillFields);
        }
        if (serviceServiceFields) {
            serviceServiceFields.classList.toggle('hidden', !showServiceFields);
        }
        if (serviceUnitStatusFields) {
            serviceUnitStatusFields.classList.toggle('hidden', isBeli);
        }
        if (serviceRegisteredUnitFields) {
            serviceRegisteredUnitFields.classList.toggle('hidden', !isRegisteredService);
        }
        if (serviceManualSizeField) {
            serviceManualSizeField.classList.toggle('hidden', isRegisteredService);
        }
        if (serviceQuantityField) {
            serviceQuantityField.classList.toggle('hidden', isRegisteredService);
        }

        if (isRegisteredService) {
            syncRegisteredAutomaticChoices();
            renderRegisteredUnitChecklist();
        } else if (serviceRegisteredUnitList) {
            serviceRegisteredUnitList.innerHTML = '';
            lastRenderedPurchaseGroup = null;
            if (serviceRegisteredEmptyNote) serviceRegisteredEmptyNote.classList.remove('hidden');
        }

        if (serviceJenisLayanan) serviceJenisLayanan.required = !isBeli;
        if (serviceUkuranApar) serviceUkuranApar.required = !isBeli && !isRegisteredService;
        if (serviceJumlahUnit) {
            serviceJumlahUnit.required = !isBeli;
            serviceJumlahUnit.readOnly = isRegisteredService;
            if (isRegisteredService) {
                serviceJumlahUnit.value = String(Math.max(1, getSelectedRegisteredUnits().length || getUnitsByPurchaseGroup(servicePurchaseGroup?.value || '').length || 1));
            }
            serviceJumlahUnit.classList.toggle('bg-slate-100', isRegisteredService);
            serviceJumlahUnit.classList.toggle('text-slate-500', isRegisteredService);
        }
        serviceUnitStatusRadios.forEach((radio) => {
            radio.required = !isBeli;
            radio.disabled = isBeli;
        });
        if (servicePurchaseGroup) {
            servicePurchaseGroup.required = isRegisteredService;
            servicePurchaseGroup.disabled = !isRegisteredService;
        }
        if (serviceJenisRefill) {
            serviceJenisRefill.required = showRefillFields;
            serviceJenisRefill.disabled = !isRefill;
        }
        if (servicePaketId) {
            servicePaketId.required = showServiceFields;
            servicePaketId.disabled = isRefill;
        }
        serviceMethodRadios.forEach((radio) => {
            radio.required = !isBeli;
        });

        updateServiceSummary();
    }

    window.switchTab = function(tab) {
        currentTab = tab;
        const tipeInput = document.getElementById('inp-tipe');
        if (tipeInput) tipeInput.value = tab;

        const isB = tab === 'beli';
        const cardBeli = document.getElementById('card-beli');
        const iconBeli = document.getElementById('icon-beli');
        const cardService = document.getElementById('card-service');
        const iconService = document.getElementById('icon-service');
        const sectionBeliItems = document.getElementById('section-beli-items');
        const sectionBeliSidebar = document.getElementById('section-beli-sidebar');
        const sectionServiceSidebar = document.getElementById('section-service-sidebar');
        const sectionServiceInline = document.getElementById('section-service-inline');

        if (cardBeli) {
            cardBeli.className = 'layanan-card' + (isB ? ' active-beli' : '');
        }
        if (iconBeli) {
            iconBeli.className = 'layanan-icon beli' + (isB ? ' active' : '');
        }
        if (cardService) {
            cardService.className = 'layanan-card' + (!isB ? ' active-service' : '');
        }
        if (iconService) {
            iconService.className = 'layanan-icon service' + (!isB ? ' active' : '');
        }

        if (sectionBeliItems) sectionBeliItems.classList.toggle('hidden', !isB);
        if (sectionServiceSidebar) sectionServiceSidebar.classList.toggle('hidden', !isB);
        if (sectionBeliSidebar) sectionBeliSidebar.classList.toggle('hidden', isB);
        if (sectionServiceInline) sectionServiceInline.classList.toggle('hidden', isB);

        if (isB) {
            setShippingMethod(inpMetodePengiriman.value || 'pickup');
        } else {
            inpMetodePengiriman.value = 'pickup';
            inpOngkir.value = '0';
            inpShippingDistance.value = '0';
        }

        updateServiceFormState();
    };

    function updateEmptyItemsMsg() {
        if (CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS) {
            if (emptyItemsMsg) emptyItemsMsg.classList.add('hidden');
            return;
        }
        const hasItems = itemsContainer.querySelectorAll('.item-row').length > 0;
        if (emptyItemsMsg) emptyItemsMsg.classList.toggle('hidden', hasItems);
    }

    function getJenisAparName(produk) {
        return produk?.jenis_apar?.nama || produk?.jenisApar?.nama || 'Lainnya';
    }

    function createRow() {
        const clone = document.importNode(tmplRow.content, true);
        const row = clone.querySelector('.item-row');

        const selJenis = row.querySelector('.sel-jenis');
        const selKap = row.querySelector('.sel-kapasitas');
        const selMerek = row.querySelector('.sel-merek');
        const lblHarga = row.querySelector('.lbl-harga');
        const inpId = row.querySelector('.inp-produk-id');
        const inpQty = row.querySelector('.inp-qty');
        const lblSub = row.querySelector('.lbl-subtotal');
        const btnHapus = row.querySelector('.btn-hapus');

        inpId.name = `items[${rowIndex}][produk_id]`;
        inpQty.name = `items[${rowIndex}][jumlah]`;
        rowIndex++;

        const types = [...new Set(PRODUK_DB.map((p) => getJenisAparName(p)))].sort();
        types.forEach((t) => {
            const o = document.createElement('option');
            o.value = t; o.textContent = t;
            selJenis.appendChild(o);
        });

        selJenis.addEventListener('change', function() {
            invalidatePricingByItemChange();
            selKap.innerHTML = '<option>-</option>';
            selMerek.innerHTML = '<option>-</option>';
            disableSelect(selKap); disableSelect(selMerek);
            inpQty.disabled = true;
            inpId.value = '';
            row.dataset.harga = 0;
            lblHarga.textContent = 'Rp 0';
            lblSub.textContent = 'Rp 0';
            if (!this.value) { recalcGlobal(); return; }
            const subset = PRODUK_DB.filter((p) => getJenisAparName(p) === this.value);
            const kaps = [...new Set(subset.map((p) => p.kapasitas || '-'))].sort();
            selKap.innerHTML = '<option value="">Pilih</option>';
            kaps.forEach((k) => {
                const o = document.createElement('option');
                o.value = k; o.textContent = k;
                selKap.appendChild(o);
            });
            enableSelect(selKap);
        });

        selKap.addEventListener('change', function() {
            invalidatePricingByItemChange();
            selMerek.innerHTML = '<option>-</option>';
            disableSelect(selMerek);
            inpQty.disabled = true;
            inpId.value = '';
            row.dataset.harga = 0;
            lblHarga.textContent = 'Rp 0';
            lblSub.textContent = 'Rp 0';
            if (!this.value) { recalcGlobal(); return; }
            const subset = PRODUK_DB.filter(
                (p) => getJenisAparName(p) === selJenis.value && (p.kapasitas || '-') === this.value,
            );
            selMerek.innerHTML = '<option value="">Pilih</option>';
            [...new Set(subset.map((p) => p.merek || 'Standar'))].sort().forEach((m) => {
                const o = document.createElement('option');
                o.value = m; o.textContent = m;
                selMerek.appendChild(o);
            });
            enableSelect(selMerek);
        });

        selMerek.addEventListener('change', function() {
            invalidatePricingByItemChange();
            if (!this.value) {
                inpId.value = '';
                row.dataset.harga = 0;
                lblHarga.textContent = 'Rp 0';
                lblSub.textContent = 'Rp 0';
                inpQty.disabled = true;
                recalcGlobal();
                return;
            }
            const match = PRODUK_DB.find(
                (p) =>
                    getJenisAparName(p) === selJenis.value &&
                    (p.kapasitas || '-') === selKap.value &&
                    (p.merek || 'Standar') === this.value,
            );
            if (match) {
                inpId.value = match.id;
                row.dataset.harga = match.harga;
                row.dataset.nama = match.nama;
                lblHarga.textContent = fmt(match.harga);
                inpQty.disabled = false;
                inpQty.classList.remove('bg-slate-100', 'text-slate-400');
                inpQty.classList.add('bg-slate-50');
                recalcGlobal();
            }
        });

        inpQty.addEventListener('input', function() {
            invalidatePricingByItemChange();
            recalcLine();
        });

        function recalcLine() {
            const h = parseInt(row.dataset.harga || 0, 10);
            const q = parseInt(inpQty.value || 0, 10);
            lblSub.textContent = fmt(h * q);
            recalcGlobal();
        }

        btnHapus.addEventListener('click', function() {
            if (itemsContainer.querySelectorAll('.item-row').length <= 1) return;
            invalidatePricingByItemChange();
            row.remove();
            recalcGlobal();
            refreshDeleteBtns();
            updateEmptyItemsMsg();
        });

        itemsContainer.appendChild(row);
        refreshDeleteBtns();
        updateEmptyItemsMsg();
    }

    function recalcGlobal() {
        let total = 0;

        if (CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS) {
            getSelectedItems().forEach((item) => {
                total += Number(item.harga || 0) * Number(item.jumlah || 0);
            });
        } else if (PREFILLED_ORDER_ITEMS.length > 0) {
            getSelectedItems().forEach((item) => {
                total += Number(item.harga || 0) * Number(item.jumlah || 0);
            });
        } else {
            itemsContainer.querySelectorAll('.item-row').forEach((r) => {
                const h = parseInt(r.dataset.harga || 0, 10);
                const q = parseInt(r.querySelector('.inp-qty').value || 0, 10);
                total += h * q;
            });
        }

        normalTotal = total;
        syncNegotiationEligibility();
        syncDisplayedTotal();
    }

    function refreshDeleteBtns() {
        const btns = itemsContainer.querySelectorAll('.btn-hapus');
        const hasMultiple = btns.length > 1;
        btns.forEach((b) => {
            b.disabled = !hasMultiple;
        });
    }

    function buildNegotiationWhatsAppMessage(kodePesanan = '') {
        updateCombinedAddress();
        const nama = inpNama.value.trim();
        const noWa = normalizePhone(inpNoWa.value);
        const alamatMaps = inpAlamatMaps.value.trim();
        const alamatDetail = inpAlamatDetail.value.trim();
        const perusahaan = inpPerusahaan.value.trim();
        const items = getSelectedItems();
        const selectedBank = inpBank.value;
        const selectedBankName = getSelectedBankName();
        const hargaUsulan = parseInt(inpHargaDeal.value || 0, 10) || 0;
        const identity = perusahaan ? `${nama} (${perusahaan})` : nama;
        const totalSistem = normalTotal + (shippingMethod === 'diantar' ? shippingCost : 0);
        const itemLinesFormatted = items.map((item) => {
            return `* ${item.jenis} ${item.kapasitas} (${item.merek}) x${item.jumlah}\n  Harga: ${fmt(item.harga * item.jumlah)}`;
        }).join('\n');

        let pengajuanSection = '';
        if (hargaUsulan > 0) {
            const selisih = hargaUsulan - totalSistem;
            pengajuanSection = `\n*Harga Usulan di Sistem*\n${fmt(hargaUsulan)}\n(Selisih dari total normal: ${selisih >= 0 ? '+' : ''}${fmt(selisih)})\n`;
        }

        return `Halo Admin, saya ingin konsultasi dan negosiasi harga pesanan APAR.

*Data Pelanggan*
Nama: ${identity}
No WA: ${noWa}
Alamat: ${alamatMaps}${alamatDetail ? ` | Detail: ${alamatDetail}` : ''}${kodePesanan ? `\nRef: ${kodePesanan}` : ''}

*Detail Pesanan*
${itemLinesFormatted}

*Pengiriman*
Metode: ${shippingMethod === 'diantar' ? 'Diantar' : 'Ambil Sendiri'}
${shippingMethod === 'diantar' ? `Ongkir: ${fmt(shippingCost)}\n` : ''}Bank Tujuan: ${selectedBankName}
*Total Normal Sistem: ${fmt(totalSistem)}*${pengajuanSection}
Mohon informasinya. Terima kasih.`;
    }

    async function submitNegotiationRequest() {
        updateCombinedAddress();
        const nama = inpNama.value.trim();
        const noWa = normalizePhone(inpNoWa.value);
        const alamatMaps = inpAlamatMaps.value.trim();
        const alamatDetail = inpAlamatDetail.value.trim();
        const perusahaan = inpPerusahaan.value.trim();
        const items = getSelectedItems();
        const hargaUsulan = parseInt(inpHargaDeal.value || 0, 10);
        const selectedBank = inpBank.value;
        const totalQty = getSelectedItemQuantityTotal();

        if (!nama || !noWa || !alamatMaps || !alamatDetail) {
            alert('Lengkapi Nama, Nomor WhatsApp, Alamat Maps, dan Detail Alamat terlebih dahulu.');
            return;
        }
        if (!items.length) {
            alert('Pilih minimal satu produk sebelum melanjutkan.');
            return;
        }
        if (totalQty < 10) {
            alert(`Harga usulan hanya bisa dipakai jika total pembelian minimal 10 unit. Saat ini jumlah pesanan Anda ${totalQty} unit.`);
            return;
        }
        if (!hargaUsulan || hargaUsulan <= 0) {
            alert('Masukkan harga usulan yang valid sebelum dikirim ke sistem.');
            return;
        }
        if (shippingMethod === 'diantar' && !shippingQuoteReady) {
            alert('Silakan cek ongkir Ekspedisi terlebih dahulu.');
            return;
        }
        if (!selectedBank) {
            showBankError('Pilih bank tujuan terlebih dahulu untuk melanjutkan.');
            return;
        }

        const targetBtn = btnSubmitNego;
        targetBtn.disabled = true;
        const originalLabel = targetBtn.innerHTML;
        targetBtn.innerHTML = '<svg class="w-5 h-5 animate-spin inline-block mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...';

        let kodePesanan = '';
        try {
            const response = await fetch(ASK_WHATSAPP_URL, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify({ nama, no_wa: noWa, alamat_maps: alamatMaps, alamat_detail: alamatDetail, alamat_provinsi: document.getElementById('inp-provinsi')?.value, alamat_kota: document.getElementById('inp-kota')?.value, alamat_kecamatan: document.getElementById('inp-kecamatan')?.value, alamat_kode_pos: document.getElementById('inp-kodepos')?.value, alamat_lat: inpAlamatLat.value || null, alamat_lng: inpAlamatLng.value || null, metode_pengiriman: shippingMethod, ongkir: shippingMethod === 'diantar' ? shippingCost : 0, perusahaan, harga_usulan: hargaUsulan, sumber_negosiasi: 'sistem', bank: selectedBank, items: items.map((item) => ({ produk_id: item.produk_id, jumlah: item.jumlah })) }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Gagal menyimpan data.');
            kodePesanan = String(data.data?.kode_pesanan || '').trim();
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan. Silakan coba lagi.');
            targetBtn.disabled = false;
            targetBtn.innerHTML = originalLabel;
            return;
        }

        alert(`Harga usulan berhasil dikirim ke sistem dengan referensi ${kodePesanan || 'pesanan baru'}. Sekarang admin/pemilik bisa meninjau dan meng-ACC pengajuan Anda. Jika disetujui, gunakan kode penawaran untuk melanjutkan transaksi.`);
        targetBtn.disabled = false;
        targetBtn.innerHTML = originalLabel;
    }

    btnAskWhatsApp.addEventListener('click', function() {
        const message = buildNegotiationWhatsAppMessage();
        window.open(`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(message)}`, '_blank');
    });

    if (btnSubmitNego) {
        btnSubmitNego.addEventListener('click', submitNegotiationRequest);
    }

    if (inpDisplayHargaUsulan) {
        inpDisplayHargaUsulan.addEventListener('input', function() {
            const raw = this.value.replace(/\D/g, '');
            const val = parseInt(raw || 0, 10);
            this.value = val > 0 ? val.toLocaleString('id-ID') : '';
            inpHargaDeal.value = String(val);
            syncDisplayedTotal();
        });
    }

    btnCheckNegoCode.addEventListener('click', checkNegoCode);

    inpKodeNego.addEventListener('input', function() {
        resetDealValidation(true);
        if (!inpKodeNego.value.trim()) {
            negoCodeError.classList.add('hidden');
        }
    });

    orderForm.addEventListener('submit', function(event) {
        const isBeli = currentTab === 'beli';
        const hasDirectProductSelection = PREFILLED_ORDER_ITEMS.length > 0 || hasSelectedProduct();

        if (isBeli && !(CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS) && !hasDirectProductSelection) {
            event.preventDefault();
            window.location.href = IS_AUTHENTICATED ? PRODUCT_PAGE_URL : LOGIN_PAGE_URL;
            return;
        }

        inpSubmitSource.value = 'normal';
        updateCombinedAddress();

        if (!inpAlamatMaps.value.trim() || !inpAlamatDetail.value.trim()) {
            alert('Alamat Maps dan detail alamat wajib diisi.');
            event.preventDefault();
            return;
        }

        if (!isBeli) {
            const serviceState = buildServiceState();

            document.querySelectorAll('[name^="items["]').forEach((field) => { field.disabled = true; });
            inpIsNegoDeal.value = '0';
            inpTipeHarga.value = 'normal';
            inpHargaDeal.value = '';
            inpBank.value = '';
            inpOngkir.value = '0';
            inpShippingDistance.value = '0';
            document.getElementById('inp-use-cart-checkout').value = '0';

            if (serviceState.unitStatus === 'terdaftar' && !servicePurchaseGroup?.value) {
                alert('Pilih Tanggal Pembelian APAR terlebih dahulu.');
                event.preventDefault();
                return;
            }

            if (serviceState.unitStatus === 'terdaftar' && serviceState.registeredUnits.length < 1) {
                alert('Minimal satu Unit APAR wajib dicentang.');
                event.preventDefault();
                return;
            }

            if (!serviceState.ukuran) {
                alert(serviceState.unitStatus === 'terdaftar'
                    ? 'Unit APAR terdaftar belum memiliki data ukuran. Hubungi admin atau gunakan opsi APAR Belum Terdaftar.'
                    : 'Pilih ukuran APAR terlebih dahulu.');
                event.preventDefault();
                return;
            }

            if (serviceState.kategori === 'refill' && !serviceJenisRefill?.value) {
                alert(serviceState.unitStatus === 'terdaftar'
                    ? 'Jenis refil otomatis belum ditemukan dari Unit APAR terdaftar. Pastikan master data Jenis Refil sudah sesuai dengan jenis APAR.'
                    : 'Pilih jenis refil terlebih dahulu.');
                event.preventDefault();
                return;
            }

            if (serviceState.kategori === 'service' && !servicePaketId?.value) {
                alert(serviceState.unitStatus === 'terdaftar'
                    ? 'Paket service standar belum tersedia. Isi atau aktifkan paket service terlebih dahulu di data admin.'
                    : 'Pilih paket service terlebih dahulu.');
                event.preventDefault();
                return;
            }

            if (!serviceState.totalPrice || serviceState.totalPrice <= 0) {
                alert(serviceState.unitStatus === 'terdaftar'
                    ? 'Harga otomatis untuk Unit APAR terdaftar belum tersedia. Pastikan harga standar refil atau paket service sudah terisi.'
                    : 'Harga layanan untuk pilihan ini belum tersedia.');
                event.preventDefault();
                return;
            }

            if (serviceState.kategori === 'refill' && serviceState.insufficientStock) {
                alert('Stok refill ' + (serviceState.refill?.nama_label || 'yang dipilih') + ' tidak mencukupi.');
                event.preventDefault();
                return;
            }

            if (serviceJenisAparHidden) {
                serviceJenisAparHidden.value = serviceState.registeredUnits.length
                    ? [...new Set(serviceState.registeredUnits.map((unit) => unit.jenis_apar || 'APAR'))].join(', ')
                    : (serviceState.ukuran ? `APAR ${serviceState.ukuran}` : '');
            }

            return;
        }

        if (shippingMethod === 'diantar') {
            if (!shippingQuoteReady) {
                alert('Silakan hitung ongkir Ekspedisi terlebih dahulu.');
                event.preventDefault();
                return;
            }
            inpOngkir.value = String(shippingCost);
            inpShippingDistance.value = String(shippingDistanceKm);
        } else {
            inpOngkir.value = '0';
            inpShippingDistance.value = '0';
        }

        document.getElementById('inp-use-cart-checkout').value = CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS ? '1' : '0';

        if (!inpBank.value) {
            showBankError('Pilih bank tujuan terlebih dahulu untuk melanjutkan pemesanan.');
            event.preventDefault();
            return;
        }

        if (dealValidated) {
            inpKodeNego.value = validatedNegoCode;
            inpHargaDeal.value = String(validatedDealPrice);
            inpIsNegoDeal.value = '1';
            inpTipeHarga.value = 'deal';
            return;
        }

        inpIsNegoDeal.value = '0';
        inpTipeHarga.value = 'normal';
        inpHargaDeal.value = '';
    });

    inpAlamatMaps.addEventListener('input', scheduleAddressSuggestSearch);
    inpAlamatMaps.addEventListener('focus', function() {
        if ((this.value || '').trim().length >= 3 && addressSuggestionItems.length) {
            addressHelper.classList.remove('hidden');
        }
    });
    inpAlamatMaps.addEventListener('blur', function() {
        setTimeout(hideAddressSuggestions, 300);
    });

    addressHelper.addEventListener('mousedown', function(event) {
        event.preventDefault();
        const target = event.target.closest('button[data-address-index]');
        if (!target) return;
        const idx = Number(target.dataset.addressIndex || -1);
        if (idx < 0 || idx >= addressSuggestionItems.length) return;
        const selected = addressSuggestionItems[idx];
        selectAddressSuggestion(String(selected.display_name || ''), Number(selected.lat || 0), Number(selected.lng || selected.lon || 0), selected);
    });

    inpAlamatDetail.addEventListener('input', function() {
        updateCombinedAddress();
        invalidateShippingQuote();
    });

    if (serviceJenisLayanan) {
        serviceJenisLayanan.addEventListener('change', updateServiceFormState);
    }
    serviceUnitStatusRadios.forEach((radio) => {
        radio.addEventListener('change', updateServiceFormState);
    });
    if (servicePurchaseGroup) {
        servicePurchaseGroup.addEventListener('change', function() {
            renderRegisteredUnitChecklist({ resetSelection: true });
            updateServiceSummary();
        });
    }
    if (serviceJenisRefill) {
        serviceJenisRefill.addEventListener('change', function() {
            renderRegisteredUnitChecklist();
            updateServiceSummary();
        });
    }
    if (servicePaketId) {
        servicePaketId.addEventListener('change', function() {
            renderRegisteredUnitChecklist();
            updateServiceFormState();
        });
    }
    if (serviceUkuranApar) {
        serviceUkuranApar.addEventListener('change', updateServiceFormState);
    }
    if (serviceJumlahUnit) {
        serviceJumlahUnit.addEventListener('input', updateServiceSummary);
        serviceJumlahUnit.addEventListener('change', updateServiceSummary);
    }
    serviceMethodRadios.forEach((radio) => {
        radio.addEventListener('change', updateServiceSummary);
    });
    if (btnSelectAllUnits) {
        btnSelectAllUnits.addEventListener('click', function() {
            serviceRegisteredUnitList?.querySelectorAll('.service-unit-checkbox').forEach((checkbox) => {
                checkbox.checked = true;
            });
            updateServiceSummary();
        });
    }
    if (btnClearAllUnits) {
        btnClearAllUnits.addEventListener('click', function() {
            serviceRegisteredUnitList?.querySelectorAll('.service-unit-checkbox').forEach((checkbox) => {
                checkbox.checked = false;
            });
            updateServiceSummary();
        });
    }

    if (btnPickup) btnPickup.addEventListener('click', function() { setShippingMethod('pickup'); });
    if (btnLalamove) btnLalamove.addEventListener('click', function() { setShippingMethod('diantar'); });
    if (btnCheckOngkir) btnCheckOngkir.addEventListener('click', checkShippingQuote);
    bankOptions.forEach((option) => {
        option.addEventListener('click', function() {
            setSelectedBank(this.dataset.bank || '');
        });
    });

    // Init map
    const initialLat = Number(inpAlamatLat.value || STORE_LAT || -6.2088);
    const initialLng = Number(inpAlamatLng.value || STORE_LNG || 106.8456);
    initLeafletMap(initialLat, initialLng);
    updateOrderCoord(initialLat, initialLng);

    // Init items
    if (btnTambahItem && itemsContainer && tmplRow) {
        createRow();
        btnTambahItem.addEventListener('click', function() {
            invalidatePricingByItemChange();
            createRow();
            recalcGlobal();
        });
    }

    if (inpAlamatMaps.value.trim().length >= 3 && !inpAlamatLat.value && !inpAlamatLng.value) {
        fetchAddressSuggestions(inpAlamatMaps.value.trim());
    }

    updateCombinedAddress();
    setShippingMethod(inpMetodePengiriman.value || 'pickup');
    setSelectedBank(inpBank.value || '');
    switchTab(document.getElementById('inp-tipe').value || 'beli');
    resetDealValidation();
    recalcGlobal();
})();
</script>

@endsection
