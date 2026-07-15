<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.jenis-refill.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Tambah Jenis Refill</h2>
                <p class="text-sm text-gray-500 font-medium">Buat jenis refill baru untuk dipakai di sistem</p>
            </div>
        </div>
    </x-slot>

    @php
        $formatRupiahInput = static function ($value): string {
            if (is_null($value) || $value === '') {
                return '';
            }
            if (is_numeric($value)) {
                return 'Rp ' . number_format(floor((float) $value), 0, ',', '.');
            }
            $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
            return $digits !== '' ? 'Rp ' . number_format((float) $digits, 0, ',', '.') : '';
        };
    @endphp

    <div class="max-w-3xl">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.jenis-refill.store') }}" method="POST" class="p-12 space-y-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label for="nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Jenis Refill</label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                            placeholder="Contoh: Dry Chemical Powder">
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <label for="satuan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Satuan</label>
                        <select name="satuan" id="satuan" required
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                            <option value="kg" {{ old('satuan') == 'kg' ? 'selected' : '' }}>Kg (Kilogram)</option>
                            <option value="kg" {{ old('satuan') == 'kg' ? 'selected' : '' }}>kg</option>
                        </select>
                        <x-input-error :messages="$errors->get('satuan')" class="mt-2" />
                    </div>

                    <div>
                        <label for="harga" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Harga Standar per Satuan</label>
                        <div class="relative">
                            <input type="text" name="harga" id="harga" value="{{ $formatRupiahInput(old('harga', 0)) }}" required inputmode="numeric"
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                placeholder="Rp 0">
                        </div>
                        <x-input-error :messages="$errors->get('harga')" class="mt-2" />
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <p class="text-sm font-bold text-amber-800">Informasi Data Layanan</p>
                            <p class="text-xs text-amber-700 mt-1">Data layanan jenis refill hanya menyimpan nama, satuan, dan harga standar. Stok dikelola melalui menu <strong>Pengeluaran</strong> dan dipantau di menu <strong>Stok</strong>.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <a href="{{ route('admin.jenis-refill.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Simpan Jenis Refill
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const hargaInput = document.getElementById('harga');

        if (hargaInput) {
            const formatRupiahInput = (value) => {
                const digits = String(value || '').replace(/\D+/g, '');
                return digits ? `Rp ${new Intl.NumberFormat('id-ID').format(Number(digits))}` : '';
            };

            hargaInput.addEventListener('input', () => {
                hargaInput.value = formatRupiahInput(hargaInput.value);
            });

            hargaInput.value = formatRupiahInput(hargaInput.value);
        }
    </script>
</x-app-layout>
