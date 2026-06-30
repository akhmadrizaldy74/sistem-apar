@csrf
@if($isEdit)
    @method('PUT')
@endif

<div class="space-y-8">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-slate-900">Informasi Jenis Service</h3>
            <div class="mt-6 space-y-5">
                <div>
                    <label for="nama" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Service</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $servicePaket->nama) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-900 focus:border-red-500 focus:ring-red-500/20">
                </div>
                <div>
                    <label for="label" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Label Ringkas</label>
                    <input type="text" id="label" name="label" value="{{ old('label', $servicePaket->label) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-900 focus:border-red-500 focus:ring-red-500/20">
                </div>
                <div>
                    <label for="harga" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Harga Dasar</label>
                    <input type="number" id="harga" name="harga" min="0" step="0.01" value="{{ old('harga', $servicePaket->harga) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-900 focus:border-red-500 focus:ring-red-500/20">
                </div>
                <div>
                    <label for="rincian_layanan" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Rincian Layanan</label>
                    <textarea id="rincian_layanan" name="rincian_layanan" rows="6" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 focus:border-red-500 focus:ring-red-500/20">{{ old('rincian_layanan', $servicePaket->rincian_layanan) }}</textarea>
                    <p class="mt-2 text-xs font-semibold text-slate-500">Pisahkan poin layanan per baris agar mudah ditampilkan di invoice dan form service.</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-slate-900">Peralatan Service</h3>
            <p class="mt-2 text-sm font-semibold text-slate-500">Pilih peralatan yang dipakai jenis service ini beserta estimasi jumlahnya per unit pekerjaan.</p>
            <div class="mt-6 space-y-4">
                @forelse($peralatans as $peralatan)
                    @php
                        $checked = array_key_exists($peralatan->id, $selectedPeralatan) || in_array((string) $peralatan->id, array_map('strval', old('peralatan_ids', [])), true);
                        $jumlahValue = old('jumlah_estimasi.' . $peralatan->id, $selectedPeralatan[$peralatan->id] ?? 1);
                    @endphp
                    <label class="flex items-start gap-4 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-red-200 hover:bg-red-50/40">
                        <input type="checkbox" name="peralatan_ids[]" value="{{ $peralatan->id }}" @checked($checked) class="mt-1 h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-slate-900">{{ $peralatan->nama }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Harga standar Rp {{ number_format((float) $peralatan->harga_standar, 0, ',', '.') }} • Stok {{ (int) $peralatan->stok }}</p>
                        </div>
                        <div class="w-24">
                            <label for="jumlah_estimasi_{{ $peralatan->id }}" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Qty</label>
                            <input type="number" id="jumlah_estimasi_{{ $peralatan->id }}" name="jumlah_estimasi[{{ $peralatan->id }}]" min="1" step="1" value="{{ $jumlahValue }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-black text-slate-900 focus:border-red-500 focus:ring-red-500/20">
                        </div>
                    </label>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm font-semibold text-slate-500">
                        Belum ada peralatan service. Tambahkan dulu lewat menu Master Service &amp; Peralatan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.peralatan.index', ['tab' => 'jenis-service']) }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-xs font-black uppercase tracking-widest text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">Batal</a>
        <button type="submit" class="rounded-2xl bg-red-600 px-6 py-3 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Jenis Service' }}
        </button>
    </div>
</div>
