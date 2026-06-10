<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-4">
                <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="mt-1 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-white text-slate-500 shadow-sm transition hover:border-red-200 hover:text-red-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-gray-900">Edit Pelanggan</h2>
                    <p class="text-sm font-medium text-gray-500">Perbarui data dasar pelanggan tanpa mengubah riwayat pembeliannya.</p>
                </div>
            </div>
            <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-red-100 bg-red-50 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-red-700 shadow-sm transition hover:bg-red-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 113 12a9 9 0 0118 0z" />
                </svg>
                Lihat Detail
            </a>
        </div>
    </x-slot>

    @php
        $waDigits = preg_replace('/\D+/', '', (string) $pelanggan->no_wa);
        $waUrl = $waDigits !== '' ? 'https://wa.me/' . preg_replace('/^0/', '62', $waDigits) : null;
    @endphp

    <div class="space-y-8">
        <section class="overflow-hidden rounded-[2.5rem] border border-white/60 bg-white/80 shadow-xl shadow-slate-200/50 backdrop-blur-md">
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-8 py-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-5">
                        <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-red-500 to-red-700 text-2xl font-black uppercase text-white shadow-lg shadow-red-500/30">
                            {{ strtoupper(substr($pelanggan->nama, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-3xl font-black tracking-tight text-white">{{ $pelanggan->nama }}</h3>
                            <p class="mt-1 text-sm font-medium text-white/60">{{ $pelanggan->user?->email ?: 'Belum ada email pemulihan' }}</p>
                            @if($pelanggan->no_wa)
                                @if($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-white/75 transition hover:text-white">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                        </svg>
                                        {{ $pelanggan->no_wa }}
                                    </a>
                                @else
                                    <p class="mt-2 text-sm font-semibold text-white/75">{{ $pelanggan->no_wa }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex w-fit rounded-2xl border border-blue-400/30 bg-blue-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-blue-200">
                        Edit Data Dasar
                    </span>
                </div>
            </div>
        </section>

        <form action="{{ route('admin.pelanggan.update', $pelanggan) }}" method="POST" class="grid grid-cols-1 gap-8 xl:grid-cols-[1.1fr_0.9fr]">
            @csrf
            @method('PUT')

            <section class="rounded-[2.5rem] border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nama" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama', $pelanggan->nama) }}" required class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <label for="no_wa" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">WhatsApp / HP <span class="text-red-500">*</span></label>
                        <input type="text" id="no_wa" name="no_wa" value="{{ old('no_wa', $pelanggan->no_wa) }}" required placeholder="08xxxxxxxxxx" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                        <x-input-error :messages="$errors->get('no_wa')" class="mt-2" />
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $pelanggan->user?->email) }}" placeholder="email@contoh.com" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                        <p class="mt-2 text-[10px] font-semibold text-slate-400">Opsional. Jika diisi, email ini dipakai sebagai email pemulihan login pelanggan.</p>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="alamat_maps" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat Pengiriman</label>
                        <textarea id="alamat_maps" name="alamat_maps" rows="3" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400">{{ old('alamat_maps', $pelanggan->alamat_maps ?: $pelanggan->alamat) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat_maps')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="alamat_detail" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Detail / Patokan</label>
                        <textarea id="alamat_detail" name="alamat_detail" rows="3" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400">{{ old('alamat_detail', $pelanggan->alamat_detail) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat_detail')" class="mt-2" />
                    </div>

                    <div>
                        <label for="alamat_provinsi" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Provinsi</label>
                        <input type="text" id="alamat_provinsi" name="alamat_provinsi" value="{{ old('alamat_provinsi', $pelanggan->alamat_provinsi) }}" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                    </div>

                    <div>
                        <label for="alamat_kota" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Kota / Kabupaten</label>
                        <input type="text" id="alamat_kota" name="alamat_kota" value="{{ old('alamat_kota', $pelanggan->alamat_kota) }}" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                    </div>

                    <div>
                        <label for="alamat_kecamatan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Kecamatan</label>
                        <input type="text" id="alamat_kecamatan" name="alamat_kecamatan" value="{{ old('alamat_kecamatan', $pelanggan->alamat_kecamatan) }}" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                    </div>

                    <div>
                        <label for="alamat_kode_pos" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Kode Pos</label>
                        <input type="text" id="alamat_kode_pos" name="alamat_kode_pos" value="{{ old('alamat_kode_pos', $pelanggan->alamat_kode_pos) }}" class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                    </div>

                    <div class="md:col-span-2 flex flex-col gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 text-xs font-black uppercase tracking-widest text-slate-500 transition hover:text-slate-900">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-gradient-to-r from-red-700 to-red-800 px-8 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/30 transition hover:from-red-800 hover:to-red-900">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </section>

            <section class="rounded-[2.5rem] border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md" x-data="{
                mapPicker: null,
                marker: null,
                initMap() {
                    if (this.mapPicker) {
                        this.mapPicker.invalidateSize();
                        return;
                    }

                    const defaultLat = -6.595038;
                    const defaultLng = 106.816635;
                    const latInput = document.getElementById('input_lat');
                    const lngInput = document.getElementById('input_lng');
                    const lat = parseFloat(latInput.value) || defaultLat;
                    const lng = parseFloat(lngInput.value) || defaultLng;

                    this.mapPicker = L.map($refs.map).setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19,
                    }).addTo(this.mapPicker);

                    const markerIcon = L.divIcon({
                        html: `<div class='w-10 h-10 rounded-full border-4 border-white bg-red-600 shadow-lg flex items-center justify-center relative'><div class='w-3 h-3 rounded-full bg-white'></div><div class='absolute -bottom-2 w-0 h-0 border-l-[8px] border-r-[8px] border-t-[10px] border-l-transparent border-r-transparent border-t-red-600'></div></div>`,
                        iconAnchor: [20, 20],
                        className: '',
                    });

                    this.marker = L.marker([lat, lng], {
                        icon: markerIcon,
                        draggable: true,
                    }).addTo(this.mapPicker);

                    if (!latInput.value) {
                        latInput.value = lat.toFixed(6);
                        lngInput.value = lng.toFixed(6);
                    }

                    this.marker.on('dragend', () => {
                        const pos = this.marker.getLatLng();
                        latInput.value = pos.lat.toFixed(6);
                        lngInput.value = pos.lng.toFixed(6);
                    });

                    this.mapPicker.on('click', (event) => {
                        this.marker.setLatLng(event.latlng);
                        latInput.value = event.latlng.lat.toFixed(6);
                        lngInput.value = event.latlng.lng.toFixed(6);
                    });
                },
            }" x-init="setTimeout(() => initMap(), 200)">
                <div class="mb-5">
                    <h3 class="text-lg font-black text-slate-900">Pin Lokasi Pelanggan</h3>
                    <p class="mt-1 text-sm font-medium text-slate-500">Geser pin atau klik peta untuk memperbarui titik koordinat pelanggan.</p>
                </div>

                <div x-ref="map" class="min-h-[420px] w-full rounded-[2rem] border-2 border-gray-200 shadow-inner" style="z-index: 10;"></div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="input_lat" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Latitude</label>
                        <input type="text" id="input_lat" name="alamat_lat" value="{{ old('alamat_lat', $pelanggan->alamat_lat) }}" readonly class="w-full rounded-2xl border border-gray-200 bg-slate-100 px-4 py-3 text-xs font-black text-slate-500" />
                        <x-input-error :messages="$errors->get('alamat_lat')" class="mt-2" />
                    </div>

                    <div>
                        <label for="input_lng" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Longitude</label>
                        <input type="text" id="input_lng" name="alamat_lng" value="{{ old('alamat_lng', $pelanggan->alamat_lng) }}" readonly class="w-full rounded-2xl border border-gray-200 bg-slate-100 px-4 py-3 text-xs font-black text-slate-500" />
                        <x-input-error :messages="$errors->get('alamat_lng')" class="mt-2" />
                    </div>
                </div>
            </section>
        </form>
    </div>

    <style>
        .leaflet-container {
            z-index: 10 !important;
        }
    </style>
</x-app-layout>
