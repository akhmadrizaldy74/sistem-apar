<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.pelanggan.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Tambah Pelanggan</h2>
                <p class="text-sm text-gray-500 font-medium">Daftarkan pelanggan baru</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl space-y-5">
        <form action="{{ route('admin.pelanggan.store') }}" method="POST" id="create-pelanggan-form">
            @csrf

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mb-5">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-black text-gray-900">Informasi Pelanggan</span>
                </div>

                <div class="px-8 py-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Lengkap / Instansi <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required
                            class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                            placeholder="Contoh: PT. Maju Jaya">
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" name="no_wa" value="{{ old('no_wa') }}" required
                            class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                            placeholder="08xxxxxxxx">
                        <x-input-error :messages="$errors->get('no_wa')" class="mt-2" />
                    </div>


                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mb-5">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-black text-gray-900">Alamat & Lokasi</span>
                </div>

                <div class="px-8 py-6 space-y-5">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Cari Alamat (OpenStreetMap) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" id="create-search" placeholder="Ketik minimal 3 huruf..."
                                class="w-full pl-14 pr-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-500/20 font-bold text-gray-900 transition">
                            <span id="create-search-loading" class="hidden absolute right-5 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <div id="create-suggestions" class="hidden absolute z-30 top-full mt-2 w-full bg-white border border-gray-200 rounded-2xl shadow-xl overflow-hidden"></div>
                        </div>
                        <p id="create-search-helper" class="text-[10px] font-semibold mt-2 text-amber-600">Ketik minimal 3 huruf, lalu pilih saran alamat OpenStreetMap agar titik koordinat terkunci.</p>
                        <x-input-error :messages="$errors->get('alamat_maps')" class="mt-2" />
                        <x-input-error :messages="collect($errors->get('alamat_lat'))->merge($errors->get('alamat_lng'))->unique()->values()->all()" class="mt-2" />
                    </div>

                    <div>
                        <div id="create-map" class="w-full rounded-3xl border-2 border-gray-200 overflow-hidden shadow-sm" style="height:320px;"></div>
                        <div class="mt-3 flex items-center justify-between px-1">
                            <div class="flex gap-5 bg-gray-50 rounded-2xl px-5 py-3">
                                <div>
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Latitude</span>
                                    <span id="create-lat-display" class="text-sm font-mono font-black text-gray-800">-</span>
                                </div>
                                <div class="w-px bg-gray-200"></div>
                                <div>
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Longitude</span>
                                    <span id="create-lng-display" class="text-sm font-mono font-black text-gray-800">-</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-2 rounded-xl">
                                Klik peta atau geser pin merah
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1.5">Provinsi</label>
                            <input type="text" name="alamat_provinsi" id="create-provinsi" value="{{ old('alamat_provinsi') }}"
                                class="w-full px-4 py-3.5 bg-gray-50 border-none rounded-xl font-semibold text-sm text-gray-800">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1.5">Kota / Kab</label>
                            <input type="text" name="alamat_kota" id="create-kota" value="{{ old('alamat_kota') }}"
                                class="w-full px-4 py-3.5 bg-gray-50 border-none rounded-xl font-semibold text-sm text-gray-800">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1.5">Kecamatan</label>
                            <input type="text" name="alamat_kecamatan" id="create-kecamatan" value="{{ old('alamat_kecamatan') }}"
                                class="w-full px-4 py-3.5 bg-gray-50 border-none rounded-xl font-semibold text-sm text-gray-800">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1.5">Kode Pos</label>
                            <input type="text" name="alamat_kode_pos" id="create-kodepos" value="{{ old('alamat_kode_pos') }}"
                                class="w-full px-4 py-3.5 bg-gray-50 border-none rounded-xl font-semibold text-sm text-gray-800">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1.5">Alamat dari OpenStreetMap <span class="text-red-500">*</span></label>
                        <input type="text" name="alamat_maps" id="create-alamat-maps" value="{{ old('alamat_maps') }}" required readonly
                            class="w-full px-5 py-4 bg-gray-100 border-none rounded-2xl font-bold text-gray-900 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1.5">Detail Alamat / Patokan <span class="text-red-500">*</span></label>
                        <textarea name="alamat_detail" id="create-detail-alamat" rows="2" required
                            class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-semibold text-gray-800 resize-none"
                            placeholder="Contoh: Blok A2 No.10, dekat minimarket">{{ old('alamat_detail') }}</textarea>
                        <x-input-error :messages="$errors->get('alamat_detail')" class="mt-2" />
                    </div>

                    <input type="hidden" name="sumber_data" value="manual">
                    <input type="hidden" name="alamat_lat" id="create-lat" value="{{ old('alamat_lat') }}">
                    <input type="hidden" name="alamat_lng" id="create-lng" value="{{ old('alamat_lng') }}">
                    <input type="hidden" name="alamat" id="create-alamat-combined" value="{{ old('alamat') }}">
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.pelanggan.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

