<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.purchase-orders.show', $purchaseOrder->id) }}" class="p-2.5 bg-white rounded-2xl border border-slate-200 text-slate-400 hover:text-red-700 hover:border-red-200 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Edit Purchase Order</h2>
                <p class="text-sm text-slate-500 font-semibold">Perbarui item dan informasi Purchase Order {{ $purchaseOrder->nomor_po }}.</p>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('admin.purchase-orders.update', $purchaseOrder->id) }}" method="POST" id="po-form" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Section Kiri: Informasi PO -->
            <div class="lg:col-span-1 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-6 h-fit">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-wide">Informasi PO</h3>
                    <p class="text-xs text-slate-400 font-semibold">No: {{ $purchaseOrder->nomor_po }}</p>
                </div>

                <!-- Supplier -->
                <div>
                    <label for="supplier_id" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                        Pilih Supplier <span class="text-red-600">*</span>
                    </label>
                    <select name="supplier_id" id="supplier_id" required
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama_supplier }} (WA: {{ $supplier->no_wa }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                </div>

                <!-- Tanggal PO -->
                <div>
                    <label for="tanggal_po" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                        Tanggal PO <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="tanggal_po" id="tanggal_po" value="{{ old('tanggal_po', $purchaseOrder->tanggal_po ? $purchaseOrder->tanggal_po->format('Y-m-d') : date('Y-m-d')) }}" required
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                    <x-input-error :messages="$errors->get('tanggal_po')" class="mt-2" />
                </div>

                <!-- Catatan -->
                <div>
                    <label for="catatan" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea name="catatan" id="catatan" rows="4"
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-semibold text-slate-900 placeholder:text-slate-300 transition outline-none"
                        placeholder="Catatan tambahan untuk supplier...">{{ old('catatan', $purchaseOrder->catatan) }}</textarea>
                    <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                </div>
            </div>

            <!-- Section Kanan: Item Pembelian -->
            <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-wide">Item Pembelian</h3>
                        <p class="text-xs text-slate-400 font-semibold">Ubah atau tambah item pesanan</p>
                    </div>
                    <button type="button" id="btn-add-item" class="px-5 py-2.5 bg-red-50 text-red-700 font-bold rounded-xl hover:bg-red-100 transition flex items-center gap-1.5 text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Tambah Baris
                    </button>
                </div>

                <!-- Table Item Pembelian -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left" id="table-items">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest w-1/3">Nama Item</th>
                                <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest w-1/5">Kategori</th>
                                <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center w-24">Jumlah</th>
                                <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest w-36">Harga Satuan (Rp)</th>
                                <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest w-36">Subtotal</th>
                                <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center w-12">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="item-rows-container" class="divide-y divide-slate-100">
                            <!-- Populated via JS -->
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50/50">
                                <td colspan="4" class="py-4 text-right pr-4 font-black text-slate-700 uppercase tracking-wider text-xs">Total Keseluruhan:</td>
                                <td colspan="2" class="py-4 font-black text-xl text-red-700" id="grand-total-display">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <x-input-error :messages="$errors->get('items')" class="mt-2" />

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.purchase-orders.show', $purchaseOrder->id) }}" class="px-8 py-4 bg-slate-600 text-white font-black rounded-2xl hover:bg-slate-700 transition uppercase tracking-widest text-xs">
                        Batal
                    </a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Update PO
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('item-rows-container');
            const btnAdd = document.getElementById('btn-add-item');
            const grandTotalDisplay = document.getElementById('grand-total-display');
            let itemIndex = 0;

            const existingDetails = @json($purchaseOrder->details);
            const itemsCache = {};

            function formatRupiah(number) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
            }

            function formatRupiahInput(value) {
                const raw = String(value ?? '').replace(/\D/g, '');
                if (!raw) return '';
                return new Intl.NumberFormat('id-ID').format(raw);
            }

            function calculateTotals() {
                let grandTotal = 0;
                const rows = container.querySelectorAll('.item-row');

                rows.forEach(row => {
                    const jumlahInput = row.querySelector('.item-jumlah');
                    const hargaInput = row.querySelector('.item-harga');
                    const subtotalDisplay = row.querySelector('.item-subtotal-display');

                    const jumlah = parseFloat(jumlahInput.value) || 0;
                    const harga = parseFloat(hargaInput.value) || 0;
                    const subtotal = jumlah * harga;

                    grandTotal += subtotal;
                    subtotalDisplay.value = formatRupiah(subtotal);
                });

                grandTotalDisplay.textContent = formatRupiah(grandTotal);
            }

            function setHargaValue(row, val) {
                const hiddenHarga = row.querySelector('.item-harga');
                const displayHarga = row.querySelector('.item-harga-display');
                const numeric = parseFloat(val) || 0;
                hiddenHarga.value = numeric;
                displayHarga.value = numeric > 0 ? formatRupiahInput(numeric) : '';
                calculateTotals();
            }

            async function fetchItemsForCategory(kategori) {
                if (itemsCache[kategori]) {
                    return itemsCache[kategori];
                }

                try {
                    const response = await fetch(`/admin/purchase-orders/get-items/${kategori}`);
                    if (!response.ok) throw new Error('Network error');
                    const data = await response.json();
                    itemsCache[kategori] = data;
                    return data;
                } catch (e) {
                    console.error('Gagal mengambil item:', e);
                    return [];
                }
            }

            async function populateItemDropdown(row, kategori, selectedNama = '') {
                const selectNama = row.querySelector('.item-nama-select');
                const hiddenHarga = row.querySelector('.item-harga');

                selectNama.innerHTML = '<option value="">-- Memuat item... --</option>';

                const items = await fetchItemsForCategory(kategori);

                selectNama.innerHTML = '<option value="">-- Pilih Item --</option>';
                let foundMatch = false;

                items.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.nama;
                    opt.textContent = item.nama;
                    opt.dataset.harga = item.harga || 0;

                    if (selectedNama && (selectedNama === item.nama || item.nama.includes(selectedNama))) {
                        opt.selected = true;
                        foundMatch = true;
                    }
                    selectNama.appendChild(opt);
                });

                if (selectedNama && !foundMatch) {
                    const opt = document.createElement('option');
                    opt.value = selectedNama;
                    opt.textContent = selectedNama + ' (Item Khusus)';
                    opt.dataset.harga = hiddenHarga.value || 0;
                    opt.selected = true;
                    selectNama.appendChild(opt);
                }

                calculateTotals();
            }

            async function addRow(nama = '', kategori = 'produk', jumlah = 1, harga = 0) {
                const rowId = itemIndex++;
                const tr = document.createElement('tr');
                tr.className = 'item-row hover:bg-slate-50/50 transition-colors';
                tr.innerHTML = `
                    <td class="py-3 pr-3">
                        <select name="items[${rowId}][nama_item]" required class="item-nama-select w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-red-500 outline-none">
                            <option value="">-- Memuat... --</option>
                        </select>
                    </td>
                    <td class="py-3 pr-3">
                        <select name="items[${rowId}][kategori]" required class="item-kategori-select w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-red-500 outline-none">
                            <option value="produk" ${kategori === 'produk' ? 'selected' : ''}>Produk</option>
                            <option value="refill" ${kategori === 'refill' ? 'selected' : ''}>Refill</option>
                            <option value="peralatan" ${kategori === 'peralatan' ? 'selected' : ''}>Peralatan</option>
                        </select>
                    </td>
                    <td class="py-3 pr-3">
                        <input type="number" step="any" name="items[${rowId}][jumlah]" value="${jumlah || 1}" required min="1"
                            class="item-jumlah w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 text-center focus:bg-white focus:border-red-500 outline-none" />
                    </td>
                    <td class="py-3 pr-3">
                        <input type="text" inputmode="numeric" class="item-harga-display w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-red-500 outline-none" value="${harga > 0 ? formatRupiahInput(harga) : ''}" placeholder="0" />
                        <input type="hidden" name="items[${rowId}][harga_satuan]" class="item-harga" value="${harga}" />
                    </td>
                    <td class="py-3 pr-3">
                        <input type="text" readonly class="item-subtotal-display w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-black text-slate-700 outline-none" value="Rp 0" />
                    </td>
                    <td class="py-3 text-center">
                        <button type="button" class="btn-remove-row p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition" title="Hapus Baris">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </td>
                `;

                container.appendChild(tr);

                const selectKategori = tr.querySelector('.item-kategori-select');
                const selectNama = tr.querySelector('.item-nama-select');
                const displayHarga = tr.querySelector('.item-harga-display');

                selectKategori.addEventListener('change', function () {
                    populateItemDropdown(tr, this.value);
                });

                selectNama.addEventListener('change', function () {
                    const opt = this.selectedOptions[0];
                    if (opt && opt.dataset.harga) {
                        setHargaValue(tr, opt.dataset.harga);
                    }
                    calculateTotals();
                });

                displayHarga.addEventListener('input', function () {
                    const rawDigits = this.value.replace(/\D/g, '');
                    setHargaValue(tr, rawDigits);
                });

                tr.querySelector('.item-jumlah').addEventListener('input', calculateTotals);
                tr.querySelector('.item-jumlah').addEventListener('change', calculateTotals);
                tr.querySelector('.btn-remove-row').addEventListener('click', function () {
                    if (container.querySelectorAll('.item-row').length > 1) {
                        tr.remove();
                        calculateTotals();
                    } else {
                        alert('Minimal 1 item harus diisi.');
                    }
                });

                await populateItemDropdown(tr, kategori, nama);
                if (harga > 0) {
                    setHargaValue(tr, harga);
                }
            }

            btnAdd.addEventListener('click', function () {
                addRow();
            });

            if (existingDetails && existingDetails.length > 0) {
                existingDetails.forEach(async item => {
                    await addRow(item.nama_item, item.kategori, item.jumlah, item.harga_satuan);
                });
            } else {
                addRow();
            }
        });
    </script>
    @endpush
</x-app-layout>
