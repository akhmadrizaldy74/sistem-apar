@php
    $serviceKategoriOld = old('service_jenis_layanan', 'refill');
    $oldRefillItems = collect(old('service_refill_items', []))
        ->map(function ($item) {
            return [
                'jenis_refill_id' => (string) ($item['jenis_refill_id'] ?? ''),
                'ukuran_apar' => (string) ($item['ukuran_apar'] ?? ''),
                'jumlah_unit' => max(1, (int) ($item['jumlah_unit'] ?? 1)),
            ];
        })
        ->filter(fn (array $item) => $item['jenis_refill_id'] !== '' || $item['ukuran_apar'] !== '')
        ->values();
    $oldServiceItems = collect(old('service_service_items', []))
        ->map(function ($item) {
            return [
                'jenis_apar' => (string) ($item['jenis_apar'] ?? ''),
                'service_paket_id' => (string) ($item['service_paket_id'] ?? ''),
                'ukuran_apar' => (string) ($item['ukuran_apar'] ?? ''),
                'jumlah_unit' => max(1, (int) ($item['jumlah_unit'] ?? 1)),
            ];
        })
        ->filter(fn (array $item) => $item['service_paket_id'] !== '' || $item['ukuran_apar'] !== '' || $item['jenis_apar'] !== '')
        ->values();

    if ($oldRefillItems->isEmpty()) {
        $oldRefillItems = collect([[
            'jenis_refill_id' => '',
            'ukuran_apar' => '',
            'jumlah_unit' => 1,
        ]]);
    }

    if ($oldServiceItems->isEmpty()) {
        $oldServiceItems = collect([[
            'jenis_apar' => '',
            'service_paket_id' => '',
            'ukuran_apar' => '',
            'jumlah_unit' => 1,
        ]]);
    }
@endphp