@push('scripts')
<script>
(function() {
    const ADDRESS_SUGGEST_URL = '{{ route('order.address.suggest') }}';

    const form = document.getElementById('create-pelanggan-form');
    const searchInput = document.getElementById('create-search');
    const loadingEl = document.getElementById('create-search-loading');
    const suggestionsEl = document.getElementById('create-suggestions');
    const helperEl = document.getElementById('create-search-helper');
    const latInput = document.getElementById('create-lat');
    const lngInput = document.getElementById('create-lng');
    const latDisplay = document.getElementById('create-lat-display');
    const lngDisplay = document.getElementById('create-lng-display');
    const alamatMaps = document.getElementById('create-alamat-maps');
    const provinsiEl = document.getElementById('create-provinsi');
    const kotaEl = document.getElementById('create-kota');
    const kecamatanEl = document.getElementById('create-kecamatan');
    const kodeposEl = document.getElementById('create-kodepos');
    const combinedEl = document.getElementById('create-alamat-combined');
    const detailEl = document.getElementById('create-detail-alamat');

    let map = null;
    let marker = null;
    let searchTimer = null;
    let suggestionItems = [];

    function updateCombined() {
        const maps = String(alamatMaps?.value || '').trim();
        const detail = String(detailEl?.value || '').trim();
        combinedEl.value = [maps, detail].filter(Boolean).join(' | Detail: ');
    }

    function updateCoord(lat, lng) {
        const latFixed = Number(lat).toFixed(8);
        const lngFixed = Number(lng).toFixed(8);
        latInput.value = latFixed;
        lngInput.value = lngFixed;
        latDisplay.textContent = latFixed;
        lngDisplay.textContent = lngFixed;
        updateCombined();
    }

    function placeMarker(lat, lng) {
        if (!map) return;
    const markerIcon = L.divIcon({
        className: 'custom-leaflet-marker',
        html: `
            <div style="position: relative; width: 34px; height: 34px;">
                <div style="position:absolute; inset:0; background:#ef4444; border-radius:9999px; border:4px solid #fff; box-shadow:0 10px 20px rgba(239,68,68,.28);"></div>
                <div style="position:absolute; left:11px; top:11px; width:6px; height:6px; border-radius:9999px; background:#fff;"></div>
                <div style="position:absolute; left:13px; bottom:-8px; width:0; height:0; border-left:4px solid transparent; border-right:4px solid transparent; border-top:10px solid #ef4444;"></div>
            </div>
        `,
        iconSize: [34, 42],
        iconAnchor: [17, 38],
    });
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {icon: markerIcon, draggable: true}).addTo(map);
            marker.on('drag', function(e) {
                updateCoord(e.latlng.lat, e.latlng.lng);
            });
            marker.on('dragend', function(e) {
                updateCoord(e.latlng.lat, e.latlng.lng);
            });
        }
    }

    function initMap() {
        setTimeout(() => {
            const oldLat = Number(latInput.value || 0);
            const oldLng = Number(lngInput.value || 0);
            const startLat = oldLat || -6.2088;
            const startLng = oldLng || 106.8456;
            const startZoom = oldLat && oldLng ? 17 : 13;

            map = L.map('create-map', {
                center: [startLat, startLng],
                zoom: startZoom,
                scrollWheelZoom: false,
                zoomControl: true,
            });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

            map.on('click', (e) => {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                placeMarker(lat, lng);
                updateCoord(lat, lng);
                helperEl.textContent = String(alamatMaps.value || '').trim()
                    ? 'Titik dipilih dari peta. Anda bisa geser pin untuk akurasi.'
                    : 'Pilih alamat OpenStreetMap terlebih dahulu, lalu koreksi titik dari peta bila perlu.';
                helperEl.className = String(alamatMaps.value || '').trim()
                    ? 'text-[10px] font-semibold mt-2 text-emerald-600'
                    : 'text-[10px] font-semibold mt-2 text-amber-600';
            });

            setTimeout(() => map.invalidateSize(), 300);

            if (oldLat && oldLng) {
                placeMarker(oldLat, oldLng);
                updateCoord(oldLat, oldLng);
            }
        });
    }

    function hideSuggestions() {
        suggestionsEl.classList.add('hidden');
        suggestionsEl.innerHTML = '';
        suggestionItems = [];
    }

    async function fetchSuggestions(q) {
        loadingEl.classList.remove('hidden');
        try {
            const res = await fetch(`${ADDRESS_SUGGEST_URL}?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            const items = (res.ok && data.success) ? (data.data || []) : [];

            if (!items.length) {
                hideSuggestions();
                helperEl.textContent = 'Alamat tidak ditemukan. Coba kata kunci lain atau klik langsung di peta.';
                helperEl.className = 'text-[10px] font-semibold mt-2 text-amber-600';
                return;
            }

            suggestionItems = items;
            suggestionsEl.innerHTML = '';

            items.forEach((item, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-5 py-3.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition border-b border-gray-50 last:border-0';
                btn.dataset.idx = String(idx);
                btn.textContent = item.display_name || '';
                suggestionsEl.appendChild(btn);
            });

            suggestionsEl.classList.remove('hidden');
            helperEl.textContent = 'Pilih salah satu untuk mengunci titik koordinat.';
            helperEl.className = 'text-[10px] font-semibold mt-2 text-blue-600';
        } catch (e) {
            hideSuggestions();
            helperEl.textContent = 'Gagal mengambil saran alamat. Coba lagi.';
            helperEl.className = 'text-[10px] font-semibold mt-2 text-red-600';
        } finally {
            loadingEl.classList.add('hidden');
        }
    }

    function selectItem(idx) {
        const item = suggestionItems[idx];
        if (!item) return;

        alamatMaps.value = item.display_name || '';
        searchInput.value = item.display_name || '';
        provinsiEl.value = item.provinsi || '';
        kotaEl.value = item.kota || '';
        kecamatanEl.value = item.kecamatan || '';
        kodeposEl.value = item.kode_pos || '';

        const lat = Number(item.lat || 0);
        const lng = Number(item.lng || item.lon || 0);
        if (lat && lng) {
            updateCoord(lat, lng);
            if (map) {
                map.setView([lat, lng], 17);
                placeMarker(lat, lng);
                setTimeout(() => map.invalidateSize(), 300);
            }
        }

        hideSuggestions();
        helperEl.textContent = 'Lokasi dipilih. Geser pin merah jika perlu koreksi.';
        helperEl.className = 'text-[10px] font-semibold mt-2 text-emerald-600';
    }

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim();
        if (searchTimer) clearTimeout(searchTimer);
        if (q.length < 3) {
            hideSuggestions();
            return;
        }
        searchTimer = setTimeout(() => fetchSuggestions(q), 400);
    });

    searchInput.addEventListener('blur', () => {
        setTimeout(hideSuggestions, 180);
    });

    suggestionsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-idx]');
        if (!btn) return;
        selectItem(Number(btn.dataset.idx));
    });

    detailEl.addEventListener('input', updateCombined);

    form.addEventListener('submit', (e) => {
        updateCombined();
        if (!String(alamatMaps.value || '').trim()) {
            e.preventDefault();
            helperEl.textContent = 'Silakan pilih alamat dari saran OpenStreetMap terlebih dahulu.';
            helperEl.className = 'text-[10px] font-semibold mt-2 text-red-600';
            return;
        }
        if (!String(latInput.value || '').trim() || !String(lngInput.value || '').trim()) {
            e.preventDefault();
            helperEl.textContent = 'Titik koordinat belum tersimpan. Pilih alamat dari saran OpenStreetMap.';
            helperEl.className = 'text-[10px] font-semibold mt-2 text-red-600';
        }
    });

    initMap();
    updateCombined();
})();
</script>
@endpush
