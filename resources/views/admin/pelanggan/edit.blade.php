<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.pelanggan.index') }}"
               class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Detail & Edit Pelanggan</h2>
                <p class="text-sm text-gray-500 font-medium">{{ $pelanggan->nama }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $waClean = preg_replace('/\D+/', '', (string) $pelanggan->no_wa);
        $waLink = preg_replace('/^0/', '62', $waClean);
        $orderCount = $pelanggan->pesanan->count();
        $isAktif = $orderCount > 0;
    @endphp

    <div class="max-w-6xl space-y-6" x-data="{ tab: 'profile' }">

        {{-- HEADER: Info Ringkas Pelanggan --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-8 py-6 flex items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-red-500/30 uppercase shrink-0">
                        {{ Str::limit($pelanggan->nama, 2, '') }}
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-white tracking-tight leading-tight">{{ $pelanggan->nama }}</h3>
                        @if($pelanggan->perusahaan)
                            <p class="text-sm text-white/50 font-medium mt-0.5">{{ $pelanggan->perusahaan }}</p>
                        @endif
                        @if($pelanggan->no_wa)
                            <div class="flex items-center gap-2 mt-1.5">
                                <svg class="w-3.5 h-3.5 text-white/40" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                <a href="https://wa.me/{{ $waLink }}" target="_blank" class="text-sm text-white/70 hover:text-white font-semibold transition">{{ $pelanggan->no_wa }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="shrink-0">
                    <span class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest {{ $isAktif ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-gray-500/20 text-gray-300 border border-gray-500/30' }}">
                        {{ $isAktif ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- TABS NAVIGATION --}}
        <div class="flex items-center gap-6 border-b border-gray-200 px-4">
            <button @click="tab = 'profile'"
                    :class="tab === 'profile' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-800'"
                    class="px-2 py-4 text-sm font-black tracking-widest uppercase border-b-2 transition">
                Profile & Address
            </button>
            <button @click="tab = 'history'"
                    :class="tab === 'history' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-800'"
                    class="px-2 py-4 text-sm font-black tracking-widest uppercase border-b-2 transition">
                Order History
            </button>
        </div>

        {{-- TAB CONTENT: PROFILE & ADDRESS (Form Kiri, Peta Kanan) --}}
        <div x-show="tab === 'profile'" x-transition.opacity.duration.300ms>
            <form action="{{ route('admin.pelanggan.update', $pelanggan) }}" method="POST" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    <div class="lg:col-span-5 space-y-5">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama', $pelanggan->nama) }}" required
                                   class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">WhatsApp / Telepon <span class="text-red-500">*</span></label>
                            <input type="text" name="no_wa" value="{{ old('no_wa', $pelanggan->no_wa) }}" required placeholder="08xxxxxxxx"
                                   class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Provinsi</label>
                                <input type="text" name="alamat_provinsi" value="{{ old('alamat_provinsi', $pelanggan->alamat_provinsi) }}"
                                       class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kota / Kabupaten</label>
                                <input type="text" name="alamat_kota" value="{{ old('alamat_kota', $pelanggan->alamat_kota) }}"
                                       class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kecamatan</label>
                                <input type="text" name="alamat_kecamatan" value="{{ old('alamat_kecamatan', $pelanggan->alamat_kecamatan) }}"
                                       class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kode Pos</label>
                                <input type="text" name="alamat_kode_pos" value="{{ old('alamat_kode_pos', $pelanggan->alamat_kode_pos) }}"
                                       class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alamat Pengiriman (Delivery Address)</label>
                            <textarea name="alamat_maps" rows="3" placeholder="Alamat lengkap tujuan pengiriman..."
                                      class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm resize-none">{{ old('alamat_maps', $pelanggan->alamat_maps ?: $pelanggan->alamat) }}</textarea>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Detail / Patokan</label>
                            <input type="text" name="alamat_detail" value="{{ old('alamat_detail', $pelanggan->alamat_detail) }}"
                                   class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 font-bold text-gray-900 transition text-sm">
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full sm:w-auto px-8 py-4 bg-gray-900 hover:bg-black text-white font-black rounded-xl transition shadow-lg text-xs uppercase tracking-widest">
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-7 flex flex-col"
                         x-data="{
                            mapPicker: null,
                            marker: null,
                            initMap: function() {
                                if(this.mapPicker) {
                                    this.mapPicker.invalidateSize();
                                    return;
                                }
                                const defaultLat = -6.595038;
                                const defaultLng = 106.816635;
                                const lat = parseFloat(document.getElementById('input_lat').value) || defaultLat;
                                const lng = parseFloat(document.getElementById('input_lng').value) || defaultLng;

                                this.mapPicker = L.map($refs.map).setView([lat, lng], 15);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap contributors',
                                    maxZoom: 19
                                }).addTo(this.mapPicker);

                                const icon = L.divIcon({
                                    html: `<div class='w-10 h-10 bg-red-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative'><div class='w-3 h-3 bg-white rounded-full'></div><div class='absolute -bottom-2 w-0 h-0 border-l-[8px] border-r-[8px] border-t-[10px] border-l-transparent border-r-transparent border-t-red-600'></div></div>`,
                                    iconAnchor: [20, 20],
                                    className: ''
                                });

                                this.marker = L.marker([lat, lng], {
                                    icon: icon,
                                    draggable: true
                                }).addTo(this.mapPicker);

                                if (!document.getElementById('input_lat').value) {
                                    document.getElementById('input_lat').value = lat.toFixed(6);
                                    document.getElementById('input_lng').value = lng.toFixed(6);
                                }

                                this.marker.on('dragend', () => {
                                    const pos = this.marker.getLatLng();
                                    document.getElementById('input_lat').value = pos.lat.toFixed(6);
                                    document.getElementById('input_lng').value = pos.lng.toFixed(6);
                                });

                                this.mapPicker.on('click', (e) => {
                                    this.marker.setLatLng(e.latlng);
                                    document.getElementById('input_lat').value = e.latlng.lat.toFixed(6);
                                    document.getElementById('input_lng').value = e.latlng.lng.toFixed(6);
                                });
                            }
                         }"
                         x-init="setTimeout(() => initMap(), 200); $watch('tab', value => { if(value === 'profile') setTimeout(() => initMap(), 200) })">

                        <div class="flex items-center justify-between mb-4">
                            <label class="text-[12px] font-black text-gray-800 uppercase tracking-widest">Pin Lokasi Peta</label>
                            <span class="text-xs font-semibold text-gray-400">Geser pin untuk set titik koordinat</span>
                        </div>

                        <div x-ref="map" style="z-index: 10;" class="w-full h-[400px] lg:h-full min-h-[400px] rounded-2xl border-2 border-gray-200 shadow-inner flex-grow"></div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Latitude</label>
                                <input type="text" id="input_lat" name="alamat_lat" value="{{ old('alamat_lat', $pelanggan->alamat_lat) }}" readonly
                                       class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl font-mono text-gray-500 transition text-xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Longitude</label>
                                <input type="text" id="input_lng" name="alamat_lng" value="{{ old('alamat_lng', $pelanggan->alamat_lng) }}" readonly
                                       class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl font-mono text-gray-500 transition text-xs">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- TAB CONTENT: ORDER HISTORY --}}
        <div x-show="tab === 'history'" x-transition.opacity.duration.300ms x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <span class="text-sm font-black text-gray-900">Riwayat Transaksi</span>
                @if($orderCount > 0)
                    <span class="ml-auto px-3 py-1 bg-purple-50 text-purple-700 text-[10px] font-black uppercase tracking-widest rounded-xl border border-purple-100">
                        {{ $orderCount }} Pesanan
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                @if($orderCount > 0)
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pengiriman</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pelanggan->pesanan as $p)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-8 py-4 text-sm font-semibold text-gray-500">
                                        {{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-8 py-4 text-sm font-black text-gray-800">
                                        Rp {{ number_format((float) ($p->total_harga ?: $p->total), 0, ',', '.') }}
                                    </td>
                                    <td class="px-8 py-4">
                                        @php
                                            $statusColors = [
                                                'diproses' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                                'selesai' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                                'selesai final' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                                'pending' => 'bg-gray-50 text-gray-500 border border-gray-100',
                                                'ditolak' => 'bg-red-50 text-red-700 border border-red-100',
                                                'menunggu persetujuan' => 'bg-blue-50 text-blue-700 border border-blue-100',
                                            ];
                                            $color = $statusColors[$p->status] ?? 'bg-blue-50 text-blue-700 border border-blue-100';
                                        @endphp
                                        <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $color }}">
                                            {{ $p->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-4 text-sm font-semibold text-gray-500">
                                        {{ ucfirst($p->metode_pengiriman ?: 'pickup') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-8 py-12 text-center">
                        <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-sm font-semibold text-gray-400">Belum ada riwayat transaksi.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <style>
        [x-cloak] { display: none !important; }
        .leaflet-container { z-index: 10 !important; }
    </style>
</x-app-layout>