<div id="section-service-inline" class="order-section-card p-5 md:p-6 hidden">
    <input type="hidden" name="service_jenis_apar" id="service-jenis-apar-hidden" value="{{ old('service_jenis_apar') }}">

    <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100">
        <div class="section-icon-wrap bg-blue-50 text-blue-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <div class="flex-1">
            <h2 class="font-black text-slate-900 text-lg leading-none">Layanan APAR</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Pilih layanan, tambahkan beberapa item sekaligus, lalu cek total otomatis sebelum lanjut ke pembayaran.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="order-label">Kategori Layanan <span>*</span></label>
            <select name="service_jenis_layanan" id="service-jenis-layanan" class="order-input">
                <option value="refill" {{ $serviceKategoriOld === 'refill' ? 'selected' : '' }}>Refill APAR</option>
                <option value="service" {{ $serviceKategoriOld === 'service' ? 'selected' : '' }}>Service APAR</option>
            </select>
        </div>

        <div id="service-refill-fields" class="md:col-span-2 space-y-4">
            <div class="flex justify-end">
                <button type="button" id="btn-add-refill-item" class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-black text-blue-700 transition hover:bg-blue-100">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Tambah Item
                </button>
            </div>

            <div id="service-refill-items" class="space-y-4">
                @foreach($oldRefillItems as $index => $item)
                    <div class="service-refill-item-row rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1.15fr)_minmax(0,0.9fr)_140px_auto] md:items-end">
                            <div>
                                <label class="order-label">Jenis Refill <span>*</span></label>
                                <select name="service_refill_items[{{ $index }}][jenis_refill_id]" class="order-input service-refill-item-select">
                                    <option value="">-- Pilih Jenis Refill --</option>
                                    @foreach($jenisRefills as $jenisRefill)
                                        <option value="{{ $jenisRefill->id }}" {{ $item['jenis_refill_id'] === (string) $jenisRefill->id ? 'selected' : '' }}>{{ $jenisRefill->nama_label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="order-label">Ukuran APAR <span>*</span></label>
                                <select name="service_refill_items[{{ $index }}][ukuran_apar]" class="order-input service-refill-item-size">
                                    <option value="">-- Pilih Ukuran APAR --</option>
                                    @foreach($serviceUkuranOptions as $ukuran)
                                        <option value="{{ $ukuran }}" {{ $item['ukuran_apar'] === $ukuran ? 'selected' : '' }}>{{ $ukuran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="order-label">Jumlah Unit <span>*</span></label>
                                <input type="number" min="1" name="service_refill_items[{{ $index }}][jumlah_unit]" value="{{ $item['jumlah_unit'] }}" class="order-input service-refill-item-qty">
                            </div>
                            <button type="button" class="btn-remove-refill-item inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-100">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                                Hapus
                            </button>
                        </div>
                        <p class="service-item-subtotal mt-3 text-sm font-semibold text-slate-500">Harga dan subtotal item refill akan dihitung otomatis.</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Harga Standar Refill</p>
                <p id="service-refill-price-note" class="mt-2 text-sm font-semibold leading-relaxed text-slate-700">Setiap item refill dihitung berdasarkan jenis refill, ukuran APAR, dan jumlah unit yang Anda isi.</p>
            </div>

            <x-input-error :messages="$errors->get('service_refill_items')" class="mt-2" />
            <x-input-error :messages="$errors->get('service_refill_items.*.jenis_refill_id')" class="mt-2" />
            <x-input-error :messages="$errors->get('service_refill_items.*.ukuran_apar')" class="mt-2" />
            <x-input-error :messages="$errors->get('service_refill_items.*.jumlah_unit')" class="mt-2" />
        </div>

        <div id="service-service-fields" class="md:col-span-2 space-y-4 hidden">
            <div class="flex justify-end">
                <button type="button" id="btn-add-service-item" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-black text-emerald-700 transition hover:bg-emerald-100 shadow-sm">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Tambah Item
                </button>
            </div>

            <div id="service-service-items" class="space-y-4">
                @foreach($oldServiceItems as $index => $item)
                    <div class="service-service-item-row rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)_120px_minmax(0,1.15fr)_auto] md:items-end">
                            <div>
                                <label class="order-label">Jenis APAR <span>*</span></label>
                                <select name="service_service_items[{{ $index }}][jenis_apar]" class="order-input service-service-item-jenis">
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="Powder" {{ ($item['jenis_apar'] ?? '') === 'Powder' ? 'selected' : '' }}>Powder</option>
                                    <option value="CO2" {{ ($item['jenis_apar'] ?? '') === 'CO2' ? 'selected' : '' }}>CO2</option>
                                    <option value="Foam" {{ ($item['jenis_apar'] ?? '') === 'Foam' ? 'selected' : '' }}>Foam</option>
                                </select>
                            </div>
                            <div>
                                <label class="order-label">Ukuran APAR <span>*</span></label>
                                <select name="service_service_items[{{ $index }}][ukuran_apar]" class="order-input service-service-item-size">
                                    <option value="">-- Pilih Ukuran --</option>
                                    @foreach($serviceUkuranOptions as $ukuran)
                                        <option value="{{ $ukuran }}" {{ $item['ukuran_apar'] === $ukuran ? 'selected' : '' }}>{{ $ukuran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="order-label">Jumlah Unit <span>*</span></label>
                                <input type="number" min="1" name="service_service_items[{{ $index }}][jumlah_unit]" value="{{ $item['jumlah_unit'] }}" class="order-input service-service-item-qty">
                            </div>
                            <div>
                                <label class="order-label">Jenis / Paket Service <span>*</span></label>
                                <select name="service_service_items[{{ $index }}][service_paket_id]" class="order-input service-service-item-select">
                                    <option value="">-- Pilih Paket --</option>
                                    @foreach($servicePakets as $servicePaket)
                                        <option value="{{ $servicePaket->id }}" data-nama="{{ $servicePaket->nama }}" {{ $item['service_paket_id'] === (string) $servicePaket->id ? 'selected' : '' }}>{{ $servicePaket->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn-remove-service-item inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-100">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                            <p class="text-xs font-semibold text-slate-500 service-item-price-label">Harga: <span class="text-slate-700 font-bold">-</span></p>
                            <p class="text-sm font-black text-red-600 service-item-subtotal-label">Subtotal: <span>Rp 0</span></p>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-input-error :messages="$errors->get('service_service_items')" class="mt-2" />
            <x-input-error :messages="$errors->get('service_service_items.*.jenis_apar')" class="mt-2" />
            <x-input-error :messages="$errors->get('service_service_items.*.service_paket_id')" class="mt-2" />
            <x-input-error :messages="$errors->get('service_service_items.*.ukuran_apar')" class="mt-2" />
            <x-input-error :messages="$errors->get('service_service_items.*.jumlah_unit')" class="mt-2" />
        </div>

        <div class="md:col-span-2">
            <label class="order-label">Upload Foto APAR <span class="text-slate-300 font-normal normal-case tracking-normal">(Opsional)</span></label>
            <input type="file" name="service_foto" id="service-foto" accept=".jpg,.jpeg,.png,.webp" class="order-input text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-600">
            <p class="text-[11px] font-semibold text-slate-400 mt-1">Foto membantu admin melakukan pemeriksaan awal.</p>
        </div>

        <div class="md:col-span-2">
            <label class="order-label">Catatan / Keluhan</label>
            <textarea name="service_keluhan" id="service-keluhan" rows="3" placeholder="Contoh: tabung perlu refill, minta pengecekan valve, atau ingin dijemput hari kerja." class="order-input resize-none">{{ old('service_keluhan', old('keterangan_service')) }}</textarea>
        </div>
    </div>

    <template id="service-refill-item-template">
        <div class="service-refill-item-row rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1.15fr)_minmax(0,0.9fr)_140px_auto] md:items-end">
                <div>
                    <label class="order-label">Jenis Refill <span>*</span></label>
                    <select name="service_refill_items[__INDEX__][jenis_refill_id]" class="order-input service-refill-item-select">
                        <option value="">-- Pilih Jenis Refill --</option>
                        @foreach($jenisRefills as $jenisRefill)
                            <option value="{{ $jenisRefill->id }}">{{ $jenisRefill->nama_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="order-label">Ukuran APAR <span>*</span></label>
                    <select name="service_refill_items[__INDEX__][ukuran_apar]" class="order-input service-refill-item-size">
                        <option value="">-- Pilih Ukuran APAR --</option>
                        @foreach($serviceUkuranOptions as $ukuran)
                            <option value="{{ $ukuran }}">{{ $ukuran }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="order-label">Jumlah Unit <span>*</span></label>
                    <input type="number" min="1" name="service_refill_items[__INDEX__][jumlah_unit]" value="1" class="order-input service-refill-item-qty">
                </div>
                <button type="button" class="btn-remove-refill-item inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-100">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    Hapus
                </button>
            </div>
            <p class="service-item-subtotal mt-3 text-sm font-semibold text-slate-500">Harga dan subtotal item refill akan dihitung otomatis.</p>
        </div>
    </template>

    <template id="service-service-item-template">
        <div class="service-service-item-row rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)_120px_minmax(0,1.15fr)_auto] md:items-end">
                <div>
                    <label class="order-label">Jenis APAR <span>*</span></label>
                    <select name="service_service_items[__INDEX__][jenis_apar]" class="order-input service-service-item-jenis">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Powder">Powder</option>
                        <option value="CO2">CO2</option>
                        <option value="Foam">Foam</option>
                    </select>
                </div>
                <div>
                    <label class="order-label">Ukuran APAR <span>*</span></label>
                    <select name="service_service_items[__INDEX__][ukuran_apar]" class="order-input service-service-item-size">
                        <option value="">-- Pilih Ukuran --</option>
                        @foreach($serviceUkuranOptions as $ukuran)
                            <option value="{{ $ukuran }}">{{ $ukuran }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="order-label">Jumlah Unit <span>*</span></label>
                    <input type="number" min="1" name="service_service_items[__INDEX__][jumlah_unit]" value="1" class="order-input service-service-item-qty">
                </div>
                <div>
                    <label class="order-label">Jenis / Paket Service <span>*</span></label>
                    <select name="service_service_items[__INDEX__][service_paket_id]" class="order-input service-service-item-select">
                        <option value="">-- Pilih Paket --</option>
                        @foreach($servicePakets as $servicePaket)
                            <option value="{{ $servicePaket->id }}" data-nama="{{ $servicePaket->nama }}">{{ $servicePaket->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn-remove-service-item inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-100">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                <p class="text-xs font-semibold text-slate-500 service-item-price-label">Harga: <span class="text-slate-700 font-bold">-</span></p>
                <p class="text-sm font-black text-red-600 service-item-subtotal-label">Subtotal: <span>Rp 0</span></p>
            </div>
        </div>
    </template>
</div>
