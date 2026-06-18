<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="text-[28px] font-black tracking-tight text-gray-900">Pesanan</h2>
                <p class="text-sm font-medium text-gray-500">Kelola pembelian unit, refill APAR, dan service APAR yang masuk dari pelanggan.</p>
            </div>
        </div>
    </x-slot>

    @php
        $purchasePriceModalState = [
            'order_id' => (int) old('purchase_price_order_id', 0),
            'harga_final' => (string) old('harga_final', ''),
            'catatan_admin' => (string) old('catatan_admin', ''),
            'errors' => [
                'harga_final' => $errors->get('harga_final'),
                'catatan_admin' => $errors->get('catatan_admin'),
            ],
        ];
    @endphp

    <div class="space-y-8">
        <div id="pesanan-summary-cards">
            @include('admin.pesanan.partials.summary-cards', ['summary' => $summary])
        </div>

        @if(session('wa_url'))
            <div class="flex flex-col gap-3 rounded-3xl border border-emerald-200 bg-emerald-50 px-6 py-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-black text-emerald-800">{{ session('wa_title', 'Pesan WhatsApp siap dikirim.') }}</p>
                    <p class="mt-1 text-sm font-medium text-emerald-700">{{ session('wa_description', 'Kirim pesan ini ke pelanggan untuk tindak lanjut pesanan.') }}</p>
                </div>
                <a href="{{ session('wa_url') }}" target="_blank" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-white transition hover:bg-emerald-700">
                    {{ session('wa_button', 'Buka WhatsApp') }}
                </a>
            </div>
        @endif

        <div class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50/60 px-8 py-7">
                <h3 class="text-xl font-black text-gray-900">Daftar Pesanan</h3>
                <p class="mt-1 text-sm font-medium text-gray-500">Daftar pembelian unit, refill APAR, dan service APAR yang perlu diproses admin.</p>
            </div>
            <div class="px-4 pb-4 pt-3">
                <div class="responsive-table-wrap overflow-x-auto overflow-y-visible">
                    <table class="w-full min-w-[980px] table-fixed text-left">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="w-[148px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Tanggal</th>
                                <th class="w-[190px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Pelanggan</th>
                                <th class="w-[150px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Jenis Pesanan</th>
                                <th class="px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Detail Pesanan</th>
                                <th class="w-[142px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Total</th>
                                <th class="w-[170px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Status</th>
                                <th class="w-[228px] px-7 py-5 text-right text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pesanan-active-rows" class="divide-y divide-gray-100">
                            @include('admin.pesanan.partials.active-rows', ['pesananAktif' => $pesananAktif])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50/60 px-8 py-7">
                <h3 class="text-xl font-black text-gray-900">Riwayat Pesanan</h3>
                <p class="mt-1 text-sm font-medium text-gray-500">Riwayat pembelian unit, refill APAR, dan service APAR yang sudah selesai atau ditutup.</p>
            </div>
            <div class="px-4 pb-4 pt-3">
                <div class="responsive-table-wrap overflow-x-auto overflow-y-visible">
                    <table class="w-full min-w-[980px] table-fixed text-left">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="w-[148px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Tanggal</th>
                                <th class="w-[190px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Pelanggan</th>
                                <th class="w-[150px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Jenis Pesanan</th>
                                <th class="px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Detail Pesanan</th>
                                <th class="w-[142px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Total</th>
                                <th class="w-[170px] px-7 py-5 text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Status</th>
                                <th class="w-[228px] px-7 py-5 text-right text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pesanan-history-rows" class="divide-y divide-gray-100">
                            @include('admin.pesanan.partials.history-rows', ['pesananRiwayat' => $pesananRiwayat])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="pesanan-detail-modal" class="fixed inset-0 z-[150] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" onclick="closePesananDetailModal()"></div>
            <div class="relative z-10 max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] border border-gray-100 bg-white shadow-2xl">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white px-6 py-5">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Detail Pesanan</h3>
                        <p id="pesanan-detail-subtitle" class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-gray-400"></p>
                    </div>
                    <button onclick="closePesananDetailModal()" class="rounded-2xl bg-gray-50 p-2 text-gray-400 transition hover:text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="pesanan-detail-content" class="space-y-5 p-6"></div>
            </div>
        </div>

        <div id="pesanan-proof-modal" class="fixed inset-0 z-[160] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-950/70 backdrop-blur-sm" onclick="closePesananProofModal()"></div>
            <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 id="pesanan-proof-title" class="text-xl font-black text-gray-900">Bukti Transfer</h3>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Preview bukti pembayaran pelanggan</p>
                    </div>
                    <button type="button" onclick="closePesananProofModal()" class="rounded-2xl bg-gray-50 p-2 text-gray-400 transition hover:text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="pesanan-proof-body" class="max-h-[78vh] overflow-auto bg-gray-50 p-6"></div>
            </div>
        </div>
    </div>

    @once
        <script>
            window.pesananDetailData = @json($pesananDetailData);

            const purchasePriceModalState = @json($purchasePriceModalState);
            const purchasePriceModalToken = @json(csrf_token());

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function nl2brHtml(value) {
                return escapeHtml(value).replace(/\r\n|\r|\n/g, '<br>');
            }

            function formatRupiahInput(value) {
                const digits = String(value ?? '').replace(/\D+/g, '');
                return digits ? 'Rp ' + Number(digits).toLocaleString('id-ID') : '';
            }

            function purchasePriceStateForOrder(orderId) {
                return Number(purchasePriceModalState.order_id || 0) === Number(orderId)
                    ? purchasePriceModalState
                    : null;
            }

            function attachPurchasePriceInputMask() {
                const input = document.getElementById('purchase-price-final-input');
                if (!input) return;

                const applyFormat = () => {
                    input.value = formatRupiahInput(input.value);
                };

                input.addEventListener('input', applyFormat);
                applyFormat();
            }

            function buildPurchasePriceCardHtml(data) {
                const purchase = data.purchase_price || {};
                if (!purchase.has_request) {
                    return '';
                }

                const modalState = purchasePriceStateForOrder(data.id);
                const hargaFinalValue = modalState
                    ? formatRupiahInput(modalState.harga_final || '')
                    : formatRupiahInput(purchase.final_price || '');
                const catatanAdminValue = modalState
                    ? (modalState.catatan_admin || '')
                    : (purchase.admin_note || '');
                const hargaFinalError = modalState?.errors?.harga_final?.[0] || '';
                const catatanAdminError = modalState?.errors?.catatan_admin?.[0] || '';
                const badgeHtml = purchase.label
                    ? `<span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black ${purchase.badge_classes || 'bg-slate-100 text-slate-700 border border-slate-200'}">${escapeHtml(purchase.label)}</span>`
                    : '';
                const customerNoteHtml = purchase.customer_note
                    ? `
                        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-amber-700">Catatan Pelanggan</p>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-amber-900">${nl2brHtml(purchase.customer_note)}</p>
                        </div>
                    `
                    : '';
                const adminNoteHtml = (purchase.admin_note || catatanAdminValue)
                    ? `
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Catatan Admin</p>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-gray-700">${nl2brHtml(purchase.admin_note || catatanAdminValue)}</p>
                        </div>
                    `
                    : '';

                if (purchase.is_pending) {
                    return `
                        <div class="rounded-[1.5rem] border border-amber-200 bg-white p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-amber-700">Tindak Lanjut Admin</p>
                                    <h4 class="mt-1 text-lg font-black text-gray-900">Pengajuan Harga Pembelian</h4>
                                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-500">Harga pengajuan pelanggan ditampilkan sebagai referensi. Sampai disetujui, total pesanan tetap mengikuti harga normal atau promo otomatis yang berjalan.</p>
                                </div>
                                ${badgeHtml}
                            </div>
                            <div class="mt-4 space-y-4">
                                <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Harga Pengajuan Pelanggan</p>
                                    <p class="mt-2 text-lg font-black text-gray-900">${purchase.requested_price ? `Rp ${escapeHtml(purchase.requested_price)}` : '-'}</p>
                                </div>
                                ${customerNoteHtml}
                                <form method="POST" action="${escapeHtml(purchase.acc_url || '#')}" class="space-y-4">
                                    <input type="hidden" name="_token" value="${escapeHtml(purchasePriceModalToken)}">
                                    <input type="hidden" name="purchase_price_order_id" value="${escapeHtml(data.id)}">
                                    <div>
                                        <label for="purchase-price-final-input" class="mb-2 block text-sm font-bold text-gray-700">Harga Final Admin</label>
                                        <input
                                            type="text"
                                            id="purchase-price-final-input"
                                            name="harga_final"
                                            value="${escapeHtml(hargaFinalValue)}"
                                            placeholder="Rp 0"
                                            inputmode="numeric"
                                            autocomplete="off"
                                            class="w-full rounded-2xl border-gray-200 text-sm font-semibold focus:border-red-500 focus:ring-red-500"
                                        >
                                        ${hargaFinalError ? `<p class="mt-2 text-sm font-semibold text-red-600">${escapeHtml(hargaFinalError)}</p>` : ''}
                                    </div>
                                    <div>
                                        <label for="purchase-price-admin-note" class="mb-2 block text-sm font-bold text-gray-700">Catatan Admin</label>
                                        <textarea
                                            id="purchase-price-admin-note"
                                            name="catatan_admin"
                                            rows="3"
                                            class="w-full rounded-2xl border-gray-200 text-sm focus:border-red-500 focus:ring-red-500"
                                            placeholder="Opsional. Tambahkan alasan singkat jika diperlukan."
                                        >${escapeHtml(catatanAdminValue)}</textarea>
                                        ${catatanAdminError ? `<p class="mt-2 text-sm font-semibold text-red-600">${escapeHtml(catatanAdminError)}</p>` : ''}
                                    </div>
                                    <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-600">
                                        <div class="flex items-center justify-between gap-3">
                                            <span>Total pesanan saat ini</span>
                                            <span class="font-black text-gray-900">Rp ${escapeHtml(purchase.normal_total || data.total || '0')}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-700 shadow-lg shadow-emerald-600/20">
                                            ACC
                                        </button>
                                        <button type="submit" formaction="${escapeHtml(purchase.reject_url || '#')}" formnovalidate class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-black text-white transition hover:bg-red-700 shadow-lg shadow-red-600/20">
                                            Tolak
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;
                }

                const statusSummaryHtml = purchase.is_approved
                    ? `
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                            <p>Total akhir pesanan ini memakai harga final admin. Promo otomatis tetap menjadi pembanding informasi dan tidak dipotong lagi dari harga final.</p>
                        </div>
                        <div class="space-y-2 rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                            <div class="flex items-center justify-between gap-3 text-sm font-semibold text-gray-600">
                                <span>Total pembanding sistem</span>
                                <span class="font-black text-gray-900">Rp ${escapeHtml(purchase.normal_total || data.total || '0')}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-sm font-semibold text-emerald-700">
                                <span>Total akhir yang dipakai</span>
                                <span class="font-black">Rp ${escapeHtml(purchase.current_total || data.total || '0')}</span>
                            </div>
                        </div>
                    `
                    : `
                        <div class="rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            Pengajuan harga tidak digunakan. Pesanan tetap mengikuti harga normal atau promo otomatis sesuai alur lama.
                        </div>
                    `;

                return `
                    <div class="rounded-[1.5rem] border border-gray-200 bg-white p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Riwayat Persetujuan</p>
                                <h4 class="mt-1 text-lg font-black text-gray-900">Pengajuan Harga Pembelian</h4>
                            </div>
                            ${badgeHtml}
                        </div>
                        <div class="mt-4 space-y-4">
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                                <div class="flex items-center justify-between gap-3 text-sm font-semibold text-gray-700">
                                    <span>Harga Pengajuan Pelanggan</span>
                                    <span class="font-black text-gray-900">${purchase.requested_price ? `Rp ${escapeHtml(purchase.requested_price)}` : '-'}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-sm font-semibold text-gray-700">
                                    <span>Harga Final Admin</span>
                                    <span class="font-black ${purchase.is_approved ? 'text-emerald-700' : 'text-gray-900'}">${purchase.final_price ? `Rp ${escapeHtml(purchase.final_price)}` : '-'}</span>
                                </div>
                            </div>
                            ${customerNoteHtml}
                            ${adminNoteHtml}
                            ${statusSummaryHtml}
                        </div>
                    </div>
                `;
            }

            function buildMetaCardsHtml(data) {
                const fields = Array.isArray(data.meta) ? data.meta : [];
                const cards = fields.map((field) => `
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">${escapeHtml(field.label)}</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900">${escapeHtml(field.value)}</p>
                    </div>
                `);

                if (data.hide_payment_badge !== true) {
                    const badge = data.is_paid
                        ? '<span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-black text-emerald-700">Lunas</span>'
                        : '<span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-[11px] font-black text-amber-700">Belum Bayar</span>';

                    cards.push(`
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Status Bayar</p>
                            <div class="mt-2">${badge}</div>
                        </div>
                    `);
                }

                return cards.join('');
            }

            function buildItemsHtml(data) {
                const items = Array.isArray(data.items) ? data.items : [];
                if (!items.length) {
                    return '<p class="py-4 text-center text-sm font-medium text-gray-500">Belum ada rincian pesanan.</p>';
                }

                return items.map((item) => `
                    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 sm:flex sm:items-start sm:justify-between sm:gap-4">
                        <div>
                            <p class="text-sm font-black text-gray-900">${escapeHtml(item.nama)}</p>
                            ${item.meta ? `<p class="mt-1 text-sm font-medium text-gray-500">${escapeHtml(item.meta)}</p>` : ''}
                        </div>
                        <div class="mt-3 text-left sm:mt-0 sm:text-right">
                            <p class="text-sm font-semibold text-gray-500">${escapeHtml(item.qty_label || '-')}</p>
                            <p class="mt-1 text-sm font-black text-gray-900">${escapeHtml(item.subtotal || '-')}</p>
                        </div>
                    </div>
                `).join('');
            }

            function buildSummaryHtml(data) {
                const lines = Array.isArray(data.summary_lines) ? data.summary_lines : [];
                if (!lines.length) {
                    return '';
                }

                const toneClasses = {
                    default: 'text-gray-600',
                    positive: 'text-emerald-700',
                    total: 'border-t border-gray-200 pt-3 text-gray-900',
                };

                return lines.map((line) => {
                    const tone = toneClasses[line.tone] || toneClasses.default;
                    const valueClass = line.tone === 'total'
                        ? 'text-lg font-black text-red-700'
                        : 'font-black text-gray-900';

                    return `
                        <div class="flex items-center justify-between gap-3 text-sm font-semibold ${tone}">
                            <span>${escapeHtml(line.label)}</span>
                            <span class="${valueClass}">${escapeHtml(line.value)}</span>
                        </div>
                    `;
                }).join('');
            }

            function buildUnitDisplayHtml(data) {
                const unitDisplay = data.unit_display || {};
                const entries = Array.isArray(unitDisplay.entries) ? unitDisplay.entries : [];
                if (!entries.length) {
                    return '';
                }

                const entryHtml = entries.map((entry) => `
                    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3">
                        <p class="text-sm font-black text-gray-900">${escapeHtml(entry.label || '-')}</p>
                        ${entry.code ? `<p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">${escapeHtml(entry.code)}</p>` : ''}
                    </div>
                `).join('');

                return `
                    <div class="rounded-[1.5rem] border border-gray-200 bg-gray-50 p-5">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Unit APAR</p>
                                <h4 class="mt-1 text-lg font-black text-gray-900">${escapeHtml(unitDisplay.heading || 'Unit APAR')}</h4>
                            </div>
                            ${unitDisplay.quantity_label ? `<p class="text-sm font-semibold text-gray-500">${escapeHtml(unitDisplay.quantity_label)}</p>` : ''}
                        </div>
                        <div class="mt-4 grid gap-3">${entryHtml}</div>
                    </div>
                `;
            }

            function buildPeralatanHtml(data) {
                const items = Array.isArray(data.peralatan) ? data.peralatan : [];
                if (!items.length) {
                    return '';
                }

                const rows = items.map((item) => `
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-3">
                        <p class="text-sm font-semibold text-gray-900">${escapeHtml(item.nama)}</p>
                        <p class="text-sm font-black text-gray-700">${escapeHtml(item.jumlah)}</p>
                    </div>
                `).join('');

                return `
                    <div class="rounded-[1.5rem] border border-gray-200 bg-gray-50 p-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Peralatan Service</p>
                        <div class="mt-4 grid gap-3">${rows}</div>
                    </div>
                `;
            }

            function buildNoteHtml(data) {
                if (!data.catatan) {
                    return '';
                }

                return `
                    <div class="rounded-[1.5rem] border border-gray-200 bg-gray-50 p-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Catatan</p>
                        <p class="mt-3 text-sm font-medium leading-relaxed text-gray-700">${nl2brHtml(data.catatan)}</p>
                    </div>
                `;
            }

            function buildProofHtml(data) {
                const url = data.proof_url || '';
                if (!url) {
                    return '<p class="py-4 text-center text-sm font-medium text-gray-500">Belum ada bukti pembayaran.</p>';
                }

                const isPdf = /\.pdf($|\?)/i.test(String(url || ''));
                if (isPdf) {
                    return `<iframe src="${url}" class="h-[60vh] w-full rounded-2xl border border-gray-200 bg-white" title="Preview bukti pembayaran"></iframe>`;
                }

                return `<a href="${url}" target="_blank" class="block"><img src="${url}" alt="Bukti pembayaran" class="mx-auto max-h-[60vh] rounded-2xl border border-gray-200 bg-white object-contain"></a>`;
            }

            function openPesananDetailModal(id) {
                const data = (window.pesananDetailData || []).find((item) => item.id === id);
                if (!data) return;

                document.getElementById('pesanan-detail-subtitle').textContent = `${data.label} • ${data.tanggal}`;
                const purchasePriceHtml = buildPurchasePriceCardHtml(data);
                const metaCardsHtml = buildMetaCardsHtml(data);
                const itemsHtml = buildItemsHtml(data);
                const summaryHtml = buildSummaryHtml(data);
                const unitDisplayHtml = buildUnitDisplayHtml(data);
                const peralatanHtml = buildPeralatanHtml(data);
                const noteHtml = buildNoteHtml(data);
                const proofHtml = buildProofHtml(data);

                document.getElementById('pesanan-detail-content').innerHTML = `
                    <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                        <div class="rounded-[1.5rem] border border-gray-100 bg-gray-50 p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Pelanggan</p>
                            <p class="mt-2 text-lg font-black text-gray-900">${escapeHtml(data.pelanggan)}</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">WhatsApp</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">${escapeHtml(data.no_wa)}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Tanggal</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">${escapeHtml(data.tanggal)}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Alamat</p>
                                <p class="mt-1 text-sm font-medium leading-relaxed text-gray-700">${escapeHtml(data.alamat)}</p>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">${metaCardsHtml}</div>
                    </div>

                    <div class="rounded-[1.5rem] border border-gray-200 bg-gray-50 p-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">${escapeHtml(data.items_heading || 'Rincian Pesanan')}</p>
                        <div class="mt-4 space-y-3">${itemsHtml}</div>
                    </div>

                    <div class="rounded-[1.5rem] border border-gray-200 bg-gray-50 p-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">${escapeHtml(data.summary_heading || 'Ringkasan Pembayaran')}</p>
                        <div class="mt-4 space-y-3">${summaryHtml}</div>
                    </div>

                    ${unitDisplayHtml}
                    ${peralatanHtml}
                    ${purchasePriceHtml}
                    ${noteHtml}

                    <div class="rounded-[1.5rem] border border-gray-200 bg-gray-50 p-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Bukti Pembayaran</p>
                        <div class="mt-4">${proofHtml}</div>
                    </div>

                    <div class="flex justify-center">
                        <button type="button" onclick="closePesananDetailModal()" class="rounded-2xl bg-gray-200 px-8 py-3 text-xs font-black uppercase tracking-[0.16em] text-gray-700 transition hover:bg-gray-300">Tutup</button>
                    </div>
                `;

                attachPurchasePriceInputMask();
                document.getElementById('pesanan-detail-modal').classList.remove('hidden');
                document.getElementById('pesanan-detail-modal').classList.add('flex');
            }

            function closePesananDetailModal() {
                const modal = document.getElementById('pesanan-detail-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('pesanan-detail-content').innerHTML = '';
            }

            function openPesananProofModal(url, meta = {}) {
                const modal = document.getElementById('pesanan-proof-modal');
                const body = document.getElementById('pesanan-proof-body');
                const heading = document.getElementById('pesanan-proof-title');
                const isPdf = /\.pdf($|\?)/i.test(String(url || ''));
                const infoHtml = `
                    <div class="rounded-[1.5rem] border border-gray-100 bg-white px-5 py-4">
                        <h4 class="text-lg font-black text-gray-900">Bukti Transfer</h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Pelanggan</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">${escapeHtml(meta.customer || '-')}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Tanggal Transaksi</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">${escapeHtml(meta.date || '-')}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-gray-400">Jenis Pesanan</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">${escapeHtml(meta.type || 'Pesanan')}</p>
                            </div>
                        </div>
                    </div>
                `;

                heading.textContent = 'Bukti Transfer';
                if (!url) {
                    body.innerHTML = `${infoHtml}
                        <div class="rounded-[1.5rem] border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-700">
                            Bukti transfer belum tersedia atau file tidak ditemukan.
                        </div>`;
                } else {
                    body.innerHTML = `${infoHtml}
                        ${isPdf
                            ? `<iframe src="${url}" class="h-[70vh] w-full rounded-[1.5rem] border border-gray-200 bg-white" title="Preview bukti pembayaran"></iframe>`
                            : `<img src="${url}" alt="Preview bukti pembayaran" class="mx-auto max-h-[70vh] rounded-[1.5rem] border border-gray-200 bg-white object-contain">`
                        }`;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closePesananProofModal() {
                const modal = document.getElementById('pesanan-proof-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('pesanan-proof-body').innerHTML = '';
            }

            if (Number(purchasePriceModalState.order_id || 0) > 0) {
                window.addEventListener('load', () => {
                    openPesananDetailModal(Number(purchasePriceModalState.order_id));
                });
            }
        </script>
    @endonce

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.createPollingUpdater({
                    url: @js(route('admin.realtime.pesanan', ['revenue_period' => $summary['revenuePeriod'] ?? 'month'])),
                    interval: 10000,
                    onSuccess(payload) {
                        const summary = document.getElementById('pesanan-summary-cards');
                        const activeRows = document.getElementById('pesanan-active-rows');
                        const historyRows = document.getElementById('pesanan-history-rows');

                        if (summary && typeof payload.summary_html === 'string') {
                            summary.innerHTML = payload.summary_html;
                        }
                        if (activeRows && typeof payload.active_rows_html === 'string') {
                            activeRows.innerHTML = payload.active_rows_html;
                        }
                        if (historyRows && typeof payload.history_rows_html === 'string') {
                            historyRows.innerHTML = payload.history_rows_html;
                        }
                        if (Array.isArray(payload.detail_data)) {
                            window.pesananDetailData = payload.detail_data;
                        }
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
