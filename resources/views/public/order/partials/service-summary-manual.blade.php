<div id="service-summary-card" class="order-section-card hidden p-6">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-sm font-black text-slate-800">Ringkasan Pesanan</p>
            <p class="text-xs font-semibold text-slate-600">Selalu ikut berubah saat item layanan ditambah atau diubah.</p>
        </div>
    </div>

    <div class="summary-card space-y-3">
        <div class="summary-row">
            <span class="text-slate-500 font-semibold">Kategori</span>
            <span id="service-summary-category" class="font-black text-slate-800">Refill APAR</span>
        </div>
        <div class="summary-row">
            <span class="text-slate-500 font-semibold">Ukuran APAR</span>
            <span id="service-summary-size" class="font-black text-slate-800 text-right">-</span>
        </div>
        <div class="summary-row">
            <span class="text-slate-500 font-semibold">Jumlah Unit</span>
            <span id="service-summary-qty" class="font-black text-slate-800">0 unit</span>
        </div>
        <div class="summary-row">
            <span id="service-summary-usage-label" class="text-slate-500 font-semibold">Kebutuhan Refill</span>
            <span id="service-summary-kg" class="font-black text-slate-800 text-right">-</span>
        </div>
        <div class="summary-row">
            <span class="text-slate-500 font-semibold">Metode Penanganan</span>
            <span id="service-summary-method" class="font-black text-slate-800">Dijemput</span>
        </div>
        <div id="service-summary-courier-row" class="summary-row hidden">
            <span class="text-slate-500 font-semibold">Ekspedisi / Layanan</span>
            <span id="service-summary-courier" class="font-black text-slate-800 text-right">-</span>
        </div>
        <div id="service-summary-etd-row" class="summary-row hidden">
            <span class="text-slate-500 font-semibold">Estimasi</span>
            <span id="service-summary-etd" class="font-black text-slate-800">-</span>
        </div>
        <div class="summary-row">
            <span class="text-slate-500 font-semibold">Biaya Penjemputan</span>
            <span id="service-summary-ongkir" class="font-black text-slate-800">Rp 0</span>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Rincian Item</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Setiap item dihitung sendiri lalu dijumlahkan.</p>
                </div>
                <span id="service-summary-item-count" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">0 item</span>
            </div>
            <div id="service-summary-items" class="mt-4 space-y-3">
                <p class="text-sm font-semibold text-slate-500">Belum ada item layanan yang diisi.</p>
            </div>
        </div>

        <div class="summary-row total">
            <span id="service-summary-price-label" class="text-slate-500 font-semibold">Total Pembayaran</span>
            <span id="service-summary-price" class="summary-value-total text-xl font-black text-blue-600">Rp 0</span>
        </div>
    </div>

    <div class="mt-4 space-y-4">
        <button type="submit" id="btn-service-submit" class="btn-primary-action submit service w-full justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span id="btn-service-submit-label">Lanjut ke Pembayaran</span>
        </button>

        <p class="text-[10px] text-slate-400 font-semibold text-center">Pastikan item layanan, metode penanganan, dan bank tujuan sudah sesuai sebelum melanjutkan.</p>
    </div>
</div>
