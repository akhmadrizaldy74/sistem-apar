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
    .summary-row{display:flex;justify-content:space-between;align-items:flex-start;gap:.875rem;padding:.45rem 0;font-size:.875rem}
    .summary-row > span:first-child{flex:1 1 auto;min-width:0;line-height:1.4}
    .summary-row > span:last-child{display:block;flex:0 1 58%;min-width:0;max-width:58%;text-align:right;line-height:1.35;white-space:normal;overflow-wrap:anywhere;font-variant-numeric:tabular-nums}
    .summary-row.total{align-items:flex-start;border-top:1.5px dashed #cbd5e1;padding-top:.85rem;margin-top:.35rem}
    .summary-row.total > span:last-child{flex-basis:62%;max-width:62%;line-height:1.15}
    .summary-value-total{display:block}
    .request-summary-card{padding:1rem 1.05rem}
    .choice-grid{display:grid;gap:.75rem}
    .choice-grid.shipping{grid-template-columns:repeat(1,minmax(0,1fr))}
    .choice-grid.bank{grid-template-columns:repeat(1,minmax(0,1fr))}
    @media(min-width:640px){.choice-grid.shipping{grid-template-columns:repeat(2,minmax(0,1fr))}.choice-grid.bank{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(min-width:1024px){.choice-grid.bank{grid-template-columns:repeat(3,minmax(0,1fr))}}
    .choice-card{width:100%;min-height:100%;padding:1rem 1.05rem;border:1.5px solid #e2e8f0;border-radius:1rem;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%);cursor:pointer;transition:all .18s ease;box-shadow:0 1px 2px rgba(15,23,42,.03)}
    .choice-card:hover{border-color:#fca5a5;background:#fff}
    .choice-card.active{border-color:#dc2626;background:linear-gradient(180deg,#fff5f5 0%,#fef2f2 100%);box-shadow:0 0 0 3px rgba(220,38,38,.08)}
    .choice-card-header{display:flex;align-items:flex-start;justify-content:space-between;gap:.9rem}
    .choice-card-copy{min-width:0}
    .choice-card-kicker{display:block;font-size:.63rem;font-weight:900;letter-spacing:.14em;text-transform:uppercase;color:#94a3b8}
    .choice-card-title{display:block;margin-top:.28rem;font-size:1rem;font-weight:900;line-height:1.2;color:#0f172a}
    .choice-card-subtitle{display:block;margin-top:.3rem;font-size:.77rem;font-weight:600;line-height:1.45;color:#64748b}
    .choice-card-indicator{width:1.15rem;height:1.15rem;border-radius:9999px;border:2px solid #cbd5e1;background:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .18s ease;margin-top:.08rem}
    .choice-card-indicator::after{content:'';width:.45rem;height:.45rem;border-radius:9999px;background:#dc2626;opacity:0;transform:scale(.7);transition:all .18s ease}
    .choice-card.active .choice-card-indicator{border-color:#dc2626;background:#fff}
    .choice-card.active .choice-card-indicator::after{opacity:1;transform:scale(1)}
    .shipping-card.active .choice-card-title,.bank-option.active .choice-card-title{color:#991b1b}
    .shipping-card.active .choice-card-subtitle,.bank-option.active .choice-card-subtitle{color:#7f1d1d}
    .address-suggestion-item{display:block;width:100%;text-align:left;padding:.7rem .85rem;border-bottom:1px solid #f1f5f9;background:#fff;transition:all .2s ease}
    .address-suggestion-item:last-child{border-bottom:0}
    .address-suggestion-item:hover{background:#fef2f2}
    .address-suggestion-title{display:block;font-size:.9rem;font-weight:700;color:#0f172a;line-height:1.35}
    .address-suggestion-subtitle{display:block;font-size:.72rem;font-weight:600;color:#64748b;line-height:1.35;margin-top:.15rem}
    .bank-option{padding:.8rem .9rem;min-height:auto}
    .bank-option .choice-card-title{font-size:1rem}
    .bank-option .choice-card-kicker{font-size:.6rem}
    .bank-option .choice-card-header{align-items:center}
    .shipping-action-row{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-top:.85rem}
    .btn-shipping-quote{display:inline-flex;align-items:center;gap:.45rem;padding:.62rem .95rem;border-radius:.85rem;background:#dc2626;color:#fff;font-size:.78rem;font-weight:900;letter-spacing:.04em;border:none;cursor:pointer;transition:all .18s ease;box-shadow:0 8px 20px rgba(220,38,38,.18)}
    .btn-shipping-quote:hover{background:#b91c1c}
    .btn-shipping-quote:disabled{opacity:.65;cursor:wait}
    .shipping-status-note{display:none;margin-top:.75rem;padding:.8rem .9rem;border-radius:.95rem;border:1px solid #e2e8f0;background:#f8fafc}
    .shipping-status-note.show{display:block}
    .shipping-status-note.compact{background:#fff;border-color:#e2e8f0}
    .shipping-status-note.success{border-color:#fecaca;background:#fff5f5}
    .shipping-status-note.error{border-color:#fecaca;background:#fef2f2}
    .shipping-status-label{display:block;font-size:.62rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8}
    .shipping-status-value{display:block;margin-top:.18rem;font-size:1rem;font-weight:900;line-height:1.2;color:#0f172a}
    .shipping-status-meta{display:block;margin-top:.2rem;font-size:.74rem;font-weight:700;line-height:1.4;color:#64748b}
    .shipping-status-detail{display:block;margin-top:.2rem;font-size:.74rem;font-weight:700;line-height:1.4;color:#64748b}
    .shipping-status-note.success .shipping-status-label,.shipping-status-note.success .shipping-status-value{color:#991b1b}
    .shipping-status-note.success .shipping-status-meta,.shipping-status-note.success .shipping-status-detail{color:#7f1d1d}
    .shipping-status-note.error .shipping-status-label,.shipping-status-note.error .shipping-status-value{color:#b91c1c}
    .shipping-status-note.error .shipping-status-meta{color:#991b1b}
    .btn-primary-action{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;border-radius:1rem;font-size:.875rem;font-weight:800;border:none;cursor:pointer;transition:all .2s ease}
    .btn-primary-action.wa{background:#16a34a;color:#fff;box-shadow:0 4px 14px rgba(22,163,74,0.35)}
    .btn-primary-action.wa:hover{background:#15803d;transform:translateY(-1px);box-shadow:0 6px 20px rgba(22,163,74,0.4)}
    .btn-primary-action.submit{background:#1e293b;color:#fff;box-shadow:0 4px 14px rgba(30,41,59,0.3)}
    .btn-primary-action.submit:hover{background:#0f172a;transform:translateY(-1px)}
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

@php
    $specialPriceRequestOpen = old('submit_source') === 'special_price_request'
        || $errors->has('harga_pengajuan')
        || $errors->has('catatan_pelanggan');
@endphp

<script>
    const PRODUK_DB = {!! json_encode($produks->load('jenisApar')) !!};

    const SHIPPING_DESTINATION_URL = '{{ route('rajaongkir.destination') }}';
    const SHIPPING_COST_URL = '{{ route('rajaongkir.cost') }}';
    const ADDRESS_SUGGEST_URL = '{{ route('order.address.suggest') }}';
    const WA_NUMBER = '{{ \App\Support\WhatsApp::companyNumber() }}';
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
            'merek' => (string) ($item->produk?->merek ?? 'FIREFIX'),
        ];
    })->values()) !!};
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const DEFAULT_MAP_LAT = -6.2088;
    const DEFAULT_MAP_LNG = 106.8456;
    const IS_AUTHENTICATED = {{ auth()->check() ? 'true' : 'false' }};
    const USE_AUTHENTICATED_CUSTOMER = {{ !empty($useAuthenticatedCustomer) ? 'true' : 'false' }};
    const PROFILE_HAS_SHIPPING_DESTINATION = {{ !empty($customerProfile['has_rajaongkir_destination']) ? 'true' : 'false' }};
    const CAN_USE_CART_CHECKOUT = {{ !empty($canUseCartCheckout) ? 'true' : 'false' }};
    const CART_HAS_ITEMS = {{ !empty($cartItemCount) ? 'true' : 'false' }};
    const PREFILLED_ORDER_ITEMS = {!! json_encode(($prefilledOrderItems ?? collect())->values()) !!};
    const PREFILLED_SERVICE_ORDER = {!! json_encode($prefillServiceOrder ?? null) !!};
    const INITIAL_PRODUCT_SUMMARY = {!! json_encode($orderSummary ?? []) !!};
    const INITIAL_SPECIAL_PRICE_REQUEST_OPEN = {{ $specialPriceRequestOpen ? 'true' : 'false' }};
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
        $rawStatusUnit = mb_strtolower(trim((string) ($unitApar->kondisi_awal ?? '')));
        $statusUnit = match ($rawStatusUnit) {
            'tidak_aktif' => 'Tidak Aktif',
            'perlu_servis' => 'Perlu Servis',
            'aktif', 'valid', 'layak', '' => 'Aktif',
            default => ucwords(str_replace('_', ' ', $rawStatusUnit)),
        };
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
            'status_unit' => $statusUnit,
            'label' => $label,
            'purchase_key' => $purchaseKey,
            'purchase_label' => $purchaseLabel,
            'needs_refill' => (bool) ($unitApar->getAttribute('needs_refill') ?? false),
            'refill_status_label' => (string) ($unitApar->getAttribute('refill_status_label') ?? 'Aman'),
            'is_refill_locked' => (bool) ($unitApar->getAttribute('is_refill_locked') ?? false),
            'refill_lock_message' => (string) ($unitApar->getAttribute('refill_lock_message') ?? ''),
        ];
    })->values()) !!};
    const OLD_SELECTED_UNIT_APAR_IDS = {!! json_encode(collect(old('service_unit_apar_ids', old('service_unit_apar_id') ? [old('service_unit_apar_id')] : (($prefillServiceOrder['selected_unit_ids'] ?? []) ?: [])))->map(fn ($id) => (int) $id)->filter()->values()) !!};
    const SERVICE_PAKET_DB = {!! json_encode(array_values($servicePackageCatalog ?? [])) !!};
    const SERVICE_MEDIA_DB = {!! json_encode(array_values($serviceMediaOptions ?? [])) !!};
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
        $prefillServiceOrder = $prefillServiceOrder ?? null;
        $selectedCheckoutMode = old('tipe_layanan', $prefillServiceOrder ? 'service' : 'beli');
        $orderSummary = $orderSummary ?? [
            'subtotalProduk' => 0,
            'totalUnit' => 0,
            'diskonPersen' => 0,
            'nominalDiskon' => 0,
            'ongkir' => 0,
            'totalPembayaran' => 0,
            'nextDiscountTier' => null,
            'nextDiscountPercent' => null,
            'nextDiscountUnitsNeeded' => null,
        ];
        $prefilledOrderTotal = (float) $prefilledOrderItems->sum(fn ($item) => ((float) ($item['harga'] ?? 0)) * ((int) ($item['jumlah'] ?? 0)));
        $prefilledOrderQty = (int) $prefilledOrderItems->sum(fn ($item) => (int) ($item['jumlah'] ?? 0));
        $selectedServiceMethod = old('service_metode_penanganan', $prefillServiceOrder ? 'dijemput' : 'dijemput');
        $selectedShippingMethod = old('metode_pengiriman', (($orderSummary['ongkir'] ?? 0) > 0 ? 'diantar' : 'pickup'));
        if ($selectedCheckoutMode === 'service' && ! old('metode_pengiriman')) {
            $selectedShippingMethod = $selectedServiceMethod === 'antar sendiri' ? 'pickup' : 'diantar';
        }
        $selectedShippingMethod = in_array($selectedShippingMethod, ['diantar', 'pickup', 'ambil_sendiri', 'diantar_internal'], true)
            ? $selectedShippingMethod
            : 'pickup';
        $selectedShippingMethod = in_array($selectedShippingMethod, ['pickup', 'ambil_sendiri'], true) ? 'pickup' : 'diantar';
        $selectedBank = old('bank_tujuan', old('bank', ''));
        $hasInitialDiscount = (int) ($orderSummary['diskonPersen'] ?? 0) > 0;
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

    <form method="POST" action="{{ route('order.store') }}" id="order-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="inp-submit-source" name="submit_source" value="normal">
    <input type="hidden" id="inp-tipe-harga" name="tipe_harga" value="normal">
    <input type="hidden" id="inp-metode-pengiriman" value="{{ $selectedShippingMethod }}">
    <input type="hidden" id="service-metode-penanganan-hidden" name="service_metode_penanganan" value="{{ $selectedServiceMethod }}">
    <input type="hidden" id="inp-bank" value="{{ $selectedBank }}">
    <input type="hidden" id="inp-ongkir" name="ongkir" value="{{ old('ongkir', (float) ($orderSummary['ongkir'] ?? 0)) }}">
    <input type="hidden" id="inp-rajaongkir-destination-id" name="rajaongkir_destination_id" value="{{ old('rajaongkir_destination_id', $customerProfile['rajaongkir_destination_id'] ?? '') }}">
    <input type="hidden" id="inp-rajaongkir-destination-label" name="rajaongkir_destination_label" value="{{ old('rajaongkir_destination_label', $customerProfile['rajaongkir_destination_label'] ?? '') }}">
    <input type="hidden" id="inp-shipping-courier" name="shipping_courier" value="{{ old('shipping_courier') }}">
    <input type="hidden" id="inp-shipping-service" name="shipping_service" value="{{ old('shipping_service') }}">
    <input type="hidden" id="inp-shipping-etd" name="shipping_etd" value="{{ old('shipping_etd') }}">
    <input type="hidden" id="inp-shipping-weight" name="shipping_weight" value="{{ old('shipping_weight', 0) }}">
    <input type="hidden" id="inp-shipping-distance" name="shipping_distance_km" value="{{ old('shipping_distance_km', 0) }}">
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

                <div class="mt-5 rounded-2xl border {{ !empty($profile['has_rajaongkir_destination']) ? 'border-emerald-200 bg-emerald-50/70' : 'border-amber-200 bg-amber-50' }} px-4 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] {{ !empty($profile['has_rajaongkir_destination']) ? 'text-emerald-700' : 'text-amber-700' }}">Lokasi Pengiriman</p>
                            <p class="mt-2 text-sm font-semibold text-slate-700">
                                {{ $profile['rajaongkir_destination_label'] ?: 'Lokasi pengiriman belum dipilih.' }}
                            </p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                            Perbarui Profil
                        </a>
                    </div>
                    @if(empty($profile['has_rajaongkir_destination']))
                        <p class="mt-3 text-sm font-semibold text-amber-800">
                            Lokasi pengiriman belum dapat digunakan untuk menghitung ongkir. Silakan perbarui alamat pengiriman Anda.
                        </p>
                    @endif
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
                    <label class="order-label">Cari Lokasi Pengiriman <span>*</span></label>
                    <input type="text" id="inp-location-search" value="{{ old('rajaongkir_destination_label', old('alamat_maps')) }}" required
                        placeholder="Contoh: Bandung, Jawa Barat"
                        class="order-input pr-10" autocomplete="off">
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-[38px] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <div id="address-suggestions" class="hidden mt-2 w-full max-h-56 overflow-auto rounded-xl border-2 border-red-500 bg-white shadow-xl"></div>
                    <p id="location-selection-helper" class="mt-2 text-xs font-semibold text-slate-500">
                        Pilih lokasi pengiriman yang sesuai agar biaya pengiriman bisa dihitung.
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="order-label">Alamat Terpilih <span>*</span></label>
                    <textarea id="inp-alamat-selected-display" rows="3" readonly
                        class="order-input resize-none bg-slate-50"
                        placeholder="Lokasi pengiriman yang dipilih akan tampil di sini">{{ old('alamat_maps') }}</textarea>
                </div>
            </div>

            <div class="mt-5" id="order-map-wrapper">
                    <label class="order-label">Konfirmasi Titik Lokasi</label>
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

            <div class="mt-5">
                <input type="hidden" name="alamat" id="inp-alamat-combined" value="{{ old('alamat') }}">
                <input type="hidden" id="inp-alamat-maps" name="alamat_maps" value="{{ old('alamat_maps') }}">
                <input type="hidden" name="alamat_lat" id="inp-alamat-lat" value="{{ old('alamat_lat') }}">
                <input type="hidden" name="alamat_lng" id="inp-alamat-lng" value="{{ old('alamat_lng') }}">
            </div>
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
        <input type="hidden" name="tipe_layanan" id="inp-tipe" value="{{ old('tipe_layanan', $prefillServiceOrder ? 'service' : 'beli') }}">
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
                                    @if($item->produk?->resolved_image_url)
                                        <img src="{{ $item->produk->resolved_image_url }}" alt="{{ $item->produk->nama }}">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-black text-slate-900">{{ $item->produk?->nama ?? 'Produk' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item->produk?->jenisApar?->nama ?? 'APAR' }} - {{ $item->produk?->kapasitas ?? '-' }} - {{ $item->produk?->merek ?? 'FIREFIX' }}</p>
                                    <p class="mt-2 text-xs font-semibold text-slate-400">Qty {{ $item->qty }} - @ Rp {{ number_format($item->harga, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-400">Subtotal</p>
                                    <p class="text-lg font-black text-red-600">Rp {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
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
                                        <img src="{{ $item['gambar_url'] ?? asset('storage/' . $item['gambar']) }}" alt="{{ $item['nama'] ?? 'Produk' }}">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-black text-slate-900">{{ $item['nama'] ?? 'Produk' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item['jenis'] ?? 'APAR' }} - {{ $item['kapasitas'] ?? '-' }} - {{ $item['merek'] ?? 'FIREFIX' }}</p>
                                    <p class="mt-2 text-xs font-semibold text-slate-400">Qty {{ (int) ($item['jumlah'] ?? 0) }} - @ Rp {{ number_format((float) ($item['harga'] ?? 0), 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-400">Subtotal</p>
                                    <p class="text-lg font-black text-red-600">Rp {{ number_format(((float) ($item['harga'] ?? 0)) * ((int) ($item['jumlah'] ?? 0)), 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
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

        @include('public.order.partials.service-form-manual')

            </div>

            <div class="lg:col-span-2 lg:sticky lg:top-24">
                <div id="section-service-sidebar" class="space-y-5">

                {{-- Shipping Method --}}
                <div class="order-section-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        </div>
                        <p id="method-card-title" class="text-sm font-black text-slate-800">Metode Pengiriman</p>
                    </div>
                    <div class="choice-grid shipping">
                        <label for="shipping-ambil-sendiri" class="flex-1 cursor-pointer">
                            <input id="shipping-ambil-sendiri" type="radio" name="metode_pengiriman" value="ambil_sendiri" class="sr-only" {{ $selectedShippingMethod === 'pickup' ? 'checked' : '' }}>
                            <div data-shipping-card="pickup" class="choice-card shipping-card">
                                <div class="choice-card-header">
                                    <div class="choice-card-copy">
                                        <span id="method-option-a-kicker" class="choice-card-kicker">Metode Pengiriman</span>
                                        <span id="method-option-a-title" class="choice-card-title">Ambil Sendiri</span>
                                        <span id="method-option-a-subtitle" class="choice-card-subtitle">Ambil pesanan langsung di lokasi kami tanpa biaya ongkir.</span>
                                    </div>
                                    <span class="choice-card-indicator" aria-hidden="true"></span>
                                </div>
                            </div>
                        </label>
                        <label for="shipping-diantar" class="flex-1 cursor-pointer">
                            <input id="shipping-diantar" type="radio" name="metode_pengiriman" value="diantar" class="sr-only" {{ $selectedShippingMethod === 'diantar' ? 'checked' : '' }}>
                            <div data-shipping-card="diantar" class="choice-card shipping-card">
                                <div class="choice-card-header">
                                    <div class="choice-card-copy">
                                        <span id="method-option-b-kicker" class="choice-card-kicker">Metode Pengiriman</span>
                                        <span id="method-option-b-title" class="choice-card-title">Diantar</span>
                                        <span id="method-option-b-subtitle" class="choice-card-subtitle">Pesanan dikirim ke alamat Anda dengan biaya pengiriman sesuai hasil cek ekspedisi.</span>
                                    </div>
                                    <span class="choice-card-indicator" aria-hidden="true"></span>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="shipping-action-row">
                    <button type="button" id="btn-check-ongkir"
                        class="btn-shipping-quote {{ $selectedShippingMethod === 'pickup' ? 'hidden' : '' }}"
                        {{ $selectedShippingMethod === 'pickup' ? 'disabled' : '' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Hitung Ongkir
                    </button>
                    </div>
                    <div id="shipping-status-note" class="shipping-status-note" aria-live="polite"></div>
                </div>

                <div class="order-section-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2m-2 0h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/></svg>
                        </div>
                        <p class="text-sm font-black text-slate-800">Pilih Bank Tujuan</p>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-500 mb-4">Pilih bank tujuan pembayaran.</p>
                    <div id="bank-options" class="choice-grid bank">
                        <label for="bank-bca" class="cursor-pointer">
                            <input id="bank-bca" type="radio" name="bank_tujuan" value="bca" class="sr-only" {{ $selectedBank === 'bca' ? 'checked' : '' }}>
                            <div data-bank-card="bca" class="choice-card bank-option">
                                <div class="choice-card-header">
                                    <div class="choice-card-copy">
                                        <span class="choice-card-kicker">Transfer Bank</span>
                                        <span class="choice-card-title">Bank BCA</span>
                                    </div>
                                    <span class="choice-card-indicator" aria-hidden="true"></span>
                                </div>
                            </div>
                        </label>
                        <label for="bank-mandiri" class="cursor-pointer">
                            <input id="bank-mandiri" type="radio" name="bank_tujuan" value="mandiri" class="sr-only" {{ $selectedBank === 'mandiri' ? 'checked' : '' }}>
                            <div data-bank-card="mandiri" class="choice-card bank-option">
                                <div class="choice-card-header">
                                    <div class="choice-card-copy">
                                        <span class="choice-card-kicker">Transfer Bank</span>
                                        <span class="choice-card-title">Bank Mandiri</span>
                                    </div>
                                    <span class="choice-card-indicator" aria-hidden="true"></span>
                                </div>
                            </div>
                        </label>
                        <label for="bank-bri" class="cursor-pointer">
                            <input id="bank-bri" type="radio" name="bank_tujuan" value="bri" class="sr-only" {{ $selectedBank === 'bri' ? 'checked' : '' }}>
                            <div data-bank-card="bri" class="choice-card bank-option">
                                <div class="choice-card-header">
                                    <div class="choice-card-copy">
                                        <span class="choice-card-kicker">Transfer Bank</span>
                                        <span class="choice-card-title">Bank BRI</span>
                                    </div>
                                    <span class="choice-card-indicator" aria-hidden="true"></span>
                                </div>
                            </div>
                        </label>
                    </div>
                    <p id="bank-selection-error" class="error-msg hidden"></p>
                </div>

                <div class="order-section-card p-6" id="section-promo-banyak">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800">Promo Pembelian Banyak</p>
                            <p class="text-[11px] font-semibold text-slate-500">Diskon otomatis diterapkan berdasarkan total unit APAR.</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-sm font-black text-emerald-900">Dapatkan Harga Lebih Murah!</p>
                        <p class="mt-1 text-xs font-semibold leading-relaxed text-emerald-700">Skema diskon kami berikan langsung ke pesanan Anda tanpa perlu menunggu persetujuan admin.</p>

                        <div id="active-promo-status" class="mt-4 p-3 rounded-xl border {{ $hasInitialDiscount ? 'border-emerald-200 bg-emerald-50 shadow-inner' : 'border-slate-200 bg-slate-50' }} text-center">
                            <p class="text-sm font-black {{ $hasInitialDiscount ? 'text-emerald-800' : 'text-slate-500' }}" id="promo-status-text">
                                {{ $hasInitialDiscount ? 'Diskon Aktif: ' . $orderSummary['diskonPersen'] . '%' : 'Belum ada diskon aktif.' }}
                            </p>
                            <p class="text-xs font-semibold text-emerald-600 mt-1" id="promo-status-subtext">
                                @if($hasInitialDiscount)
                                    @if(!empty($orderSummary['nextDiscountUnitsNeeded']) && !empty($orderSummary['nextDiscountPercent']))
                                        Tambah {{ $orderSummary['nextDiscountUnitsNeeded'] }} unit lagi untuk mendapatkan diskon {{ $orderSummary['nextDiscountPercent'] }}%.
                                    @else
                                        Anda sudah mendapatkan diskon maksimal.
                                    @endif
                                @elseif(!empty($orderSummary['nextDiscountUnitsNeeded']))
                                    Tambah {{ $orderSummary['nextDiscountUnitsNeeded'] }} unit lagi untuk mendapatkan diskon 5%.
                                @else
                                    Tambah unit lagi untuk mendapatkan diskon.
                                @endif
                            </p>
                        </div>

                        <div id="purchase-price-request-box" class="mt-4 {{ $prefilledOrderTotal > 5000000 ? '' : 'hidden' }}">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-black text-slate-900">Pengajuan Harga Pembelian</p>
                                        <p class="mt-1 text-xs font-semibold leading-relaxed text-slate-500">Ajukan harga khusus untuk total pembelian APAR jika subtotal produk sudah lebih dari Rp 5.000.000.</p>
                                    </div>
                                    <button type="button" id="btn-toggle-price-request" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-black uppercase tracking-wider text-slate-700 transition hover:bg-slate-100">
                                        Ajukan Harga Khusus
                                    </button>
                                </div>

                                <div id="purchase-price-request-form" class="mt-4 {{ $specialPriceRequestOpen ? '' : 'hidden' }}">
                                    <div class="summary-card request-summary-card">
                                        <div class="summary-row">
                                            <span class="text-slate-500 font-semibold">Subtotal Harga Dasar</span>
                                            <span id="request-base-subtotal" class="font-black text-slate-800">Rp {{ number_format((float) ($orderSummary['subtotalProduk'] ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                        <div class="summary-row text-emerald-700">
                                            <span class="font-semibold">Diskon Promo Otomatis</span>
                                            <span id="request-promo-discount" class="font-black">- Rp {{ number_format((float) ($orderSummary['nominalDiskon'] ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                        <div class="summary-row total">
                                            <span class="text-slate-500 font-semibold">Total Setelah Promo</span>
                                            <span id="request-after-promo" class="summary-value-total text-xl font-black text-red-600">Rp {{ number_format((float) ($orderSummary['totalSetelahPromo'] ?? ($orderSummary['totalPembayaran'] ?? 0)), 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="inp-harga-pengajuan" class="order-label">Harga Pengajuan <span>*</span></label>
                                            <input
                                                type="text"
                                                id="inp-harga-pengajuan"
                                                name="harga_pengajuan"
                                                value="{{ old('harga_pengajuan') }}"
                                                placeholder="Rp 0"
                                                inputmode="numeric"
                                                class="order-input"
                                            >
                                            <p id="request-price-hint" class="mt-2 text-[11px] font-semibold text-slate-500">Harga Pengajuan tidak boleh lebih besar dari subtotal harga dasar.</p>
                                            @error('harga_pengajuan')
                                                <p class="error-msg">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="inp-catatan-pelanggan" class="order-label">Catatan Pelanggan</label>
                                            <textarea
                                                id="inp-catatan-pelanggan"
                                                name="catatan_pelanggan"
                                                rows="3"
                                                placeholder="Opsional. Contoh: kebutuhan pengadaan kantor atau jumlah pembelian rutin."
                                                class="order-input resize-none"
                                            >{{ old('catatan_pelanggan') }}</textarea>
                                            @error('catatan_pelanggan')
                                                <p class="error-msg">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button
                                            type="submit"
                                            id="btn-submit-price-request"
                                            data-submit-source="special_price_request"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-800 transition hover:bg-slate-50"
                                        >
                                            Kirim Pengajuan Harga
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('public.order.partials.service-summary-manual')

                <div id="product-summary-card" class="order-section-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-black text-slate-800">Ringkasan Pesanan</p>
                    </div>

                    <div class="summary-card">
                        <div class="summary-row">
                            <span class="text-slate-500 font-semibold">Kategori</span>
                            <span id="lbl-category" class="font-bold text-slate-700">Pembelian Unit</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-slate-500 font-semibold">Produk</span>
                            <span id="lbl-product-items" class="font-bold text-slate-700 text-right">Belum dipilih</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-slate-500 font-semibold">Total Item</span>
                            <span id="lbl-item-count" class="font-bold text-slate-700">{{ (int) (($orderSummary['items'] ?? collect()) instanceof \Illuminate\Support\Collection ? ($orderSummary['items'] ?? collect())->count() : count($orderSummary['items'] ?? [])) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-slate-500 font-semibold">Subtotal Produk</span>
                            <span id="lbl-subtotal" class="font-bold text-slate-700">Rp {{ number_format((float) ($orderSummary['subtotalProduk'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-slate-500 font-semibold">Total Unit</span>
                            <span id="lbl-total-unit" class="font-bold text-slate-700">{{ (int) ($orderSummary['totalUnit'] ?? 0) }}</span>
                        </div>
                        <div class="summary-row text-emerald-700" id="discount-percent-row">
                            <span class="font-semibold">Diskon Promo</span>
                            <span id="lbl-discount-percent" class="font-bold">{{ (int) ($orderSummary['diskonPersen'] ?? 0) }}%</span>
                        </div>
                        <div class="summary-row text-emerald-700" id="discount-row">
                            <span class="font-semibold">Nominal Diskon</span>
                            <span id="lbl-discount" class="font-bold">- Rp {{ number_format((float) ($orderSummary['nominalDiskon'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-slate-500 font-semibold">Metode Pengiriman</span>
                            <span id="lbl-shipping-method" class="font-bold text-slate-700">{{ $selectedShippingMethod === 'diantar' ? 'Diantar' : 'Ambil Sendiri' }}</span>
                        </div>
                        <div id="shipping-summary-courier-row" class="summary-row hidden">
                            <span class="text-slate-500 font-semibold">Ekspedisi / Layanan</span>
                            <span id="lbl-shipping-courier" class="font-bold text-slate-700 text-right">-</span>
                        </div>
                        <div id="shipping-summary-etd-row" class="summary-row hidden">
                            <span class="text-slate-500 font-semibold">Estimasi</span>
                            <span id="lbl-shipping-etd" class="font-bold text-slate-700">-</span>
                        </div>
                        <div class="summary-row" id="ongkir-row">
                            <span class="text-slate-500 font-semibold">Biaya Pengiriman</span>
                            <span id="lbl-ongkir" class="font-bold text-slate-700">Rp {{ number_format((float) ($orderSummary['ongkir'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row total">
                            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Total Pembayaran</span>
                            <span id="lbl-total" class="summary-value-total text-xl font-black text-red-600">Rp {{ number_format((float) ($orderSummary['totalPembayaran'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-4 space-y-2.5">
                        <button type="submit" id="btn-submit" data-submit-source="normal" class="btn-primary-action submit w-full justify-center">
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
    let normalTotal = Number(INITIAL_PRODUCT_SUMMARY.subtotalProduk || 0);
    let promoDiscountPercent = Number(INITIAL_PRODUCT_SUMMARY.diskonPersen || 0);
    let promoDiscountNominal = Number(INITIAL_PRODUCT_SUMMARY.nominalDiskon || 0);
    let shippingMethod = (document.querySelector('input[name="metode_pengiriman"]:checked')?.value || 'ambil_sendiri') === 'diantar' ? 'diantar' : 'pickup';
    let shippingCost = shippingMethod === 'diantar' ? Number(INITIAL_PRODUCT_SUMMARY.ongkir || 0) : 0;
    let shippingDistanceKm = 0;
    let shippingRoundTripDistanceKm = 0;
    let shippingQuoteReady = shippingMethod === 'pickup' || shippingCost > 0;
    let shippingCourierCode = '';
    let shippingCourierName = '';
    let shippingServiceName = '';
    let shippingEtd = '';
    let shippingWeight = 0;
    let shippingRatePerKm = 0;
    let shippingMinimumCost = 0;
    let rowIndex = 0;
    let purchasePriceRequestOpen = INITIAL_SPECIAL_PRICE_REQUEST_OPEN;

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
    const productSummaryCard = document.getElementById('product-summary-card');
    const serviceSummaryCard = document.getElementById('service-summary-card');
    const sectionPromoBanyak = document.getElementById('section-promo-banyak');
    const activePromoStatus = document.getElementById('active-promo-status');
    const promoStatusText = document.getElementById('promo-status-text');
    const promoStatusSubtext = document.getElementById('promo-status-subtext');
    const purchasePriceRequestBox = document.getElementById('purchase-price-request-box');
    const purchasePriceRequestForm = document.getElementById('purchase-price-request-form');
    const btnTogglePriceRequest = document.getElementById('btn-toggle-price-request');
    const requestBaseSubtotal = document.getElementById('request-base-subtotal');
    const requestPromoDiscount = document.getElementById('request-promo-discount');
    const requestAfterPromo = document.getElementById('request-after-promo');
    const requestPriceHint = document.getElementById('request-price-hint');
    const inpHargaPengajuan = document.getElementById('inp-harga-pengajuan');
    const btnSubmitPriceRequest = document.getElementById('btn-submit-price-request');
    const shippingMethodRadios = [...document.querySelectorAll('input[name="metode_pengiriman"]')];
    const shippingCards = [...document.querySelectorAll('[data-shipping-card]')];
    const bankRadios = [...document.querySelectorAll('input[name="bank_tujuan"]')];
    const bankCards = [...document.querySelectorAll('[data-bank-card]')];
    const tierBadges = [...document.querySelectorAll('.tier-badge')];
    const btnCheckOngkir = document.getElementById('btn-check-ongkir');
    const inpBank = document.getElementById('inp-bank');
    const bankSelectionError = document.getElementById('bank-selection-error');
    const shippingStatusNote = document.getElementById('shipping-status-note');
    const inpMetodePengiriman = document.getElementById('inp-metode-pengiriman');
    const serviceMethodHidden = document.getElementById('service-metode-penanganan-hidden');
    const inpOngkir = document.getElementById('inp-ongkir');
    const inpShippingDestinationId = document.getElementById('inp-rajaongkir-destination-id');
    const inpShippingDestinationLabel = document.getElementById('inp-rajaongkir-destination-label');
    const inpShippingCourier = document.getElementById('inp-shipping-courier');
    const inpShippingService = document.getElementById('inp-shipping-service');
    const inpShippingEtd = document.getElementById('inp-shipping-etd');
    const inpShippingWeight = document.getElementById('inp-shipping-weight');
    const inpShippingDistance = document.getElementById('inp-shipping-distance');
    const orderForm = document.getElementById('order-form');
    const inpSubmitSource = document.getElementById('inp-submit-source');
    const inpTipeHarga = document.getElementById('inp-tipe-harga');
    const inpNama = document.getElementById('inp-nama');
    const inpNoWa = document.getElementById('inp-nowa');
    const locationSearchInput = document.getElementById('inp-location-search');
    const inpAlamatMaps = document.getElementById('inp-alamat-maps');
    const selectedAddressDisplay = document.getElementById('inp-alamat-selected-display');
    const inpAlamatDetail = document.getElementById('inp-alamat-detail');
    const inpAlamatCombined = document.getElementById('inp-alamat-combined');
    const inpAlamatLat = document.getElementById('inp-alamat-lat');
    const inpAlamatLng = document.getElementById('inp-alamat-lng');
    const inpPerusahaan = document.getElementById('inp-perusahaan');
    const addressHelper = document.getElementById('address-suggestions');
    const locationSelectionHelper = document.getElementById('location-selection-helper');
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
    const serviceJenisAparHidden = document.getElementById('service-jenis-apar-hidden');
    const serviceManualTypeField = document.getElementById('service-manual-type-field');
    const serviceJenisAparManual = document.getElementById('service-jenis-apar-manual');
    const serviceManualSizeField = document.getElementById('service-manual-size-field');
    const serviceRefillFields = document.getElementById('service-refill-fields');
    const serviceRefillSelectField = document.getElementById('service-refill-select-field');
    const serviceServiceFields = document.getElementById('service-service-fields');
    const serviceRefillItemsContainer = document.getElementById('service-refill-items');
    const serviceServiceItemsContainer = document.getElementById('service-service-items');
    const serviceRefillItemTemplate = document.getElementById('service-refill-item-template');
    const serviceServiceItemTemplate = document.getElementById('service-service-item-template');
    const btnAddRefillItem = document.getElementById('btn-add-refill-item');
    const btnAddServiceItem = document.getElementById('btn-add-service-item');
    const serviceRefillPriceNote = document.getElementById('service-refill-price-note');
    const serviceSummaryCategory = document.getElementById('service-summary-category');
    const serviceSummarySize = document.getElementById('service-summary-size');
    const serviceSummaryQty = document.getElementById('service-summary-qty');
    const serviceSummaryKg = document.getElementById('service-summary-kg');
    const serviceSummaryUsageLabel = document.getElementById('service-summary-usage-label');
    const serviceSummaryMethod = document.getElementById('service-summary-method');
    const serviceSummaryCourierRow = document.getElementById('service-summary-courier-row');
    const serviceSummaryCourier = document.getElementById('service-summary-courier');
    const serviceSummaryEtdRow = document.getElementById('service-summary-etd-row');
    const serviceSummaryEtd = document.getElementById('service-summary-etd');
    const serviceSummaryOngkir = document.getElementById('service-summary-ongkir');
    const serviceSummaryPriceLabel = document.getElementById('service-summary-price-label');
    const serviceSummaryPrice = document.getElementById('service-summary-price');
    const serviceSummaryItemCount = document.getElementById('service-summary-item-count');
    const serviceSummaryItems = document.getElementById('service-summary-items');
    const serviceSummaryEquipmentBox = document.getElementById('service-summary-equipment-box');
    const serviceSummaryEquipmentList = document.getElementById('service-summary-equipment-list');
    const serviceSummaryEquipmentCount = document.getElementById('service-summary-equipment-count');
    const serviceStockTitle = document.getElementById('service-stock-title');
    const serviceStockCurrent = document.getElementById('service-stock-current');
    const serviceStockAfter = document.getElementById('service-stock-after');
    const serviceStockWarning = document.getElementById('service-stock-warning');
    const serviceLowStockWarning = document.getElementById('service-low-stock-warning');
    const servicePackageNote = document.getElementById('service-package-note');
    const servicePackageRincian = document.getElementById('service-package-rincian');
    const serviceMethodRadios = [...document.querySelectorAll('input[name="service_metode_penanganan"]')];
    const shippingDestinationSearchInput = null;
    const shippingDestinationHelper = null;
    const shippingDestinationSuggestions = null;
    const lblProductItems = document.getElementById('lbl-product-items');
    const lblItemCount = document.getElementById('lbl-item-count');
    const lblShippingMethod = document.getElementById('lbl-shipping-method');
    const shippingSummaryCourierRow = document.getElementById('shipping-summary-courier-row');
    const lblShippingCourier = document.getElementById('lbl-shipping-courier');
    const shippingSummaryEtdRow = document.getElementById('shipping-summary-etd-row');
    const lblShippingEtd = document.getElementById('lbl-shipping-etd');
    const methodCardTitle = document.getElementById('method-card-title');
    const methodOptionAKicker = document.getElementById('method-option-a-kicker');
    const methodOptionATitle = document.getElementById('method-option-a-title');
    const methodOptionASubtitle = document.getElementById('method-option-a-subtitle');
    const methodOptionBKicker = document.getElementById('method-option-b-kicker');
    const methodOptionBTitle = document.getElementById('method-option-b-title');
    const methodOptionBSubtitle = document.getElementById('method-option-b-subtitle');
    let serviceRefillRowIndex = serviceRefillItemsContainer ? serviceRefillItemsContainer.querySelectorAll('.service-refill-item-row').length : 0;
    let serviceServiceRowIndex = serviceServiceItemsContainer ? serviceServiceItemsContainer.querySelectorAll('.service-service-item-row').length : 0;
    let lastRenderedPurchaseGroup = null;
    let addressSearchTimer = null;
    let addressSuggestionItems = [];
    let shippingDestinationSearchTimer = null;
    let shippingDestinationSuggestionItems = [];
    let orderMap = null;
    let orderMarker = null;

    shippingCourierCode = (inpShippingCourier?.value || '').trim();
    shippingServiceName = (inpShippingService?.value || '').trim();
    shippingEtd = (inpShippingEtd?.value || '').trim();
    shippingWeight = Number(inpShippingWeight?.value || 0);

    function fmt(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function moneyDigits(value) {
        return String(value || '').replace(/\D+/g, '');
    }

    function moneyValue(value) {
        const digits = moneyDigits(value);
        return digits ? Number(digits) : 0;
    }

    function formatMoneyInput(input) {
        if (!input) return 0;
        const value = moneyValue(input.value);
        input.value = value > 0 ? fmt(value) : '';
        return value;
    }

    function formatDistance(distance) {
        return Number(distance || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
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

    function syncBankCardState() {
        const selectedBank = inpBank.value || '';

        bankCards.forEach((card) => {
            const isActive = card.dataset.bankCard === selectedBank;
            card.classList.toggle('active', isActive);
        });
    }

    function setSelectedBank(bank) {
        inpBank.value = bank || '';
        const radio = document.querySelector(`input[name="bank_tujuan"][value="${bank}"]`);
        if (radio) radio.checked = true;
        syncBankCardState();
        hideBankError();
    }

    function updateCombinedAddress() {
        const maps = (inpAlamatMaps.value || '').trim();
        const detail = (inpAlamatDetail.value || '').trim();
        const combined = [maps, detail].filter(Boolean).join(' | Detail: ');
        inpAlamatCombined.value = combined;
    }

    function syncMethodCardCopy() {
        const isProductCheckout = currentTab === 'beli';

        if (methodCardTitle) {
            methodCardTitle.textContent = isProductCheckout ? 'Metode Pengiriman' : 'Metode Penanganan';
        }
        if (methodOptionAKicker) {
            methodOptionAKicker.textContent = isProductCheckout ? 'Metode Pengiriman' : 'Metode Penanganan';
        }
        if (methodOptionATitle) {
            methodOptionATitle.textContent = isProductCheckout ? 'Ambil Sendiri' : 'Antar Sendiri';
        }
        if (methodOptionASubtitle) {
            methodOptionASubtitle.textContent = isProductCheckout
                ? 'Ambil pesanan langsung di lokasi kami tanpa biaya ongkir.'
                : 'Anda mengantar unit APAR langsung ke lokasi PD. Anugrah Utama tanpa biaya penjemputan.';
        }
        if (methodOptionBKicker) {
            methodOptionBKicker.textContent = isProductCheckout ? 'Metode Pengiriman' : 'Metode Penanganan';
        }
        if (methodOptionBTitle) {
            methodOptionBTitle.textContent = isProductCheckout ? 'Diantar' : 'Dijemput';
        }
        if (methodOptionBSubtitle) {
            methodOptionBSubtitle.textContent = isProductCheckout
                ? 'Pesanan dikirim ke alamat Anda dengan biaya pengiriman sesuai hasil cek ekspedisi.'
                : 'Kami menjemput unit APAR ke alamat Anda dengan biaya penjemputan.';
        }
    }

    function shippingQuoteActionLabel() {
        return currentTab === 'beli' ? 'Hitung Ongkir' : 'Hitung Biaya Penjemputan';
    }

    function shippingQuoteLoadingLabel() {
        return currentTab === 'beli' ? 'Menghitung Ongkir' : 'Menghitung Biaya Penjemputan';
    }

    function shippingQuoteResetMessage() {
        return currentTab === 'beli'
            ? 'Lokasi pengiriman berubah. Silakan hitung ongkir lagi.'
            : 'Lokasi penjemputan berubah. Silakan hitung biaya penjemputan lagi.';
    }

    function renderShippingQuoteButton(loading = false) {
        if (!btnCheckOngkir) return;

        btnCheckOngkir.innerHTML = loading
            ? '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> ' + shippingQuoteLoadingLabel()
            : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> ' + shippingQuoteActionLabel();
    }

    function shippingServiceLabel() {
        return [shippingCourierName || shippingCourierCode.toUpperCase(), shippingServiceName]
            .filter(Boolean)
            .join(' - ');
    }

    function clearShippingQuoteFields() {
        shippingCost = 0;
        shippingDistanceKm = 0;
        shippingRoundTripDistanceKm = 0;
        shippingQuoteReady = shippingMethod === 'pickup';
        shippingCourierCode = '';
        shippingCourierName = '';
        shippingServiceName = '';
        shippingEtd = '';
        shippingWeight = 0;
        shippingRatePerKm = 0;
        shippingMinimumCost = 0;

        if (inpOngkir) inpOngkir.value = '0';
        if (inpShippingDistance) inpShippingDistance.value = '0';
        if (inpShippingCourier) inpShippingCourier.value = '';
        if (inpShippingService) inpShippingService.value = '';
        if (inpShippingEtd) inpShippingEtd.value = '';
        if (inpShippingWeight) inpShippingWeight.value = '0';
    }

    function setShippingStatus(message, type) {
        if (!message) {
            shippingStatusNote.className = 'shipping-status-note';
            shippingStatusNote.innerHTML = '';
            return;
        }

        const isProductCheckout = currentTab === 'beli';

        if (type === 'error') {
            shippingStatusNote.className = 'shipping-status-note show error';
                shippingStatusNote.innerHTML = `
                <span class="shipping-status-label">${isProductCheckout ? 'Informasi Pengiriman' : 'Informasi Penanganan'}</span>
                <span class="shipping-status-value">${isProductCheckout ? 'Belum bisa menghitung ongkir' : 'Belum bisa menghitung biaya penjemputan'}</span>
                <span class="shipping-status-meta">${escapeHtml(message)}</span>
            `;
        } else if (type === 'success') {
            if (isProductCheckout) {
                shippingStatusNote.className = 'shipping-status-note show success';
            shippingStatusNote.innerHTML = `
                <span class="shipping-status-label">Biaya Pengiriman</span>
                <span class="shipping-status-value">${escapeHtml(shippingServiceLabel() || 'Layanan tersedia')}</span>
                <span class="shipping-status-meta">Estimasi ${escapeHtml(shippingEtd || '-')} • ${fmt(shippingCost)}</span>
            `;
            } else {
                shippingStatusNote.className = 'shipping-status-note show success';
                shippingStatusNote.innerHTML = `
                    <span class="shipping-status-label">Biaya Penjemputan</span>
                    <span class="shipping-status-value">${fmt(shippingCost)}</span>
                    <span class="shipping-status-detail">Jarak toko ke lokasi: ${formatDistance(shippingDistanceKm)} km</span>
                    <span class="shipping-status-detail">Pulang-pergi: ${formatDistance(shippingRoundTripDistanceKm)} km</span>
                    <span class="shipping-status-detail">Tarif penjemputan: ${fmt(shippingRatePerKm)} / km</span>
                    <span class="shipping-status-detail">Total biaya penjemputan: ${fmt(shippingCost)}</span>
                `;
            }
        } else if (shippingMethod === 'pickup') {
            shippingStatusNote.className = 'shipping-status-note';
            shippingStatusNote.innerHTML = '';
        } else {
            shippingStatusNote.className = 'shipping-status-note show compact';
            shippingStatusNote.innerHTML = `
                <span class="shipping-status-label">${isProductCheckout ? 'Pengiriman Diantar' : 'Penjemputan APAR'}</span>
                <span class="shipping-status-meta">${escapeHtml(message)}</span>
            `;
        }
    }

    function applyShippingModeVisual() {
        const isPickup = shippingMethod === 'pickup';
        const radioPickup = document.querySelector('input[name="metode_pengiriman"][value="ambil_sendiri"]');
        const radioDiantar = document.querySelector('input[name="metode_pengiriman"][value="diantar"]');
        if (radioPickup && isPickup) radioPickup.checked = true;
        if (radioDiantar && !isPickup) radioDiantar.checked = true;

        shippingCards.forEach((card) => {
            const isActive = card.dataset.shippingCard === (isPickup ? 'pickup' : 'diantar');
            card.classList.toggle('active', isActive);
        });

        if (btnCheckOngkir) {
            btnCheckOngkir.classList.toggle('hidden', isPickup);
            btnCheckOngkir.disabled = isPickup;
        }

        syncMethodCardCopy();
        renderShippingQuoteButton(false);
    }

    function setShippingMethod(method) {
        shippingMethod = method === 'diantar' ? 'diantar' : 'pickup';
        inpMetodePengiriman.value = shippingMethod === 'pickup' ? 'ambil_sendiri' : 'diantar';

        if (serviceMethodHidden && currentTab !== 'beli') {
            serviceMethodHidden.value = shippingMethod === 'pickup' ? 'antar sendiri' : 'dijemput';
        }

        if (currentTab === 'beli' && shippingMethod === 'pickup') {
            clearShippingQuoteFields();
            shippingQuoteReady = true;
            setShippingStatus('', 'info');
        } else if (currentTab === 'beli') {
            clearShippingQuoteFields();
            shippingQuoteReady = false;
            setShippingStatus('Klik "Hitung Ongkir" untuk melihat biaya pengiriman ke alamat Anda.', 'info');
        } else if (shippingMethod === 'pickup') {
            clearShippingQuoteFields();
            shippingQuoteReady = true;
            setShippingStatus('', 'info');
        } else {
            clearShippingQuoteFields();
            shippingQuoteReady = false;
            setShippingStatus('Klik "Hitung Biaya Penjemputan" untuk melihat biaya penjemputan ke alamat Anda.', 'info');
        }

        applyShippingModeVisual();
        syncDisplayedTotal();
        if (currentTab !== 'beli') {
            updateServiceSummary();
        }
    }

    function invalidateShippingQuote(message) {
        if (shippingMethod !== 'diantar') return;
        clearShippingQuoteFields();
        shippingQuoteReady = false;
        setShippingStatus(message || shippingQuoteResetMessage(), 'info');
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

    function updateLocationSelectionHelper(message, tone = 'default') {
        if (!locationSelectionHelper) return;

        locationSelectionHelper.textContent = message;
        locationSelectionHelper.className = 'mt-2 text-xs font-semibold ';

        if (tone === 'error') {
            locationSelectionHelper.className += 'text-red-600';
            return;
        }

        if (tone === 'success') {
            locationSelectionHelper.className += 'text-emerald-600';
            return;
        }

        if (tone === 'info') {
            locationSelectionHelper.className += 'text-blue-600';
            return;
        }

        locationSelectionHelper.className += 'text-slate-500';
    }

    function updateSelectedAddressPreview(value) {
        if (selectedAddressDisplay) {
            selectedAddressDisplay.value = value || '';
        }
    }

    function renderAddressSuggestions(items) {
        if (!Array.isArray(items) || !items.length) {
            hideAddressSuggestions();
            updateLocationSelectionHelper('Lokasi pengiriman belum ditemukan. Coba kata kunci yang lebih spesifik.', 'error');
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
            title.textContent = String(item.label || '');

            const subtitle = document.createElement('span');
            subtitle.className = 'address-suggestion-subtitle';
            subtitle.dataset.locationMeta = '1';
            subtitle.textContent = `Lat ${Number(item.lat || 0).toFixed(5)} • Lng ${Number(item.lng || item.lon || 0).toFixed(5)}`;

            subtitle.textContent = [item.subdistrict_name, item.district_name, item.city_name, item.province_name, item.zip_code]
                .filter(Boolean)
                .join(' • ');

            btn.appendChild(title);
            btn.appendChild(subtitle);
            addressHelper.appendChild(btn);
        });
        addressHelper.classList.remove('hidden');
        updateLocationSelectionHelper('Pilih salah satu lokasi pengiriman untuk melengkapi alamat dan ongkir.', 'info');
    }

    function updateShippingDestinationHelper(message, tone = 'default') {
        if (!rajaOngkirHelper) return;

        rajaOngkirHelper.textContent = message;
        rajaOngkirHelper.className = 'mt-2 text-xs font-semibold ';

        if (tone === 'error') {
            rajaOngkirHelper.className += 'text-red-600';
            return;
        }

        if (tone === 'success') {
            rajaOngkirHelper.className += 'text-emerald-600';
            return;
        }

        if (tone === 'info') {
            rajaOngkirHelper.className += 'text-blue-600';
            return;
        }

        rajaOngkirHelper.className += 'text-slate-500';
    }

    function hideShippingDestinationSuggestions() {
        if (!rajaOngkirSuggestions) return;

        rajaOngkirSuggestions.classList.add('hidden');
        rajaOngkirSuggestions.innerHTML = '';
        rajaOngkirSuggestionItems = [];
    }

    function clearShippingDestination(options = {}) {
        const preserveSearch = Boolean(options.preserveSearch);

        if (inpShippingDestinationId) inpShippingDestinationId.value = '';
        if (inpShippingDestinationLabel) inpShippingDestinationLabel.value = '';
        if (!preserveSearch && rajaOngkirSearchInput) rajaOngkirSearchInput.value = '';

        hideShippingDestinationSuggestions();
        updateShippingDestinationHelper('Pilih lokasi pengiriman agar sistem bisa menghitung ongkir otomatis.', 'default');
    }

    function renderShippingDestinationSuggestions(items) {
        if (!rajaOngkirSuggestions) return;

        if (!Array.isArray(items) || !items.length) {
            hideShippingDestinationSuggestions();
            updateShippingDestinationHelper('Lokasi pengiriman belum ditemukan. Coba kata kunci yang lebih spesifik.', 'error');
            return;
        }

        rajaOngkirSuggestionItems = items;
        rajaOngkirSuggestions.innerHTML = '';

        items.forEach((item, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.rajaongkirIndex = String(index);
            button.className = 'block w-full border-b border-slate-100 px-4 py-3 text-left text-sm text-slate-700 hover:bg-red-50 hover:text-red-700 last:border-b-0';

            const title = document.createElement('span');
            title.className = 'block font-bold';
            title.textContent = item.label || '-';

            const subtitle = document.createElement('span');
            subtitle.className = 'mt-1 block text-xs font-semibold text-slate-400';
            subtitle.textContent = [item.subdistrict_name, item.district_name, item.city_name, item.zip_code]
                .filter(Boolean)
                .join(' • ');

            button.appendChild(title);
            button.appendChild(subtitle);
            rajaOngkirSuggestions.appendChild(button);
        });

        rajaOngkirSuggestions.classList.remove('hidden');
        updateShippingDestinationHelper('Pilih salah satu lokasi pengiriman untuk melengkapi tujuan ongkir.', 'info');
    }

    async function fetchShippingDestinationSuggestions(query) {
        try {
            const response = await fetch(`${SHIPPING_DESTINATION_URL}?search=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const payload = await response.json();
            const items = response.ok && payload.success ? (payload.data || []) : [];

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Gagal mencari lokasi pengiriman.');
            }

            renderShippingDestinationSuggestions(items);
        } catch (error) {
            hideShippingDestinationSuggestions();
            updateShippingDestinationHelper(error.message || 'Gagal mencari lokasi pengiriman.', 'error');
        }
    }

    function scheduleShippingDestinationSearch() {
        if (!rajaOngkirSearchInput) return;

        const query = (rajaOngkirSearchInput.value || '').trim();
        if (inpShippingDestinationId) inpShippingDestinationId.value = '';
        if (inpShippingDestinationLabel) inpShippingDestinationLabel.value = '';
        invalidateShippingQuote();

        if (rajaOngkirSearchTimer) clearTimeout(rajaOngkirSearchTimer);

        if (query.length < 3) {
            hideShippingDestinationSuggestions();
            updateShippingDestinationHelper('Ketik minimal 3 huruf untuk mencari lokasi pengiriman.', 'default');
            return;
        }

        rajaOngkirSearchTimer = setTimeout(() => {
            fetchShippingDestinationSuggestions(query);
        }, 350);
    }

    function selectShippingDestination(item) {
        if (!item) return;

        if (rajaOngkirSearchInput) {
            rajaOngkirSearchInput.value = item.label || '';
        }
        if (inpShippingDestinationId) {
            inpShippingDestinationId.value = item.id || '';
        }
        if (inpShippingDestinationLabel) {
            inpShippingDestinationLabel.value = item.label || '';
        }

        hideShippingDestinationSuggestions();
        updateShippingDestinationHelper('Lokasi pengiriman berhasil dipilih.', 'success');
        invalidateShippingQuote();
    }

    function clearAddressSelection(options = {}) {
        const preserveSearch = Boolean(options.preserveSearch);

        if (inpAlamatMaps) inpAlamatMaps.value = '';
        if (inpShippingDestinationId) inpShippingDestinationId.value = '';
        if (inpShippingDestinationLabel) inpShippingDestinationLabel.value = '';

        const inpProvinsi = document.getElementById('inp-provinsi');
        const inpKota = document.getElementById('inp-kota');
        const inpKecamatan = document.getElementById('inp-kecamatan');
        const inpKodePos = document.getElementById('inp-kodepos');

        if (inpProvinsi) inpProvinsi.value = '';
        if (inpKota) inpKota.value = '';
        if (inpKecamatan) inpKecamatan.value = '';
        if (inpKodePos) inpKodePos.value = '';
        if (inpAlamatLat) inpAlamatLat.value = '';
        if (inpAlamatLng) inpAlamatLng.value = '';
        if (orderMapLatEl) orderMapLatEl.textContent = '-';
        if (orderMapLngEl) orderMapLngEl.textContent = '-';
        if (!preserveSearch && locationSearchInput) locationSearchInput.value = '';
        if (orderMarker) {
            orderMap?.removeLayer(orderMarker);
            orderMarker = null;
        }

        updateSelectedAddressPreview('');
        updateCombinedAddress();
        invalidateShippingQuote();
    }

    function buildGeocodingQueries(item) {
        const primary = String(item?.label || '').trim();
        const fallback = [
            item?.subdistrict_name,
            item?.district_name,
            item?.city_name,
            item?.province_name,
            item?.zip_code,
        ].filter(Boolean).join(', ');

        return [primary, fallback].filter((value, index, values) => value && values.indexOf(value) === index);
    }

    async function fetchMapCoordinate(query) {
        const response = await fetch(`${ADDRESS_SUGGEST_URL}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            return null;
        }

        const firstResult = Array.isArray(data.data) ? data.data[0] : null;
        if (!firstResult) {
            return null;
        }

        const lat = Number(firstResult.lat || 0);
        const lng = Number(firstResult.lng || firstResult.lon || 0);

        if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat === 0 || lng === 0) {
            return null;
        }

        return { lat, lng };
    }

    async function syncMapToSelectedLocation(item) {
        const selectedLabel = String(item?.label || '').trim();
        const inpProvinsi = document.getElementById('inp-provinsi');
        const inpKota = document.getElementById('inp-kota');
        const inpKecamatan = document.getElementById('inp-kecamatan');
        const inpKodePos = document.getElementById('inp-kodepos');

        if (locationSearchInput) locationSearchInput.value = selectedLabel;
        if (inpAlamatMaps) inpAlamatMaps.value = selectedLabel;
        if (inpShippingDestinationId) inpShippingDestinationId.value = item?.id || '';
        if (inpShippingDestinationLabel) inpShippingDestinationLabel.value = item?.label || selectedLabel;
        if (inpProvinsi) inpProvinsi.value = item?.province_name || '';
        if (inpKota) inpKota.value = item?.city_name || '';
        if (inpKecamatan) inpKecamatan.value = item?.subdistrict_name || item?.district_name || '';
        if (inpKodePos) inpKodePos.value = item?.zip_code || '';

        updateSelectedAddressPreview(selectedLabel);
        updateCombinedAddress();
        hideAddressSuggestions();

        const queries = buildGeocodingQueries(item);

        for (const query of queries) {
            const coordinate = await fetchMapCoordinate(query);
            if (!coordinate) {
                continue;
            }

            selectAddressSuggestion(item, coordinate.lat, coordinate.lng);
            updateLocationSelectionHelper('Lokasi pengiriman dipilih dan titik peta sudah ditampilkan.', 'success');
            return;
        }

        if (inpAlamatLat) inpAlamatLat.value = '';
        if (inpAlamatLng) inpAlamatLng.value = '';
        if (orderMapLatEl) orderMapLatEl.textContent = '-';
        if (orderMapLngEl) orderMapLngEl.textContent = '-';
        if (orderMarker) {
            orderMap?.removeLayer(orderMarker);
            orderMarker = null;
        }

        updateLocationSelectionHelper('Lokasi pengiriman tersimpan. Titik peta dapat disesuaikan manual.', 'info');
    }

    function updateOrderCoord(lat, lng) {
        inpAlamatLat.value = Number(lat).toFixed(8);
        inpAlamatLng.value = Number(lng).toFixed(8);
        if (orderMapLatEl) orderMapLatEl.textContent = Number(lat).toFixed(6);
        if (orderMapLngEl) orderMapLngEl.textContent = Number(lng).toFixed(6);
        updateCombinedAddress();
    }

    function initLeafletMap(lat, lng, withMarker = true) {
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

        function bindMarker(markerInstance) {
            markerInstance.on('dragend', function(e) {
                var pos = e.target.getLatLng();
                updateOrderCoord(pos.lat, pos.lng);
                invalidateShippingQuote();
            });
        }

        function placeMarker(markerLat, markerLng) {
            if (orderMarker) {
                orderMarker.setLatLng([markerLat, markerLng]);
                return;
            }

            orderMarker = L.marker([markerLat, markerLng], { icon: redIcon, draggable: true }).addTo(orderMap)
                .bindPopup('Lokasi Pengiriman');
            bindMarker(orderMarker);
        }

        orderMarker = null;
        if (withMarker) {
            placeMarker(lat, lng);
        }

        orderMap.on('click', function(e) {
            if (!String(inpAlamatMaps?.value || '').trim()) {
                updateLocationSelectionHelper('Pilih lokasi pengiriman terlebih dahulu, lalu sesuaikan titiknya di peta.', 'error');
                return;
            }

            placeMarker(e.latlng.lat, e.latlng.lng);
            updateOrderCoord(e.latlng.lat, e.latlng.lng);
            invalidateShippingQuote();
        });
    }

    function selectAddressSuggestion(displayName, lat, lng, item = null) {
        const locationItem = typeof displayName === 'object' && displayName !== null ? displayName : item;
        const selectedLabel = typeof displayName === 'string'
            ? displayName
            : String(locationItem?.label || locationItem?.display_name || '');

        inpAlamatMaps.value = selectedLabel;
        inpAlamatLat.value = String(lat);
        inpAlamatLng.value = String(lng);
        if (locationSearchInput) locationSearchInput.value = selectedLabel;
        updateSelectedAddressPreview(selectedLabel);
        if (locationItem) {
            const inpProvinsi = document.getElementById('inp-provinsi');
            const inpKota = document.getElementById('inp-kota');
            const inpKecamatan = document.getElementById('inp-kecamatan');
            const inpKodePos = document.getElementById('inp-kodepos');

            if (inpShippingDestinationId) inpShippingDestinationId.value = locationItem.id || '';
            if (inpShippingDestinationLabel) inpShippingDestinationLabel.value = locationItem.label || selectedLabel;
            if (inpProvinsi) inpProvinsi.value = locationItem.province_name || locationItem.provinsi || '';
            if (inpKota) inpKota.value = locationItem.city_name || locationItem.kota || '';
            if (inpKecamatan) inpKecamatan.value = locationItem.subdistrict_name || locationItem.district_name || locationItem.kecamatan || '';
            if (inpKodePos) inpKodePos.value = locationItem.zip_code || locationItem.kode_pos || '';
        }
        updateCombinedAddress();
        invalidateShippingQuote();
        hideAddressSuggestions();
        if (lat && lng) initLeafletMap(Number(lat), Number(lng), true);
    }

    async function fetchAddressSuggestions(query) {
        try {
            const response = await fetch(`${SHIPPING_DESTINATION_URL}?search=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Gagal mencari lokasi pengiriman.');
            renderAddressSuggestions(data.data || []);
        } catch (error) {
            hideAddressSuggestions();
            updateLocationSelectionHelper(error.message || 'Gagal mencari lokasi pengiriman.', 'error');
        }
    }

    function scheduleAddressSuggestSearch() {
        if (!locationSearchInput) return;

        const query = (locationSearchInput.value || '').trim();

        if (addressSearchTimer) clearTimeout(addressSearchTimer);

        clearAddressSelection({ preserveSearch: true });

        if (query.length < 3) {
            hideAddressSuggestions();
            updateLocationSelectionHelper('Ketik minimal 3 huruf untuk mencari lokasi pengiriman.', 'default');
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
        const destinationId = (inpShippingDestinationId?.value || '').trim();
        const destinationLabel = (inpShippingDestinationLabel?.value || '').trim();
        const addressLat = (inpAlamatLat?.value || '').trim();
        const addressLng = (inpAlamatLng?.value || '').trim();
        const items = currentTab === 'beli'
            ? getSelectedItems().map((item) => ({ produk_id: item.produk_id, jumlah: item.jumlah }))
            : [];
        const serviceState = currentTab === 'beli' ? null : buildServiceState();

        if (currentTab === 'beli' && (!mapsAddress || !detailAddress)) {
            setShippingStatus('Lokasi pengiriman belum lengkap. Silakan pilih lokasi pengiriman terlebih dahulu.', 'error');
            return;
        }
        if (currentTab !== 'beli' && (!addressLat || !addressLng)) {
            setShippingStatus('Lokasi penjemputan belum lengkap. Silakan perbarui alamat terlebih dahulu.', 'error');
            return;
        }
        if (currentTab === 'beli' && !destinationId) {
            setShippingStatus(USE_AUTHENTICATED_CUSTOMER
                ? 'Lokasi pengiriman belum dapat digunakan untuk menghitung ongkir. Silakan perbarui alamat pengiriman Anda.'
                : 'Lokasi pengiriman belum dapat digunakan untuk menghitung ongkir. Silakan pilih lokasi pengiriman terlebih dahulu.', 'error');
            return;
        }
        if (currentTab === 'beli' && !items.length) {
            setShippingStatus(currentTab === 'beli'
                ? 'Pilih minimal satu produk sebelum cek ongkir.'
                : 'Lengkapi detail layanan terlebih dahulu sebelum menghitung biaya penjemputan.', 'error');
            return;
        }
        if (currentTab !== 'beli' && (!serviceState || !serviceState.qty || !serviceState.totalPrice)) {
            setShippingStatus('Lengkapi detail layanan terlebih dahulu sebelum menghitung biaya penjemputan.', 'error');
            return;
        }

        btnCheckOngkir.disabled = true;
        renderShippingQuoteButton(true);
        setShippingStatus('', 'info');

        try {
            const payload = currentTab === 'beli'
                ? {
                    destination_id: destinationId,
                    destination_label: destinationLabel,
                    order_type: 'produk',
                    handling_method: 'diantar',
                    items,
                }
                : {
                    order_type: serviceState.kategori,
                    handling_method: serviceState.metode,
                    service_unit_apar_ids: serviceState.registeredUnits.map((unit) => unit.id),
                    service_ukuran_apar: serviceState.ukuran,
                    service_jumlah_unit: serviceState.qty,
                    alamat_lat: addressLat,
                    alamat_lng: addressLng,
                };

            const response = await fetch(SHIPPING_COST_URL, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Gagal menghitung ongkir.');

            shippingCost = Number(data.cost || 0);
            shippingDistanceKm = Number(data.distance_km || 0);
            shippingRoundTripDistanceKm = Number(data.round_trip_distance_km || 0);
            shippingQuoteReady = shippingCost >= 0;
            shippingCourierCode = String(data.courier || '');
            shippingCourierName = String(data.courier_name || '');
            shippingServiceName = [String(data.service || ''), String(data.service_description || '')]
                .filter(Boolean)
                .join(' - ');
            shippingEtd = String(data.etd || '');
            shippingWeight = Number(data.weight || 0);
            shippingRatePerKm = Number(data.rate_per_km || 0);
            shippingMinimumCost = Number(data.minimum_cost || 0);
            inpOngkir.value = String(shippingCost);
            inpShippingDistance.value = String(currentTab === 'beli' ? 0 : shippingRoundTripDistanceKm);
            if (inpShippingCourier) inpShippingCourier.value = shippingCourierCode;
            if (inpShippingService) inpShippingService.value = shippingServiceName;
            if (inpShippingEtd) inpShippingEtd.value = shippingEtd;
            if (inpShippingWeight) inpShippingWeight.value = String(shippingWeight);

            setShippingStatus('Ongkir berhasil dihitung.', 'success');
            syncDisplayedTotal();
            if (currentTab !== 'beli') {
                updateServiceSummary();
            }
        } catch (error) {
            invalidateShippingQuote(error.message || 'Gagal menghitung ongkir.');
        } finally {
            btnCheckOngkir.disabled = false;
            renderShippingQuoteButton(false);
        }
    }

    function getSelectedItems() {
        if (CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS) {
            return CART_ORDER_ITEMS.map((item) => ({
                produk_id: Number(item.produk_id || 0),
                jumlah: Number(item.jumlah || 0),
                jenis: item.jenis || 'APAR',
                kapasitas: item.kapasitas || '-',
                merek: item.merek || 'FIREFIX',
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
                merek: item.merek || 'FIREFIX',
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

    function syncPurchasePriceRequestState() {
        if (!purchasePriceRequestBox) return;

        const isEligible = currentTab === 'beli' && normalTotal > 5000000 && getSelectedItems().length > 0;
        purchasePriceRequestBox.classList.toggle('hidden', !isEligible);

        if (!isEligible) {
            purchasePriceRequestOpen = false;
            purchasePriceRequestForm?.classList.add('hidden');
            if (btnTogglePriceRequest) btnTogglePriceRequest.textContent = 'Ajukan Harga Khusus';
            return;
        }

        if (requestBaseSubtotal) requestBaseSubtotal.textContent = fmt(normalTotal);
        if (requestPromoDiscount) requestPromoDiscount.textContent = '- ' + fmt(promoDiscountNominal);
        if (requestAfterPromo) requestAfterPromo.textContent = fmt(Math.max(0, normalTotal - promoDiscountNominal));
        if (requestPriceHint) requestPriceHint.textContent = `Harga Pengajuan tidak boleh lebih besar dari ${fmt(normalTotal)}.`;
        purchasePriceRequestForm?.classList.toggle('hidden', !purchasePriceRequestOpen);

        if (btnTogglePriceRequest) {
            btnTogglePriceRequest.textContent = purchasePriceRequestOpen ? 'Tutup Form' : 'Ajukan Harga Khusus';
        }
    }

    function syncDisplayedTotal() {
        if (sectionPromoBanyak) {
            sectionPromoBanyak.classList.toggle('hidden', currentTab !== 'beli');
        }

        if (currentTab !== 'beli') {
            syncPurchasePriceRequestState();
            return;
        }

        const selectedItems = getSelectedItems();
        const totalQty = selectedItems.reduce((total, item) => total + Number(item.jumlah || 0), 0);
        const totalOngkir = shippingMethod === 'diantar' ? shippingCost : 0;
        const productLabel = selectedItems.length
            ? selectedItems.map((item) => `${item.nama || 'Produk'} x${Number(item.jumlah || 0)}`).join(', ')
            : 'Belum dipilih';
        const methodLabel = shippingMethod === 'diantar' ? 'Diantar' : 'Ambil Sendiri';
        
        // Cek promo diskon untuk tipe beli
        promoDiscountPercent = 0;
        if (currentTab === 'beli' && totalQty > 0) {
            if (totalQty >= 50) promoDiscountPercent = 25;
            else if (totalQty >= 35) promoDiscountPercent = 20;
            else if (totalQty >= 20) promoDiscountPercent = 15;
            else if (totalQty >= 10) promoDiscountPercent = 10;
            else if (totalQty >= 5) promoDiscountPercent = 5;
        }
        
        // Update UI Promo
        if (activePromoStatus && currentTab === 'beli') {
            if (promoDiscountPercent > 0) {
                activePromoStatus.className = 'mt-4 p-3 rounded-xl border border-emerald-200 bg-emerald-50 text-center shadow-inner';
                promoStatusText.textContent = `Diskon Aktif: ${promoDiscountPercent}%`;
                promoStatusText.className = 'text-sm font-black text-emerald-800';
                
                let nextTier = 0;
                let nextPercent = 0;
                if (totalQty >= 50) {
                    promoStatusSubtext.textContent = 'Anda sudah mendapatkan diskon maksimal.';
                } else {
                    if (totalQty >= 35) { nextTier = 50; nextPercent = 25; }
                    else if (totalQty >= 20) { nextTier = 35; nextPercent = 20; }
                    else if (totalQty >= 10) { nextTier = 20; nextPercent = 15; }
                    else if (totalQty >= 5) { nextTier = 10; nextPercent = 10; }
                    
                    const diff = nextTier - totalQty;
                    promoStatusSubtext.textContent = `Tambah ${diff} unit lagi untuk mendapatkan diskon ${nextPercent}%.`;
                }
            } else {
                activePromoStatus.className = 'mt-4 p-3 rounded-xl border border-slate-200 bg-slate-50 text-center';
                promoStatusText.textContent = 'Belum ada diskon aktif.';
                promoStatusText.className = 'text-sm font-black text-slate-500';
                const diff = 5 - totalQty;
                if (diff > 0) {
                    promoStatusSubtext.textContent = `Tambah ${diff} unit lagi untuk mendapatkan diskon 5%.`;
                } else {
                    promoStatusSubtext.textContent = `Tambah unit lagi untuk mendapatkan diskon.`;
                }
            }
        }

        promoDiscountNominal = (normalTotal * promoDiscountPercent) / 100;
        const finalTotal = normalTotal - promoDiscountNominal + totalOngkir;

        lblSubtotal.textContent = fmt(normalTotal);
        if (lblProductItems) lblProductItems.textContent = productLabel;
        if (lblItemCount) lblItemCount.textContent = String(selectedItems.length);
        if (lblShippingMethod) lblShippingMethod.textContent = methodLabel;
        
        const lblTotalUnit = document.getElementById('lbl-total-unit');
        if (lblTotalUnit) lblTotalUnit.textContent = totalQty;
        
        const lblDiscountPercent = document.getElementById('lbl-discount-percent');
        if (lblDiscountPercent) lblDiscountPercent.textContent = promoDiscountPercent + '%';

        if (lblDiscount) lblDiscount.textContent = '- ' + fmt(promoDiscountNominal);
        if (lblOngkir) lblOngkir.textContent = fmt(totalOngkir);
        if (shippingSummaryCourierRow) {
            shippingSummaryCourierRow.classList.toggle('hidden', shippingMethod !== 'diantar' || !shippingServiceLabel());
        }
        if (lblShippingCourier) {
            lblShippingCourier.textContent = shippingServiceLabel() || '-';
        }
        if (shippingSummaryEtdRow) {
            shippingSummaryEtdRow.classList.toggle('hidden', shippingMethod !== 'diantar' || !shippingEtd);
        }
        if (lblShippingEtd) {
            lblShippingEtd.textContent = shippingEtd || '-';
        }
        
        if (ongkirRow) {
            ongkirRow.style.display = 'flex';
        }
        
        lblTotal.textContent = fmt(finalTotal);
        
        btnSubmitLabel.textContent = 'Checkout';
        btnSubmit.className = 'btn-primary-action submit w-full justify-center';

        if (discountRow) {
            discountRow.classList.toggle('text-emerald-700', promoDiscountNominal > 0);
            discountRow.style.display = 'flex';
        }

        syncPurchasePriceRequestState();
    }

    function invalidatePricingByItemChange() {
        invalidateShippingQuote();
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
        if (serviceMethodHidden && serviceMethodHidden.value) {
            return serviceMethodHidden.value;
        }

        const selected = serviceMethodRadios.find((radio) => radio.checked);
        if (selected) {
            return selected.value;
        }

        return shippingMethod === 'pickup' ? 'antar sendiri' : 'dijemput';
    }

    function isPrefilledServiceCheckout() {
        return Boolean(PREFILLED_SERVICE_ORDER)
            && Array.isArray(PREFILLED_SERVICE_ORDER.selected_unit_ids)
            && PREFILLED_SERVICE_ORDER.selected_unit_ids.length > 0;
    }

    function getServiceUnitStatus() {
        if (isPrefilledServiceCheckout()) {
            return 'terdaftar';
        }

        const selected = serviceUnitStatusRadios.find((radio) => radio.checked);
        return selected && selected.value === 'terdaftar' ? 'terdaftar' : 'belum_terdaftar';
    }

    function normalizeMatchText(value) {
        return String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
    }

    function normalizeServiceMediaKey(value) {
        const text = String(value || '').toLowerCase().trim();
        if (!text) return 'unknown';
        if (text.includes('powder') || text.includes('dry chemical') || text.includes('dcp')) return 'powder';
        if (text.includes('foam')) return 'foam';
        if (text.includes('co2') || text.includes('carbon')) return 'co2';
        if (text.includes('clean agent') || text.includes('halotron')) return 'clean_agent';
        return text.replace(/[^a-z0-9]+/g, '_');
    }

    function displayServiceMediaLabel(value) {
        const key = normalizeServiceMediaKey(value);
        if (key === 'powder') return 'Powder';
        if (key === 'foam') return 'Foam';
        if (key === 'co2') return 'CO2';
        if (key === 'clean_agent') return 'Clean Agent';
        return String(value || 'APAR').trim() || 'APAR';
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

    function isPrefilledServiceGroup(groupKey) {
        return Boolean(PREFILLED_SERVICE_ORDER)
            && String(groupKey || '') === String(PREFILLED_SERVICE_ORDER.group_key || '');
    }

    function getUnitsByPurchaseGroup(groupKey) {
        if (isPrefilledServiceGroup(groupKey)) {
            const selectedIds = Array.isArray(PREFILLED_SERVICE_ORDER?.selected_unit_ids)
                ? PREFILLED_SERVICE_ORDER.selected_unit_ids.map((id) => Number(id || 0)).filter((id) => id > 0)
                : [];

            return selectedIds
                .map((id) => REGISTERED_UNIT_APAR_DB.find((unit) => Number(unit.id) === Number(id)))
                .filter(Boolean);
        }

        return REGISTERED_UNIT_APAR_DB.filter((unit) => unit.purchase_key === groupKey);
    }

    function getSelectedRegisteredUnitIds() {
        if (isPrefilledServiceCheckout()) {
            const hiddenInputs = [...document.querySelectorAll('.service-unit-hidden-input')]
                .map((input) => Number(input.value || 0))
                .filter((id) => id > 0);

            if (hiddenInputs.length) {
                return [...new Set(hiddenInputs)];
            }

            return (PREFILLED_SERVICE_ORDER?.selected_unit_ids || [])
                .map((id) => Number(id || 0))
                .filter((id) => id > 0);
        }

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

    function resolveRefillPriceForSize(refill, ukuran) {
        if (!refill) {
            return 0;
        }

        const ukuranNormalized = String(ukuran || '').trim().toLowerCase();
        const rules = Array.isArray(refill.service_price_rules) ? refill.service_price_rules : [];
        const directRule = rules.find((rule) => String(rule.ukuran || '').trim().toLowerCase() === ukuranNormalized);

        if (directRule && Number(directRule.harga || 0) > 0) {
            return Number(directRule.harga || 0);
        }

        const ukuranKg = parseServiceSizeKg(ukuran);
        if (ukuranKg > 0) {
            const numericRule = rules.find((rule) => parseServiceSizeKg(rule.ukuran) === ukuranKg);
            if (numericRule && Number(numericRule.harga || 0) > 0) {
                return Number(numericRule.harga || 0);
            }

            if (Number(refill.harga || 0) > 0) {
                return ukuranKg * Number(refill.harga || 0);
            }
        }

        return 0;
    }

    function getRegisteredUnitRefillSelection(unit) {
        const refill = suggestRefillForUnits([unit]);
        const unitPrice = resolveRefillPriceForSize(refill, unit.ukuran);

        return {
            refill,
            unitPrice,
        };
    }

    function getManualServiceMedia() {
        return String(serviceJenisAparManual?.value || '').trim();
    }

    function availableSizesForManualService() {
        const mediaKey = normalizeServiceMediaKey(getManualServiceMedia());
        const matched = SERVICE_MEDIA_DB.find((item) => normalizeServiceMediaKey(item.label || item.key) === mediaKey);

        return matched && Array.isArray(matched.sizes) ? matched.sizes : [];
    }

    function syncServiceJenisAparValue() {
        if (!serviceJenisAparHidden) {
            return;
        }

        const isService = serviceJenisLayanan && serviceJenisLayanan.value === 'service';
        const isRegistered = getServiceUnitStatus() === 'terdaftar';

        if (isService && !isRegistered) {
            serviceJenisAparHidden.value = getManualServiceMedia();
            return;
        }

        const selectedUnits = getSelectedRegisteredUnits();
        if (selectedUnits.length) {
            serviceJenisAparHidden.value = [...new Set(selectedUnits.map((unit) => displayServiceMediaLabel(unit.jenis_apar || 'APAR')))].join(', ');
        }
    }

    function syncManualServiceSizeOptions() {
        if (!serviceUkuranApar) {
            return;
        }

        const isService = serviceJenisLayanan && serviceJenisLayanan.value === 'service';
        const isRegistered = getServiceUnitStatus() === 'terdaftar';
        const selectedValue = String(serviceUkuranApar.value || '').trim();
        const sizeOptions = (!isService || isRegistered) ? SERVICE_UKURAN_OPTIONS : availableSizesForManualService();

        serviceUkuranApar.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '-- Pilih Ukuran APAR --';
        serviceUkuranApar.appendChild(placeholder);

        sizeOptions.forEach((ukuran) => {
            const option = document.createElement('option');
            option.value = ukuran;
            option.textContent = ukuran;
            option.selected = ukuran === selectedValue;
            serviceUkuranApar.appendChild(option);
        });

        if (!sizeOptions.includes(selectedValue)) {
            serviceUkuranApar.value = '';
        }
    }

    function resolveServicePackagePrice(paket, media, ukuran) {
        if (!paket) {
            return 0;
        }

        const mediaKey = normalizeServiceMediaKey(media);
        const mediaPrices = paket.price_matrix?.[mediaKey] || {};
        const direct = Number(mediaPrices?.[ukuran] || 0);
        if (direct > 0) {
            return direct;
        }

        const ukuranKg = parseServiceSizeKg(ukuran);
        const matchedSize = Object.keys(mediaPrices).find((size) => parseServiceSizeKg(size) === ukuranKg);

        return matchedSize ? Number(mediaPrices[matchedSize] || 0) : 0;
    }

    function resolveServicePeralatan(paket, qty) {
        if (!paket) {
            return [];
        }

        return (paket.peralatans || []).map((peralatan) => {
            const jumlahPerUnit = Math.max(1, Number(peralatan.jumlah || 0));

            return {
                peralatan_id: Number(peralatan.peralatan_id || 0),
                nama: peralatan.nama || '-',
                jumlah_per_unit: jumlahPerUnit,
                jumlah: jumlahPerUnit * Math.max(1, Number(qty || 1)),
                stok: Number(peralatan.stok || 0),
                stok_minimum: Number(peralatan.stok_minimum || 0),
            };
        });
    }

    function renderServiceTemplate(template, index) {
        if (!template) {
            return null;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(index)).trim();

        return wrapper.firstElementChild;
    }

    function refreshDynamicRemoveButtons(container, selector) {
        if (!container) {
            return;
        }

        const buttons = [...container.querySelectorAll(selector)];
        const hasMultipleRows = container.children.length > 1;
        buttons.forEach((button) => {
            button.disabled = !hasMultipleRows;
            button.classList.toggle('opacity-60', !hasMultipleRows);
            button.classList.toggle('cursor-not-allowed', !hasMultipleRows);
        });
    }

    function summarizeRefillRow(row, item) {
        const note = row?.querySelector('.service-item-subtotal');
        if (!note) {
            return;
        }

        if (!item.refill || !item.ukuran) {
            note.textContent = 'Pilih jenis refill dan ukuran APAR untuk melihat subtotal item refill.';
            return;
        }

        const kgLabel = item.totalKg > 0 ? ` • Kebutuhan ${formatKg(item.totalKg)} ${item.refill?.satuan_label || 'Kg'}` : '';
        note.textContent = `Harga ${fmt(item.unitPrice)} / unit • Subtotal ${fmt(item.total)}${kgLabel}`;
    }

    function summarizeServiceRow(row, item) {
        const priceLabel = row?.querySelector('.service-item-price-label span');
        const subtotalLabel = row?.querySelector('.service-item-subtotal-label span');
        
        if (!priceLabel || !subtotalLabel) {
            return;
        }

        if (!item.jenisApar || !item.paket || !item.ukuran) {
            priceLabel.textContent = '-';
            subtotalLabel.textContent = 'Rp 0';
            return;
        }

        priceLabel.textContent = `${fmt(item.unitPrice)} / unit`;
        subtotalLabel.textContent = fmt(item.total);
    }

    function readManualRefillItems() {
        if (!serviceRefillItemsContainer) {
            return [];
        }

        return [...serviceRefillItemsContainer.querySelectorAll('.service-refill-item-row')]
            .map((row, index) => {
                const refillId = Number(row.querySelector('.service-refill-item-select')?.value || 0);
                const ukuran = String(row.querySelector('.service-refill-item-size')?.value || '').trim();
                const qty = Math.max(1, Number(row.querySelector('.service-refill-item-qty')?.value || 1));
                const refill = findServiceRefillById(refillId);
                const unitPrice = resolveRefillPriceForSize(refill, ukuran);
                const totalKg = parseServiceSizeKg(ukuran) * qty;
                const item = {
                    index,
                    row,
                    refillId,
                    refill,
                    ukuran,
                    qty,
                    unitPrice,
                    total: unitPrice * qty,
                    totalKg,
                    displayLabel: refill?.nama_label || 'Jenis refill',
                };

                summarizeRefillRow(row, item);

                return item;
            })
            .filter((item) => item.refillId > 0 || item.ukuran !== '');
    }

    function readManualServiceItems() {
        if (!serviceServiceItemsContainer) {
            return [];
        }

        return [...serviceServiceItemsContainer.querySelectorAll('.service-service-item-row')]
            .map((row, index) => {
                const jenisApar = String(row.querySelector('.service-service-item-jenis')?.value || '').trim();
                const paketId = Number(row.querySelector('.service-service-item-select')?.value || 0);
                const ukuran = String(row.querySelector('.service-service-item-size')?.value || '').trim();
                const qty = Math.max(1, Number(row.querySelector('.service-service-item-qty')?.value || 1));
                const paket = findServicePaketById(paketId);
                const unitPrice = paket ? resolveServicePackagePrice(paket, jenisApar || 'APAR', ukuran) : 0;
                const peralatanItems = paket ? resolveServicePeralatan(paket, qty) : [];
                const item = {
                    index,
                    row,
                    jenisApar,
                    paketId,
                    paket,
                    ukuran,
                    qty,
                    unitPrice,
                    total: unitPrice * qty,
                    peralatanItems,
                    displayLabel: (jenisApar ? `${jenisApar} - ` : '') + (paket?.nama || 'Paket service'),
                };

                summarizeServiceRow(row, item);

                return item;
            })
            .filter((item) => item.paketId > 0 || item.ukuran !== '' || item.jenisApar !== '');
    }

    function aggregatePeralatanItems(lineItems) {
        const groups = new Map();

        lineItems.forEach((lineItem) => {
            (lineItem.peralatanItems || []).forEach((item) => {
                const peralatanId = Number(item.peralatan_id || 0);
                const key = peralatanId > 0
                    ? `id:${peralatanId}`
                    : `name:${String(item.nama || '').trim().toLowerCase()}`;

                if (!groups.has(key)) {
                    groups.set(key, {
                        peralatan_id: peralatanId,
                        nama: String(item.nama || '-').trim() || '-',
                        jumlah: 0,
                        stok: Number(item.stok || 0),
                        stok_minimum: Number(item.stok_minimum || 0),
                    });
                }

                const current = groups.get(key);
                current.jumlah += Number(item.jumlah || 0);
                current.stok = Number(item.stok || current.stok || 0);
                current.stok_minimum = Number(item.stok_minimum || current.stok_minimum || 0);
            });
        });

        return [...groups.values()];
    }

    function attachRefillRowEvents(row) {
        if (!row) {
            return;
        }

        row.querySelectorAll('.service-refill-item-select, .service-refill-item-size, .service-refill-item-qty').forEach((field) => {
            field.addEventListener('change', updateServiceSummary);
            field.addEventListener('input', updateServiceSummary);
        });

        const removeButton = row.querySelector('.btn-remove-refill-item');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                if (!serviceRefillItemsContainer || serviceRefillItemsContainer.children.length <= 1) {
                    return;
                }

                row.remove();
                refreshDynamicRemoveButtons(serviceRefillItemsContainer, '.btn-remove-refill-item');
                updateServiceSummary();
            });
        }
    }

    function attachServiceRowEvents(row) {
        if (!row) {
            return;
        }

        const jenisSelect = row.querySelector('.service-service-item-jenis');
        const paketSelect = row.querySelector('.service-service-item-select');

        if (jenisSelect && paketSelect) {
            // Function to filter paket options based on selected Jenis APAR
            const filterPaket = () => {
                const jenis = jenisSelect.value.toLowerCase();
                const currentVal = paketSelect.value;
                let valStillValid = false;

                [...paketSelect.options].forEach((opt) => {
                    if (opt.value === '') return;
                    
                    const namaPaket = (opt.getAttribute('data-nama') || opt.text || '').toLowerCase();
                    let show = true;
                    
                    if (jenis === 'powder' && namaPaket.includes('co2')) show = false;
                    if (jenis === 'foam' && namaPaket.includes('co2')) show = false;
                    if (jenis === 'co2' && (namaPaket.includes('powder') || namaPaket.includes('foam'))) show = false;

                    opt.style.display = show ? '' : 'none';
                    if (show && opt.value === currentVal) {
                        valStillValid = true;
                    }
                });

                if (currentVal !== '' && !valStillValid) {
                    paketSelect.value = '';
                }
            };

            jenisSelect.addEventListener('change', () => {
                filterPaket();
                updateServiceSummary();
            });
            
            // Run initially
            filterPaket();
        }

        row.querySelectorAll('.service-service-item-select, .service-service-item-size, .service-service-item-qty').forEach((field) => {
            field.addEventListener('change', updateServiceSummary);
            field.addEventListener('input', updateServiceSummary);
        });

        const removeButton = row.querySelector('.btn-remove-service-item');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                if (!serviceServiceItemsContainer || serviceServiceItemsContainer.children.length <= 1) {
                    return;
                }

                row.remove();
                refreshDynamicRemoveButtons(serviceServiceItemsContainer, '.btn-remove-service-item');
                updateServiceSummary();
            });
        }
    }

    function appendManualRefillRow() {
        if (!serviceRefillItemsContainer || !serviceRefillItemTemplate) {
            return;
        }

        const row = renderServiceTemplate(serviceRefillItemTemplate, serviceRefillRowIndex++);
        if (!row) {
            return;
        }

        serviceRefillItemsContainer.appendChild(row);
        attachRefillRowEvents(row);
        refreshDynamicRemoveButtons(serviceRefillItemsContainer, '.btn-remove-refill-item');
        updateServiceSummary();
    }

    function appendManualServiceRow() {
        if (!serviceServiceItemsContainer || !serviceServiceItemTemplate) {
            return;
        }

        const row = renderServiceTemplate(serviceServiceItemTemplate, serviceServiceRowIndex++);
        if (!row) {
            return;
        }

        serviceServiceItemsContainer.appendChild(row);
        attachServiceRowEvents(row);
        refreshDynamicRemoveButtons(serviceServiceItemsContainer, '.btn-remove-service-item');
        updateServiceSummary();
    }

    function getRegisteredUnitSubtotal(unit) {
        const kategori = serviceJenisLayanan && serviceJenisLayanan.value === 'service' ? 'service' : 'refill';

        if (kategori === 'service') {
            const paket = findServicePaketById(servicePaketId?.value);
            return resolveServicePackagePrice(paket, unit.jenis_apar, unit.ukuran);
        }

        return getRegisteredUnitRefillSelection(unit).unitPrice;
    }

    function renderRegisteredUnitChecklist(options = {}) {
        if (!servicePurchaseGroup || !serviceRegisteredUnitList) {
            return;
        }

        if (isPrefilledServiceCheckout()) {
            serviceRegisteredUnitList.innerHTML = '';
            lastRenderedPurchaseGroup = servicePurchaseGroup.value || '';
            if (serviceRegisteredEmptyNote) serviceRegisteredEmptyNote.classList.add('hidden');
            if (serviceRegisteredCountNote) serviceRegisteredCountNote.classList.add('hidden');
            return;
        }

        const groupKey = servicePurchaseGroup.value || '';
        const units = getUnitsByPurchaseGroup(groupKey);
        const shouldReset = Boolean(options.resetSelection) || groupKey !== lastRenderedPurchaseGroup;
        const unitIdsInGroup = new Set(units.map((unit) => Number(unit.id)));
        const oldIdsInGroup = OLD_SELECTED_UNIT_APAR_IDS.filter((id) => unitIdsInGroup.has(Number(id)));
        const currentSelectedIds = shouldReset
            ? new Set(oldIdsInGroup)
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

        units.forEach((unit) => {
            const row = document.createElement('label');
            row.className = 'block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 has-[:checked]:border-blue-400 has-[:checked]:bg-blue-50';

            const subtotal = getRegisteredUnitSubtotal(unit);
            const unitRefillSelection = getRegisteredUnitRefillSelection(unit);
            const subtotalText = subtotal > 0
                ? fmt(subtotal)
                : (serviceJenisLayanan?.value === 'service' ? 'Harga service standar belum tersedia' : 'Harga refill belum tersedia');
            const kode = escapeHtml(unit.kode || '-');
            const produkNama = escapeHtml(unit.produk_nama || '-');
            const jenisApar = escapeHtml(unit.jenis_apar || '-');
            const ukuran = escapeHtml(unit.ukuran || '-');
            const tglBeli = escapeHtml(unit.tgl_beli || '-');
            const masaBerlaku = escapeHtml(unit.masa_berlaku || '-');
            const statusUnit = escapeHtml(unit.status_unit || '-');
            const refillLabel = escapeHtml(unitRefillSelection.refill?.nama_label || '-');
            const refillStatusLabel = escapeHtml(unit.refill_status_label || 'Aman');
            const isLocked = Boolean(unit.is_refill_locked);
            const lockMessage = escapeHtml(unit.refill_lock_message || 'Unit ini sedang dalam proses refill.');

            row.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="pt-1">
                        <input type="checkbox" name="service_unit_apar_ids[]" value="${unit.id}" class="service-unit-checkbox h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                            <p class="font-black text-slate-900">${kode}</p>
                            <p class="text-sm font-black text-blue-700">${subtotalText}</p>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-slate-700">${produkNama}</p>
                        <p class="mt-2 text-xs font-black uppercase tracking-[0.18em] ${unit.needs_refill ? 'text-amber-600' : 'text-emerald-600'}">${refillStatusLabel}</p>
                        <div class="mt-3 grid grid-cols-1 gap-2 text-xs font-semibold text-slate-500 sm:grid-cols-2">
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Jenis APAR:</span> ${jenisApar}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Ukuran:</span> ${ukuran}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Jenis Refill:</span> ${refillLabel}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Tanggal Beli:</span> ${tglBeli}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Tanggal Expired:</span> ${masaBerlaku}</p>
                            <p><span class="font-black text-slate-400 uppercase tracking-wider">Status:</span> ${statusUnit}</p>
                        </div>
                        <p class="mt-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Pilih untuk Refill</p>
                        ${isLocked ? `<div class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800">${lockMessage}</div>` : ''}
                    </div>
                </div>
            `;

            const checkbox = row.querySelector('.service-unit-checkbox');
            checkbox.checked = currentSelectedIds.has(Number(unit.id));
            checkbox.disabled = isLocked;
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

    function renderServicePackageDetails(state) {
        if (!servicePackageNote || !servicePackageRincian) {
            return;
        }

        servicePackageRincian.innerHTML = '';

        if (!state || state.kategori !== 'service' || !state.paket || currentTab === 'beli') {
            servicePackageNote.classList.add('hidden');
            return;
        }

        const detailTitle = document.createElement('p');
        detailTitle.className = 'text-sm font-black text-slate-900';
        detailTitle.textContent = `${state.paket.label ? state.paket.label + ' - ' : ''}${state.paket.nama}`;
        servicePackageRincian.appendChild(detailTitle);

        const workTitle = document.createElement('p');
        workTitle.className = 'pt-2 text-xs font-black uppercase tracking-[0.18em] text-slate-400';
        workTitle.textContent = 'Pekerjaan Dalam Paket';
        servicePackageRincian.appendChild(workTitle);

        (state.paket.rincian || []).forEach((item) => {
            const row = document.createElement('p');
            row.className = 'text-sm font-semibold text-slate-700';
            row.textContent = '- ' + item;
            servicePackageRincian.appendChild(row);
        });

        if ((state.lineItems || []).length) {
            const priceTitle = document.createElement('p');
            priceTitle.className = 'pt-2 text-xs font-black uppercase tracking-[0.18em] text-slate-400';
            priceTitle.textContent = 'Harga Service';
            servicePackageRincian.appendChild(priceTitle);

            state.lineItems.forEach((item) => {
                const row = document.createElement('p');
                row.className = 'text-sm font-semibold text-slate-700';
                const qtyLabel = Number(item.qty || 1) > 1 ? ` x ${Number(item.qty || 1)} unit` : '';
                row.textContent = `${item.label}${qtyLabel}: ${fmt(item.total || 0)}`;
                servicePackageRincian.appendChild(row);
            });
        }

        if ((state.peralatanItems || []).length) {
            const peralatanTitle = document.createElement('p');
            peralatanTitle.className = 'pt-2 text-xs font-black uppercase tracking-[0.18em] text-slate-400';
            peralatanTitle.textContent = `Peralatan Paket (${state.qty} unit)`;
            servicePackageRincian.appendChild(peralatanTitle);

            (state.peralatanItems || []).forEach((item) => {
                const row = document.createElement('p');
                row.className = 'text-sm font-semibold text-slate-700';
                row.textContent = `${item.nama} (${Number(item.jumlah || 0)} pcs total)`;
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
        const manualMedia = getManualServiceMedia();
        const registeredRefillSelections = kategori === 'refill' && registeredUnits.length
            ? registeredUnits.map((unit) => ({
                unit,
                ...getRegisteredUnitRefillSelection(unit),
            }))
            : [];
        const registeredBreakdowns = Object.values(registeredRefillSelections.reduce((carry, selection) => {
            const refillId = Number(selection.refill?.id || 0);
            if (refillId <= 0) {
                return carry;
            }

            if (!carry[refillId]) {
                carry[refillId] = {
                    refill: selection.refill,
                    qty: 0,
                    totalKg: 0,
                    totalPrice: 0,
                };
            }

            carry[refillId].qty += 1;
            carry[refillId].totalKg += parseServiceSizeKg(selection.unit?.ukuran);
            carry[refillId].totalPrice += Number(selection.unitPrice || 0);

            return carry;
        }, {}));

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
            manualMedia,
            itemLabel: 'Belum dipilih',
            unitPrice: 0,
            totalPrice: 0,
            ongkir: metode === 'dijemput' && shippingMethod === 'diantar' ? Number(shippingCost || 0) : 0,
            grandTotal: 0,
            totalKg: 0,
            stockUnit: refill?.satuan_label || 'Kg',
            insufficientStock: false,
            lowStock: false,
            currentStockLabel: 'Pilih jenis refil untuk melihat stok.',
            afterStockLabel: 'Sisa stok setelah transaksi akan tampil di sini.',
            registeredRefillSelections,
            registeredBreakdowns,
            hasMixedRegisteredRefill: registeredBreakdowns.length > 1,
            hasMissingRegisteredRefill: registeredUnits.length > 0 && registeredRefillSelections.some((selection) => !selection.refill),
            lineItems: [],
            peralatanItems: [],
        };

        if (kategori === 'refill') {
            state.refill = registeredUnits.length
                ? (registeredBreakdowns[0]?.refill || null)
                : refill;
            state.itemLabel = registeredUnits.length && registeredBreakdowns.length > 1
                ? registeredBreakdowns.map((item) => `${item.refill?.nama_label || 'Refill'} (${item.qty} unit)`).join(', ')
                : (state.refill?.nama_label || 'Belum dipilih');
            state.unitPrice = registeredUnits.length
                ? Number(registeredRefillSelections[0]?.unitPrice || 0)
                : resolveRefillPriceForSize(refill, ukuran);
            state.totalKg = ukuranKg > 0 ? (registeredUnits.length ? ukuranKg : ukuranKg * qty) : 0;
            state.totalPrice = registeredUnits.length
                ? registeredRefillSelections.reduce((total, selection) => total + Number(selection.unitPrice || 0), 0)
                : (state.unitPrice > 0 ? state.unitPrice * qty : 0);

            if (state.hasMissingRegisteredRefill) {
                state.currentStockLabel = 'Jenis refill otomatis belum ditemukan untuk salah satu unit yang dipilih.';
                state.afterStockLabel = 'Pastikan master data jenis refill sesuai dengan jenis APAR pada unit terdaftar.';
                state.insufficientStock = true;
                state.lowStock = false;
            } else if (registeredUnits.length && registeredBreakdowns.length) {
                const hasInsufficientBreakdown = registeredBreakdowns.some((item) => Number(item.refill?.stok || 0) < Number(item.totalKg || 0));
                const hasLowBreakdown = registeredBreakdowns.some((item) => (Number(item.refill?.stok || 0) - Number(item.totalKg || 0)) <= Number(item.refill?.stok_minimum || 0));
                state.currentStockLabel = registeredBreakdowns
                    .map((item) => `${item.refill?.nama_label || 'Refill'}: ${formatKg(Number(item.refill?.stok || 0))} ${item.refill?.satuan_label || 'Kg'}`)
                    .join(' • ');
                state.afterStockLabel = registeredBreakdowns
                    .map((item) => `${item.refill?.nama_label || 'Refill'} sisa ${formatKg(Number(item.refill?.stok || 0) - Number(item.totalKg || 0))} ${item.refill?.satuan_label || 'Kg'}`)
                    .join(' • ');
                state.insufficientStock = hasInsufficientBreakdown;
                state.lowStock = !hasInsufficientBreakdown && hasLowBreakdown;
            } else if (state.refill) {
                const currentStock = Number(state.refill.stok || 0);
                const remainingStock = currentStock - state.totalKg;
                state.currentStockLabel = `${state.refill.nama_label}: ${formatKg(currentStock)} ${state.refill.satuan_label}`;
                state.afterStockLabel = state.totalKg > 0
                    ? `Perkiraan sisa stok setelah pesanan: ${formatKg(remainingStock)} ${state.refill.satuan_label}`
                    : 'Jumlah kebutuhan refill akan muncul setelah ukuran dan unit dipilih.';
                state.insufficientStock = state.totalKg > 0 && remainingStock < 0;
                state.lowStock = state.totalKg > 0 && remainingStock <= Number(state.refill.stok_minimum || 0);
            } else if (refill) {
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
            state.lineItems = registeredUnits.length
                ? registeredUnits.map((unit) => {
                    const unitPrice = resolveServicePackagePrice(paket, unit.jenis_apar, unit.ukuran);
                    return {
                        label: `${displayServiceMediaLabel(unit.jenis_apar)} ${unit.ukuran}`,
                        qty: 1,
                        unitPrice,
                        total: unitPrice,
                    };
                })
                : [{
                    label: `${displayServiceMediaLabel(manualMedia)} ${ukuran}`.trim(),
                    qty,
                    unitPrice: resolveServicePackagePrice(paket, manualMedia, ukuran),
                    total: resolveServicePackagePrice(paket, manualMedia, ukuran) * qty,
                }];
            state.unitPrice = Number(state.lineItems[0]?.unitPrice || 0);
            state.totalPrice = state.lineItems.reduce((total, item) => total + Number(item.total || 0), 0);
            state.peralatanItems = resolveServicePeralatan(paket, qty);

            const stockIssues = state.peralatanItems.filter((item) => Number(item.stok || 0) < Number(item.jumlah || 0));
            const lowStockItems = state.peralatanItems.filter((item) => {
                const remaining = Number(item.stok || 0) - Number(item.jumlah || 0);
                return Number(item.jumlah || 0) > 0 && remaining <= Number(item.stok_minimum || 0);
            });

            if (stockIssues.length) {
                state.currentStockLabel = stockIssues.map((item) => `${item.nama}: stok ${item.stok}, butuh ${item.jumlah}`).join(' • ');
                state.afterStockLabel = 'Service tetap bisa dibuat, tetapi finalisasi akan ditolak jika stok peralatan belum mencukupi.';
                state.insufficientStock = true;
                state.lowStock = false;
            } else {
                state.currentStockLabel = state.peralatanItems.length
                    ? state.peralatanItems.map((item) => `${item.nama}: ${item.stok} pcs`).join(' • ')
                    : 'Paket service akan memakai peralatan sesuai standar pekerjaan.';
                state.afterStockLabel = 'Stok peralatan akan berkurang setelah pembayaran valid dikonfirmasi admin.';
                state.lowStock = lowStockItems.length > 0;
                state.insufficientStock = false;
            }
        }

        state.grandTotal = state.totalPrice + state.ongkir;

        return state;
    }

    function updateServiceSummary() {
        const state = buildServiceState();
        const metodeLabel = state.metode === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput';

        if (state.unitStatus === 'terdaftar' && serviceJumlahUnit) {
            serviceJumlahUnit.value = String(state.qty);
        }

        if (serviceJenisAparHidden) {
            serviceJenisAparHidden.value = state.kategori === 'service' && state.unitStatus !== 'terdaftar'
                ? getManualServiceMedia()
                : (state.registeredUnits.length
                    ? [...new Set(state.registeredUnits.map((unit) => displayServiceMediaLabel(unit.jenis_apar || 'APAR')))].join(', ')
                    : '');
        }

        if (serviceRefillPriceNote) {
            if (state.kategori === 'refill') {
                if (state.unitStatus === 'terdaftar') {
                    serviceRefillPriceNote.textContent = state.totalPrice > 0
                        ? `Jenis refill mengikuti unit APAR terpilih secara otomatis. Total dihitung per unit berdasarkan ukuran APAR yang dicentang, termasuk jika jenis refill berbeda.`
                        : 'Jenis refill dan harga untuk APAR terdaftar akan mengikuti unit APAR yang dicentang.';
                } else {
                    serviceRefillPriceNote.textContent = state.refill && state.unitPrice > 0
                        ? `Harga standar ${state.refill.nama_label}: ${fmt(state.unitPrice)} per unit ukuran ${state.ukuran || 'APAR'}.`
                        : 'Harga standar refil akan muncul otomatis saat jenis refil dan ukuran APAR dipilih.';
                }
            } else {
                serviceRefillPriceNote.textContent = state.totalPrice > 0
                    ? 'Harga service mengikuti harga standar jenis service dan dikalikan sesuai jumlah unit APAR.'
                    : 'Pilih jenis service untuk melihat harga standar otomatis.';
            }
        }

        renderServicePackageDetails(state);

        if (serviceSummaryCategory) serviceSummaryCategory.textContent = state.kategori === 'service' ? 'Service APAR' : 'Refill APAR';
        if (serviceSummaryStatusRow) serviceSummaryStatusRow.classList.toggle('hidden', false);
        if (serviceSummaryStatus) serviceSummaryStatus.textContent = state.unitStatus === 'terdaftar' ? 'APAR Terdaftar' : 'APAR Belum Terdaftar';
        if (serviceSummaryUnitRow) serviceSummaryUnitRow.classList.toggle('hidden', state.unitStatus !== 'terdaftar' || state.registeredUnits.length < 1);
        if (serviceSummaryUnit) {
            serviceSummaryUnit.textContent = state.registeredUnits.length
                ? state.registeredUnits
                    .map((unit) => unit.kode || unit.label || `UNIT-${unit.id}`)
                    .filter(Boolean)
                    .join(', ')
                : '-';
        }
        if (serviceSummaryItemLabel) {
            serviceSummaryItemLabel.textContent = state.kategori === 'service' ? 'Jenis Service' : 'Jenis Refill';
        }
        if (serviceSummaryItem) serviceSummaryItem.textContent = state.itemLabel;
        if (serviceSummarySize) serviceSummarySize.textContent = state.ukuran || '-';
        if (serviceSummaryQty) serviceSummaryQty.textContent = `${state.qty} unit`;
        if (serviceSummaryUsageLabel) {
            serviceSummaryUsageLabel.textContent = state.kategori === 'service' ? 'Peralatan Paket' : 'Kebutuhan Refill';
        }
        if (serviceSummaryKg) {
            serviceSummaryKg.textContent = state.kategori === 'service'
                ? `${(state.peralatanItems || []).length} item`
                : (state.totalKg > 0 ? `${formatKg(state.totalKg)} ${state.stockUnit}` : '-');
        }
        if (serviceSummaryEquipmentRow) {
            serviceSummaryEquipmentRow.classList.toggle('hidden', state.kategori !== 'service' || (state.peralatanItems || []).length < 1);
        }
        if (serviceSummaryEquipment) {
            serviceSummaryEquipment.textContent = state.kategori === 'service' && (state.peralatanItems || []).length
                ? state.peralatanItems.map((item) => `${item.nama} x ${Number(item.jumlah || 0)}`).join(', ')
                : '-';
        }
        if (serviceSummaryMethod) serviceSummaryMethod.textContent = metodeLabel;
        if (serviceSummaryCourierRow) {
            serviceSummaryCourierRow.classList.toggle('hidden', state.metode !== 'dijemput' || !shippingServiceLabel());
        }
        if (serviceSummaryCourier) {
            serviceSummaryCourier.textContent = shippingServiceLabel() || '-';
        }
        if (serviceSummaryEtdRow) {
            serviceSummaryEtdRow.classList.toggle('hidden', state.metode !== 'dijemput' || !shippingEtd);
        }
        if (serviceSummaryEtd) {
            serviceSummaryEtd.textContent = shippingEtd || '-';
        }
        if (serviceSummaryOngkir) serviceSummaryOngkir.textContent = fmt(state.ongkir || 0);
        if (serviceSummaryPriceLabel) {
            serviceSummaryPriceLabel.textContent = 'Total Pembayaran';
        }
        if (serviceSummaryPrice) serviceSummaryPrice.textContent = fmt(state.grandTotal || 0);
        if (serviceStockTitle) {
            serviceStockTitle.textContent = state.kategori === 'service' ? 'Stok Peralatan Paket' : 'Stok Saat Ini';
        }
        if (serviceStockCurrent) serviceStockCurrent.textContent = state.currentStockLabel;
        if (serviceStockAfter) serviceStockAfter.textContent = state.afterStockLabel;
        if (serviceRegisteredCountNote) {
            if (state.unitStatus === 'terdaftar' && state.registeredUnits.length) {
                serviceRegisteredCountNote.textContent = `${state.registeredUnits.length} Unit APAR dipilih - Total ${fmt(state.grandTotal || 0)}`;
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

        if (state.insufficientStock) {
            setServiceAlert(serviceStockWarning, state.kategori === 'service'
                ? 'Stok peralatan untuk jenis service ini belum cukup. Service masih bisa dibuat, tetapi finalisasi nanti akan ditolak sampai stok tersedia.'
                : (state.hasMissingRegisteredRefill
                    ? 'Jenis refill otomatis belum ditemukan untuk salah satu unit yang dipilih.'
                    : `Stok refill ${state.refill?.nama_label || 'yang dipilih'} tidak mencukupi.`));
        } else {
            setServiceAlert(serviceStockWarning, '');
        }

        if (state.lowStock) {
            const targetName = state.kategori === 'service'
                ? 'peralatan paket service'
                : (state.refill?.nama_label || 'refill yang dipilih');
            setServiceAlert(serviceLowStockWarning, `Stok ${targetName} hampir habis.`);
        } else {
            setServiceAlert(serviceLowStockWarning, '');
        }
    }

    function updateServiceFormState() {
        const isBeli = currentTab === 'beli';
        const isRefill = !serviceJenisLayanan || serviceJenisLayanan.value !== 'service';
        const unitStatus = getServiceUnitStatus();
        const isRegisteredService = !isBeli && unitStatus === 'terdaftar';
        const isPrefilledCheckout = !isBeli && isPrefilledServiceCheckout();
        const showRefillFields = !isBeli && isRefill;
        const showServiceFields = !isBeli && !isRefill;

        if (serviceRefillFields) {
            serviceRefillFields.classList.toggle('hidden', !showRefillFields);
        }
        if (serviceRefillSelectField) {
            serviceRefillSelectField.classList.toggle('hidden', isRegisteredService && isRefill);
        }
        if (serviceServiceFields) {
            serviceServiceFields.classList.toggle('hidden', !showServiceFields);
        }
        if (serviceUnitStatusFields) {
            serviceUnitStatusFields.classList.toggle('hidden', isBeli || isPrefilledCheckout);
        }
        if (serviceRegisteredUnitFields) {
            serviceRegisteredUnitFields.classList.toggle('hidden', !isRegisteredService || isPrefilledCheckout);
        }
        if (serviceManualTypeField) {
            serviceManualTypeField.classList.toggle('hidden', isRegisteredService || isRefill);
        }
        if (serviceManualSizeField) {
            serviceManualSizeField.classList.toggle('hidden', isRegisteredService);
        }
        if (serviceQuantityField) {
            serviceQuantityField.classList.toggle('hidden', isRegisteredService);
        }

        if (isRegisteredService && !isPrefilledCheckout) {
            syncRegisteredAutomaticChoices();
            renderRegisteredUnitChecklist();
        } else if (serviceRegisteredUnitList) {
            serviceRegisteredUnitList.innerHTML = '';
            lastRenderedPurchaseGroup = null;
            if (serviceRegisteredEmptyNote) {
                serviceRegisteredEmptyNote.classList.toggle('hidden', isPrefilledCheckout);
                if (!isPrefilledCheckout) {
                    serviceRegisteredEmptyNote.classList.remove('hidden');
                }
            }
        }

        syncManualServiceSizeOptions();
        syncServiceJenisAparValue();

        if (serviceJenisLayanan) serviceJenisLayanan.required = !isBeli;
        if (serviceUkuranApar) serviceUkuranApar.required = !isBeli && !isRegisteredService;
        if (serviceJenisAparManual) {
            serviceJenisAparManual.required = !isBeli && !isRefill && !isRegisteredService;
            serviceJenisAparManual.disabled = isBeli || isRefill || isRegisteredService;
        }
        if (serviceJumlahUnit) {
            serviceJumlahUnit.required = !isBeli && !isRegisteredService;
            serviceJumlahUnit.readOnly = isRegisteredService;
            if (isRegisteredService) {
                const registeredQty = getSelectedRegisteredUnits().length;
                serviceJumlahUnit.value = registeredQty > 0 ? String(registeredQty) : '';
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
            serviceJenisRefill.required = showRefillFields && !isRegisteredService;
            serviceJenisRefill.disabled = !isRefill || isRegisteredService;
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

    function renderServicePackageDetails(state) {
        if (!servicePackageNote || !servicePackageRincian) {
            return;
        }

        servicePackageRincian.innerHTML = '';

        if (!state || state.kategori !== 'service' || currentTab === 'beli' || !(state.lineItems || []).length) {
            servicePackageNote.classList.add('hidden');
            return;
        }

        state.lineItems.forEach((item, index) => {
            const block = document.createElement('div');
            block.className = 'rounded-2xl border border-white/70 bg-white px-3 py-3';

            const title = document.createElement('p');
            title.className = 'text-sm font-black text-slate-900';
            title.textContent = `${index + 1}. ${item.displayLabel || item.label || 'Paket service'} (${Number(item.qty || 0)} unit)`;
            block.appendChild(title);

            const subtotal = document.createElement('p');
            subtotal.className = 'mt-1 text-xs font-semibold text-slate-500';
            subtotal.textContent = `Harga ${fmt(item.unitPrice || 0)} / unit | Subtotal ${fmt(item.total || 0)}`;
            block.appendChild(subtotal);

            const rincian = Array.isArray(item.paket?.rincian) ? item.paket.rincian : [];
            if (rincian.length) {
                const workTitle = document.createElement('p');
                workTitle.className = 'pt-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400';
                workTitle.textContent = 'Pekerjaan Dalam Paket';
                block.appendChild(workTitle);

                rincian.forEach((detail) => {
                    const row = document.createElement('p');
                    row.className = 'text-sm font-semibold text-slate-700';
                    row.textContent = '- ' + detail;
                    block.appendChild(row);
                });
            }

            servicePackageRincian.appendChild(block);
        });

        if ((state.peralatanItems || []).length) {
            const equipmentTitle = document.createElement('p');
            equipmentTitle.className = 'pt-2 text-xs font-black uppercase tracking-[0.18em] text-slate-400';
            equipmentTitle.textContent = `Peralatan Paket (${state.qty} unit)`;
            servicePackageRincian.appendChild(equipmentTitle);

            state.peralatanItems.forEach((item) => {
                const row = document.createElement('p');
                row.className = 'text-sm font-semibold text-slate-700';
                row.textContent = `${item.nama} (${Number(item.jumlah || 0)} pcs total)`;
                servicePackageRincian.appendChild(row);
            });
        }

        servicePackageNote.classList.remove('hidden');
    }

    function buildServiceState() {
        const kategori = serviceJenisLayanan && serviceJenisLayanan.value === 'service' ? 'service' : 'refill';
        const metode = getSelectedServiceMethod();
        const ongkir = metode === 'dijemput' && shippingMethod === 'diantar' ? Number(shippingCost || 0) : 0;

        const state = {
            kategori,
            unitStatus: getServiceUnitStatus(),
            registeredUnits: getSelectedRegisteredUnits(),
            metode,
            ongkir,
            grandTotal: 0,
            itemLabel: 'Belum ada item layanan',
            qty: 0,
            ukuran: '',
            ukuranKg: 0,
            totalKg: 0,
            unitPrice: 0,
            totalPrice: 0,
            stockUnit: 'Kg',
            insufficientStock: false,
            lowStock: false,
            currentStockLabel: kategori === 'service'
                ? 'Peralatan paket akan dihitung otomatis dari item service yang Anda pilih.'
                : 'Stok refill akan dihitung otomatis dari item refill yang Anda isi.',
            afterStockLabel: kategori === 'service'
                ? 'Stok peralatan akan berkurang setelah pembayaran valid dikonfirmasi admin.'
                : 'Sisa stok refill setelah transaksi akan tampil di sini.',
            lineItems: [],
            refillGroups: [],
            peralatanItems: [],
            rawRefillItems: [],
            rawServiceItems: [],
            refill: null,
            paket: null,
            hasMissingRegisteredRefill: false,
        };

        if (kategori === 'refill') {
            state.rawRefillItems = readManualRefillItems();
            state.lineItems = state.rawRefillItems.map((item) => {
                return {
                    ...item,
                    label: item.refill?.nama_label || 'Jenis refill',
                    displayLabel: `${item.refill?.nama_label || 'Jenis refill'} | ${item.ukuran || 'Ukuran APAR'}`,
                    totalKg: Number(item.totalKg || 0),
                    unitPrice: Number(item.unitPrice || 0),
                    total: Number(item.total || 0),
                };
            });

            state.qty = state.lineItems.reduce((total, item) => total + Math.max(1, Number(item.qty || 1)), 0);
            state.ukuran = [...new Set(state.lineItems.map((item) => String(item.ukuran || '').trim()).filter(Boolean))].join(', ');
            state.totalKg = state.lineItems.reduce((total, item) => total + Number(item.totalKg || 0), 0);
            state.ukuranKg = state.totalKg;
            state.totalPrice = state.lineItems.reduce((total, item) => total + Number(item.total || 0), 0);
            state.unitPrice = Number(state.lineItems[0]?.unitPrice || 0);
            state.refill = state.lineItems.length === 1 ? (state.lineItems[0]?.refill || null) : null;
            state.itemLabel = state.lineItems.length
                ? state.lineItems.map((item) => `${item.label} (${Number(item.qty || 1)} unit)`).join(', ')
                : 'Belum ada item refill';

            const refillGroups = new Map();
            state.lineItems.forEach((item) => {
                if (!item.refill) {
                    return;
                }

                const refillId = Number(item.refill.id || 0);
                if (!refillGroups.has(refillId)) {
                    refillGroups.set(refillId, {
                        refill: item.refill,
                        qty: 0,
                        totalKg: 0,
                        totalPrice: 0,
                    });
                }

                const group = refillGroups.get(refillId);
                group.qty += Number(item.qty || 0);
                group.totalKg += Number(item.totalKg || 0);
                group.totalPrice += Number(item.total || 0);
            });

            state.refillGroups = [...refillGroups.values()].map((group) => {
                const stok = Number(group.refill?.stok || 0);
                const stokMinimum = Number(group.refill?.stok_minimum || 0);
                const remaining = stok - Number(group.totalKg || 0);

                return {
                    ...group,
                    stok,
                    stokMinimum,
                    remaining,
                };
            });

            state.hasMissingRegisteredRefill = state.lineItems.some((item) => !item.refill || !item.ukuran);
            state.stockUnit = state.refillGroups[0]?.refill?.satuan_label || 'Kg';

            if (state.lineItems.length > 0 && state.hasMissingRegisteredRefill) {
                state.currentStockLabel = 'Lengkapi jenis refill dan ukuran APAR untuk setiap item.';
                state.afterStockLabel = 'Subtotal dan kebutuhan refill baru dihitung setelah semua item refill lengkap.';
                state.insufficientStock = true;
            } else if (state.refillGroups.length > 0) {
                state.currentStockLabel = state.refillGroups
                    .map((group) => `${group.refill?.nama_label || 'Refill'}: ${formatKg(group.stok)} ${group.refill?.satuan_label || 'Kg'}`)
                    .join(' | ');
                state.afterStockLabel = state.refillGroups
                    .map((group) => `${group.refill?.nama_label || 'Refill'} sisa ${formatKg(group.remaining)} ${group.refill?.satuan_label || 'Kg'}`)
                    .join(' | ');
                state.insufficientStock = state.refillGroups.some((group) => group.remaining < 0);
                state.lowStock = !state.insufficientStock && state.refillGroups.some((group) => group.remaining <= group.stokMinimum);
            }
        } else {
            state.rawServiceItems = readManualServiceItems();
            state.lineItems = state.rawServiceItems.map((item) => {
                return {
                    ...item,
                    label: item.paket?.nama || 'Paket service',
                    displayLabel: `${item.paket?.nama || 'Paket service'} | ${item.ukuran || 'Ukuran APAR'}`,
                    unitPrice: Number(item.unitPrice || 0),
                    total: Number(item.total || 0),
                };
            });

            state.qty = state.lineItems.reduce((total, item) => total + Math.max(1, Number(item.qty || 1)), 0);
            state.ukuran = [...new Set(state.lineItems.map((item) => String(item.ukuran || '').trim()).filter(Boolean))].join(', ');
            state.totalPrice = state.lineItems.reduce((total, item) => total + Number(item.total || 0), 0);
            state.unitPrice = Number(state.lineItems[0]?.unitPrice || 0);
            state.paket = state.lineItems.length === 1 ? (state.lineItems[0]?.paket || null) : null;
            state.itemLabel = state.lineItems.length
                ? state.lineItems.map((item) => `${item.label} (${Number(item.qty || 1)} unit)`).join(', ')
                : 'Belum ada item service';
            state.peralatanItems = aggregatePeralatanItems(state.rawServiceItems);

            if (state.lineItems.length > 0 && state.lineItems.some((item) => !item.jenisApar || !item.paket || !item.ukuran)) {
                state.currentStockLabel = 'Lengkapi jenis, ukuran APAR, dan paket service untuk setiap item.';
                state.afterStockLabel = 'Subtotal service baru dihitung setelah semua item service lengkap.';
                state.insufficientStock = true;
            } else if (state.peralatanItems.length > 0) {
                state.currentStockLabel = state.peralatanItems
                    .map((item) => `${item.nama}: ${Number(item.stok || 0)} pcs`)
                    .join(' | ');
                state.afterStockLabel = state.peralatanItems
                    .map((item) => `${item.nama} sisa ${Number(item.stok || 0) - Number(item.jumlah || 0)} pcs`)
                    .join(' | ');
                state.insufficientStock = state.peralatanItems.some((item) => Number(item.stok || 0) < Number(item.jumlah || 0));
                state.lowStock = !state.insufficientStock && state.peralatanItems.some((item) => {
                    const remaining = Number(item.stok || 0) - Number(item.jumlah || 0);
                    return Number(item.jumlah || 0) > 0 && remaining <= Number(item.stok_minimum || 0);
                });
            }
        }

        state.grandTotal = state.totalPrice + state.ongkir;

        return state;
    }

    function updateServiceSummary() {
        const state = buildServiceState();
        const metodeLabel = state.metode === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput';

        if (serviceJenisAparHidden) {
            serviceJenisAparHidden.value = state.lineItems.length
                ? [...new Set(state.lineItems.map((item) => item.label || '').filter(Boolean))].join(', ').slice(0, 120)
                : '';
        }

        if (serviceRefillPriceNote) {
            serviceRefillPriceNote.textContent = state.kategori === 'service'
                ? (
                    state.lineItems.length
                        ? 'Harga service dihitung per item berdasarkan jenis, paket, ukuran APAR, dan jumlah unit lalu dijumlahkan otomatis.'
                        : 'Tambahkan item service untuk melihat subtotal tiap paket dan total pembayaran.'
                )
                : (
                    state.lineItems.length
                        ? 'Harga refill dihitung per item berdasarkan jenis refill, ukuran APAR, dan jumlah unit lalu dijumlahkan otomatis.'
                        : 'Tambahkan item refill untuk melihat subtotal tiap item dan total pembayaran.'
                );
        }

        renderServicePackageDetails(state);

        if (serviceSummaryCategory) {
            serviceSummaryCategory.textContent = state.kategori === 'service' ? 'Service APAR' : 'Refill APAR';
        }
        if (serviceSummarySize) {
            serviceSummarySize.textContent = state.ukuran || '-';
        }
        if (serviceSummaryQty) {
            serviceSummaryQty.textContent = `${state.qty} unit`;
        }
        if (serviceSummaryUsageLabel) {
            serviceSummaryUsageLabel.textContent = state.kategori === 'service' ? 'Peralatan Paket' : 'Kebutuhan Refill';
        }
        if (serviceSummaryKg) {
            serviceSummaryKg.textContent = state.kategori === 'service'
                ? `${(state.peralatanItems || []).length} item`
                : (state.totalKg > 0 ? `${formatKg(state.totalKg)} ${state.stockUnit}` : '-');
        }
        if (serviceSummaryMethod) {
            serviceSummaryMethod.textContent = metodeLabel;
        }
        if (serviceSummaryCourierRow) {
            serviceSummaryCourierRow.classList.toggle('hidden', state.metode !== 'dijemput' || !shippingServiceLabel());
        }
        if (serviceSummaryCourier) {
            serviceSummaryCourier.textContent = shippingServiceLabel() || '-';
        }
        if (serviceSummaryEtdRow) {
            serviceSummaryEtdRow.classList.toggle('hidden', state.metode !== 'dijemput' || !shippingEtd);
        }
        if (serviceSummaryEtd) {
            serviceSummaryEtd.textContent = shippingEtd || '-';
        }
        if (serviceSummaryOngkir) {
            serviceSummaryOngkir.textContent = fmt(state.ongkir || 0);
        }
        if (serviceSummaryPriceLabel) {
            serviceSummaryPriceLabel.textContent = 'Total Pembayaran';
        }
        if (serviceSummaryPrice) {
            serviceSummaryPrice.textContent = fmt(state.grandTotal || 0);
        }
        if (serviceSummaryItemCount) {
            serviceSummaryItemCount.textContent = `${state.lineItems.length} item`;
        }
        if (serviceSummaryItems) {
            serviceSummaryItems.innerHTML = '';

            if (!state.lineItems.length) {
                const empty = document.createElement('p');
                empty.className = 'text-sm font-semibold text-slate-500';
                empty.textContent = 'Belum ada item layanan yang diisi.';
                serviceSummaryItems.appendChild(empty);
            } else {
                state.lineItems.forEach((item, index) => {
                    const card = document.createElement('div');
                    card.className = 'rounded-2xl border border-slate-200 bg-slate-50/70 px-3 py-3';

                    const title = document.createElement('p');
                    title.className = 'text-sm font-black text-slate-900';
                    title.textContent = `${index + 1}. ${item.displayLabel || item.label || 'Item layanan'}`;
                    card.appendChild(title);

                    const meta = document.createElement('p');
                    meta.className = 'mt-1 text-xs font-semibold text-slate-500';
                    meta.textContent = state.kategori === 'service'
                        ? `${Number(item.qty || 1)} unit | Harga ${fmt(item.unitPrice || 0)} / unit`
                        : `${Number(item.qty || 1)} unit | Harga ${fmt(item.unitPrice || 0)} / unit | ${formatKg(item.totalKg || 0)} ${item.refill?.satuan_label || 'Kg'}`;
                    card.appendChild(meta);

                    const subtotal = document.createElement('p');
                    subtotal.className = 'mt-2 text-sm font-black text-slate-900';
                    subtotal.textContent = `Subtotal ${fmt(item.total || 0)}`;
                    card.appendChild(subtotal);

                    serviceSummaryItems.appendChild(card);
                });
            }
        }
        if (serviceSummaryEquipmentBox) {
            serviceSummaryEquipmentBox.classList.toggle('hidden', state.kategori !== 'service' || (state.peralatanItems || []).length < 1);
        }
        if (serviceSummaryEquipmentCount) {
            serviceSummaryEquipmentCount.textContent = `${(state.peralatanItems || []).length} item`;
        }
        if (serviceSummaryEquipmentList) {
            serviceSummaryEquipmentList.innerHTML = '';

            (state.peralatanItems || []).forEach((item) => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2';

                const name = document.createElement('p');
                name.className = 'text-sm font-semibold text-slate-700';
                name.textContent = item.nama || '-';
                row.appendChild(name);

                const qty = document.createElement('span');
                qty.className = 'text-xs font-black text-slate-500';
                qty.textContent = `x${Number(item.jumlah || 0)}`;
                row.appendChild(qty);

                serviceSummaryEquipmentList.appendChild(row);
            });
        }

        if (serviceStockTitle) {
            serviceStockTitle.textContent = state.kategori === 'service' ? 'Stok Peralatan Paket' : 'Stok Saat Ini';
        }
        if (serviceStockCurrent) {
            serviceStockCurrent.textContent = state.currentStockLabel;
        }
        if (serviceStockAfter) {
            serviceStockAfter.textContent = state.afterStockLabel;
        }
        if (serviceRegisteredCountNote) {
            serviceRegisteredCountNote.classList.add('hidden');
        }

        if (currentTab === 'beli') {
            setServiceAlert(serviceStockWarning, '');
            setServiceAlert(serviceLowStockWarning, '');
            return;
        }

        if (state.insufficientStock) {
            if (state.kategori === 'service') {
                setServiceAlert(serviceStockWarning, 'Lengkapi item service terlebih dahulu atau pastikan stok peralatan paket masih mencukupi.');
            } else {
                const refillTargets = state.refillGroups
                    .filter((group) => Number(group.remaining || 0) < 0)
                    .map((group) => group.refill?.nama_label || 'refill')
                    .filter(Boolean);
                const refillLabel = refillTargets.length ? refillTargets.join(', ') : 'item refill';
                setServiceAlert(serviceStockWarning, state.hasMissingRegisteredRefill
                    ? 'Lengkapi jenis refill dan ukuran APAR untuk semua item refill terlebih dahulu.'
                    : `Stok ${refillLabel} tidak mencukupi untuk item yang dipilih.`);
            }
        } else {
            setServiceAlert(serviceStockWarning, '');
        }

        if (state.lowStock) {
            setServiceAlert(
                serviceLowStockWarning,
                state.kategori === 'service'
                    ? 'Stok peralatan paket service sudah mendekati batas minimum.'
                    : 'Stok refill untuk item yang dipilih sudah mendekati batas minimum.'
            );
        } else {
            setServiceAlert(serviceLowStockWarning, '');
        }
    }

    function updateServiceFormState() {
        const isBeli = currentTab === 'beli';
        const isRefill = !serviceJenisLayanan || serviceJenisLayanan.value !== 'service';
        const showRefillFields = !isBeli && isRefill;
        const showServiceFields = !isBeli && !isRefill;

        if (serviceRefillFields) {
            serviceRefillFields.classList.toggle('hidden', !showRefillFields);
        }
        if (serviceServiceFields) {
            serviceServiceFields.classList.toggle('hidden', !showServiceFields);
        }

        [
            serviceRefillSelectField,
            serviceUnitStatusFields,
            serviceRegisteredUnitFields,
            serviceManualTypeField,
            serviceManualSizeField,
            serviceQuantityField,
            serviceRegisteredEmptyNote,
        ].forEach((element) => {
            if (element) {
                element.classList.add('hidden');
            }
        });

        if (serviceRegisteredUnitList) {
            serviceRegisteredUnitList.innerHTML = '';
            lastRenderedPurchaseGroup = null;
        }
        if (serviceRegisteredCountNote) {
            serviceRegisteredCountNote.classList.add('hidden');
        }

        if (serviceJenisLayanan) {
            serviceJenisLayanan.required = !isBeli;
        }
        if (serviceJenisRefill) {
            serviceJenisRefill.required = false;
            serviceJenisRefill.disabled = true;
        }
        if (servicePaketId) {
            servicePaketId.required = false;
            servicePaketId.disabled = true;
        }
        if (serviceUkuranApar) {
            serviceUkuranApar.required = false;
            serviceUkuranApar.disabled = true;
        }
        if (serviceJumlahUnit) {
            serviceJumlahUnit.required = false;
            serviceJumlahUnit.readOnly = false;
            serviceJumlahUnit.disabled = true;
        }
        if (serviceJenisAparManual) {
            serviceJenisAparManual.required = false;
            serviceJenisAparManual.disabled = true;
        }
        if (servicePurchaseGroup) {
            servicePurchaseGroup.required = false;
            servicePurchaseGroup.disabled = true;
        }
        serviceUnitStatusRadios.forEach((radio) => {
            radio.required = false;
            radio.disabled = true;
        });
        serviceMethodRadios.forEach((radio) => {
            radio.required = !isBeli;
        });

        refreshDynamicRemoveButtons(serviceRefillItemsContainer, '.btn-remove-refill-item');
        refreshDynamicRemoveButtons(serviceServiceItemsContainer, '.btn-remove-service-item');
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
        if (sectionServiceSidebar) sectionServiceSidebar.classList.remove('hidden');
        if (sectionServiceInline) sectionServiceInline.classList.toggle('hidden', isB);
        if (productSummaryCard) productSummaryCard.classList.toggle('hidden', !isB);
        if (serviceSummaryCard) serviceSummaryCard.classList.toggle('hidden', isB);
        if (sectionPromoBanyak) sectionPromoBanyak.classList.toggle('hidden', !isB);

        if (isB) {
            setShippingMethod(inpMetodePengiriman.value || 'pickup');
        } else {
            const serviceMode = (serviceMethodHidden?.value || '') === 'antar sendiri' ? 'pickup' : 'diantar';
            setShippingMethod(serviceMode);
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
        syncDisplayedTotal();
    }

    function refreshDeleteBtns() {
        const btns = itemsContainer.querySelectorAll('.btn-hapus');
        const hasMultiple = btns.length > 1;
        btns.forEach((b) => {
            b.disabled = !hasMultiple;
        });
    }

    // Negosiasi code functions and listeners have been removed

    orderForm.addEventListener('submit', function(event) {
        const isBeli = currentTab === 'beli';
        const hasDirectProductSelection = PREFILLED_ORDER_ITEMS.length > 0 || hasSelectedProduct();
        const submitSource = event.submitter?.dataset.submitSource || 'normal';

        if (isBeli && !(CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS) && !hasDirectProductSelection) {
            event.preventDefault();
            window.location.href = IS_AUTHENTICATED ? PRODUCT_PAGE_URL : LOGIN_PAGE_URL;
            return;
        }

        inpSubmitSource.value = submitSource;
        updateCombinedAddress();

        if (!inpAlamatMaps.value.trim() || !inpAlamatDetail.value.trim()) {
            showAppAlert('Lokasi pengiriman belum lengkap. Silakan pilih lokasi pengiriman terlebih dahulu.', 'warning', 'Peringatan');
            event.preventDefault();
            return;
        }

        if (!isBeli) {
            const serviceState = buildServiceState();

            document.querySelectorAll('[name^="items["]').forEach((field) => { field.disabled = true; });
            inpTipeHarga.value = 'normal';
            document.getElementById('inp-use-cart-checkout').value = '0';

            if (!serviceState.lineItems.length) {
                showAppAlert(
                    serviceState.kategori === 'service'
                        ? 'Tambahkan minimal satu item service terlebih dahulu.'
                        : 'Tambahkan minimal satu item refill terlebih dahulu.',
                    'warning',
                    'Peringatan'
                );
                event.preventDefault();
                return;
            }

            if (serviceState.kategori === 'refill' && serviceState.lineItems.some((item) => !item.refill || !item.ukuran)) {
                showAppAlert('Lengkapi jenis refill dan ukuran APAR untuk semua item refill.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }

            if (serviceState.kategori === 'service' && serviceState.lineItems.some((item) => !item.paket || !item.ukuran)) {
                showAppAlert('Lengkapi paket service dan ukuran APAR untuk semua item service.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }

            if (!serviceState.ukuran || !serviceState.qty) {
                showAppAlert('Lengkapi ukuran APAR dan jumlah unit untuk semua item layanan.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }

            if (serviceState.kategori === 'refill' && serviceState.totalKg <= 0) {
                showAppAlert('Kebutuhan refill belum bisa dihitung. Pastikan ukuran APAR pada setiap item sudah benar.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }

            if (!serviceState.totalPrice || serviceState.totalPrice <= 0) {
                showAppAlert('Harga layanan untuk item yang dipilih belum tersedia.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }

            if (shippingMethod === 'diantar') {
                if (!shippingQuoteReady) {
                    showAppAlert('Silakan hitung biaya penjemputan terlebih dahulu.', 'warning', 'Peringatan');
                    event.preventDefault();
                    return;
                }

                inpOngkir.value = String(shippingCost);
                inpShippingDistance.value = String(shippingRoundTripDistanceKm || shippingDistanceKm);
                if (inpShippingCourier) inpShippingCourier.value = shippingCourierCode;
                if (inpShippingService) inpShippingService.value = shippingServiceName;
                if (inpShippingEtd) inpShippingEtd.value = shippingEtd;
                if (inpShippingWeight) inpShippingWeight.value = String(shippingWeight || 0);
            } else {
                inpOngkir.value = '0';
                inpShippingDistance.value = '0';
                if (inpShippingCourier) inpShippingCourier.value = '';
                if (inpShippingService) inpShippingService.value = '';
                if (inpShippingEtd) inpShippingEtd.value = '';
                if (inpShippingWeight) inpShippingWeight.value = '0';
            }

            if (!inpBank.value) {
                showBankError('Pilih bank tujuan terlebih dahulu untuk melanjutkan pemesanan.');
                event.preventDefault();
                return;
            }

            if (serviceJenisAparHidden) {
                serviceJenisAparHidden.value = [...new Set(serviceState.lineItems
                    .map((item) => item.label || '')
                    .filter(Boolean))]
                    .join(', ')
                    .slice(0, 120);
            }

            return;
        }

        if (shippingMethod === 'diantar') {
            if (!shippingQuoteReady) {
                showAppAlert('Silakan hitung ongkir terlebih dahulu.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }
            inpOngkir.value = String(shippingCost);
            inpShippingDistance.value = String(shippingDistanceKm);
            if (inpShippingCourier) inpShippingCourier.value = shippingCourierCode;
            if (inpShippingService) inpShippingService.value = shippingServiceName;
            if (inpShippingEtd) inpShippingEtd.value = shippingEtd;
            if (inpShippingWeight) inpShippingWeight.value = String(shippingWeight || 0);
        } else {
            inpOngkir.value = '0';
            inpShippingDistance.value = '0';
            if (inpShippingCourier) inpShippingCourier.value = '';
            if (inpShippingService) inpShippingService.value = '';
            if (inpShippingEtd) inpShippingEtd.value = '';
            if (inpShippingWeight) inpShippingWeight.value = '0';
        }

        document.getElementById('inp-use-cart-checkout').value = CAN_USE_CART_CHECKOUT && CART_HAS_ITEMS ? '1' : '0';

        if (!inpBank.value) {
            showBankError('Pilih bank tujuan terlebih dahulu untuk melanjutkan pemesanan.');
            event.preventDefault();
            return;
        }

        if (promoDiscountNominal > 0) {
            if (inpTipeHarga) inpTipeHarga.value = 'promo';
        } else {
            if (inpTipeHarga) inpTipeHarga.value = 'normal';
        }

        if (submitSource === 'special_price_request') {
            if (normalTotal <= 5000000) {
                showAppAlert('Ajukan Harga Khusus hanya tersedia jika subtotal produk lebih dari Rp 5.000.000.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }

            const hargaPengajuan = formatMoneyInput(inpHargaPengajuan);
            if (!hargaPengajuan || hargaPengajuan <= 0) {
                showAppAlert('Isi Harga Pengajuan terlebih dahulu.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }

            if (hargaPengajuan > normalTotal) {
                showAppAlert('Harga Pengajuan tidak boleh lebih besar dari subtotal harga dasar.', 'warning', 'Peringatan');
                event.preventDefault();
                return;
            }
        }
    });

    if (btnTogglePriceRequest) {
        btnTogglePriceRequest.addEventListener('click', function() {
            purchasePriceRequestOpen = !purchasePriceRequestOpen;
            syncPurchasePriceRequestState();
        });
    }

    if (inpHargaPengajuan) {
        inpHargaPengajuan.addEventListener('input', function() {
            formatMoneyInput(this);
        });
        formatMoneyInput(inpHargaPengajuan);
    }

    if (locationSearchInput) {
        locationSearchInput.addEventListener('input', scheduleAddressSuggestSearch);
        locationSearchInput.addEventListener('focus', function() {
            if ((this.value || '').trim().length >= 3 && addressSuggestionItems.length) {
                addressHelper.classList.remove('hidden');
            }
        });
        locationSearchInput.addEventListener('blur', function() {
            setTimeout(hideAddressSuggestions, 300);
        });
    }

    addressHelper.addEventListener('mousedown', function(event) {
        event.preventDefault();
        const target = event.target.closest('button[data-address-index]');
        if (!target) return;
        const idx = Number(target.dataset.addressIndex || -1);
        if (idx < 0 || idx >= addressSuggestionItems.length) return;
        const selected = addressSuggestionItems[idx];
        updateLocationSelectionHelper('Lokasi pengiriman dipilih. Peta sedang menyesuaikan posisi.', 'info');
        void syncMapToSelectedLocation(selected);
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
            updateServiceSummary();
        });
    }
    if (serviceJenisAparManual) {
        serviceJenisAparManual.addEventListener('change', function() {
            syncServiceJenisAparValue();
            syncManualServiceSizeOptions();
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
    if (serviceRefillItemsContainer) {
        serviceRefillItemsContainer.querySelectorAll('.service-refill-item-row').forEach(attachRefillRowEvents);
        refreshDynamicRemoveButtons(serviceRefillItemsContainer, '.btn-remove-refill-item');
    }
    if (serviceServiceItemsContainer) {
        serviceServiceItemsContainer.querySelectorAll('.service-service-item-row').forEach(attachServiceRowEvents);
        refreshDynamicRemoveButtons(serviceServiceItemsContainer, '.btn-remove-service-item');
    }
    if (btnAddRefillItem) {
        btnAddRefillItem.addEventListener('click', appendManualRefillRow);
    }
    if (btnAddServiceItem) {
        btnAddServiceItem.addEventListener('click', appendManualServiceRow);
    }
    serviceMethodRadios.forEach((radio) => {
        radio.addEventListener('change', updateServiceSummary);
    });
    shippingMethodRadios.forEach((radio) => {
        radio.addEventListener('change', function() {
            setShippingMethod(this.value);
        });
    });
    bankRadios.forEach((radio) => {
        radio.addEventListener('change', function() {
            setSelectedBank(this.value);
        });
    });
    if (inpMetodePengiriman.value) {
        setShippingMethod(inpMetodePengiriman.value);
    }

    if (btnCheckOngkir) btnCheckOngkir.addEventListener('click', checkShippingQuote);


    // Init map
    const initialLat = Number(inpAlamatLat.value || DEFAULT_MAP_LAT || -6.2088);
    const initialLng = Number(inpAlamatLng.value || DEFAULT_MAP_LNG || 106.8456);
    const hasInitialCoordinates = String(inpAlamatLat.value || '').trim() !== '' && String(inpAlamatLng.value || '').trim() !== '';
    initLeafletMap(initialLat, initialLng, hasInitialCoordinates);
    if (hasInitialCoordinates) {
        updateOrderCoord(initialLat, initialLng);
    } else {
        if (orderMapLatEl) orderMapLatEl.textContent = '-';
        if (orderMapLngEl) orderMapLngEl.textContent = '-';
    }

    // Init items
    if (btnTambahItem && itemsContainer && tmplRow) {
        createRow();
        btnTambahItem.addEventListener('click', function() {
            invalidatePricingByItemChange();
            createRow();
            recalcGlobal();
        });
    }

    if (locationSearchInput && locationSearchInput.value.trim().length >= 3 && !inpShippingDestinationId.value) {
        fetchAddressSuggestions(locationSearchInput.value.trim());
    }

    updateSelectedAddressPreview(inpAlamatMaps.value || '');
    updateCombinedAddress();
    setShippingMethod(inpMetodePengiriman.value || 'pickup');
    setSelectedBank(inpBank.value || '');
    switchTab(document.getElementById('inp-tipe').value || 'beli');
    recalcGlobal();
})();
</script>

@endsection
