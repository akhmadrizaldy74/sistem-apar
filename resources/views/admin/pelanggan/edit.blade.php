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
                    <h2 class="text-3xl font-black tracking-tight text-gray-900">Edit Alamat Pelanggan</h2>
                    <p class="mt-2 text-sm font-medium text-gray-500">Perbarui alamat dan titik lokasi pelanggan.</p>
                </div>
            </div>
            <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-600 shadow-sm transition hover:bg-slate-50">
                Lihat Detail
            </a>
        </div>
    </x-slot>

    @php
        $alamatMapsValue = old('alamat_maps', $pelanggan->alamat_maps ?: $pelanggan->alamat);
        $alamatDetailValue = old('alamat_detail', $pelanggan->alamat_detail);
        $alamatLatValue = old('alamat_lat', $pelanggan->alamat_lat);
        $alamatLngValue = old('alamat_lng', $pelanggan->alamat_lng);
        $alamatProvinsiValue = old('alamat_provinsi', $pelanggan->alamat_provinsi);
        $alamatKotaValue = old('alamat_kota', $pelanggan->alamat_kota);
        $alamatKecamatanValue = old('alamat_kecamatan', $pelanggan->alamat_kecamatan);
        $alamatKodePosValue = old('alamat_kode_pos', $pelanggan->alamat_kode_pos);
        $rajaOngkirDestinationIdValue = old('rajaongkir_destination_id', $pelanggan->rajaongkir_destination_id);
        $rajaOngkirDestinationLabelValue = old('rajaongkir_destination_label', $pelanggan->rajaongkir_destination_label);
        $linkedUser = $pelanggan->user;
        $displayEmail = $linkedUser?->email ?: '-';
        $displayPhone = $linkedUser?->no_telpon ?: ($pelanggan->no_wa ?: '-');
    @endphp

    <div class="space-y-6">
        <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-xl shadow-gray-200/40 sm:p-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-slate-50/80 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Pelanggan</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $pelanggan->nama }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-slate-50/80 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Email</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $displayEmail }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-slate-50/80 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">WhatsApp / HP</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $displayPhone }}</p>
                </div>
            </div>
            <p class="mt-4 text-xs font-semibold text-slate-500">
                Data akun dikelola dari
                <a href="{{ route('admin.akun.index') }}" class="font-black text-red-700 hover:underline">Manajemen Akun</a>.
            </p>
        </section>

        <form id="admin-pelanggan-edit-form" action="{{ route('admin.pelanggan.update', $pelanggan) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-xl shadow-gray-200/40 sm:p-8">
                <div class="space-y-6">
                    <div>
                        <label for="admin-location-search" class="block text-sm font-bold text-gray-700">Cari Lokasi Pengiriman</label>
                        <input
                            id="admin-location-search"
                            type="text"
                            value="{{ $rajaOngkirDestinationLabelValue ?: $alamatMapsValue }}"
                            class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Contoh: Bandung, Jawa Barat"
                            autocomplete="off"
                        >
                        <div id="admin-location-suggestions" class="mt-2 hidden max-h-64 overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-xl"></div>
                        <p id="admin-location-helper" class="mt-2 text-xs font-medium text-gray-500">
                            Pilih lokasi pengiriman untuk mengisi tujuan pengiriman dan titik peta secara otomatis.
                        </p>
                        <x-input-error class="mt-2" :messages="collect($errors->get('alamat_maps'))->merge($errors->get('rajaongkir_destination_id'))->merge($errors->get('rajaongkir_destination_label'))->unique()->values()->all()" />
                    </div>

                    <div>
                        <label for="alamat_selected_display" class="block text-sm font-bold text-gray-700">Alamat Terpilih</label>
                        <textarea
                            id="alamat_selected_display"
                            rows="4"
                            readonly
                            class="mt-1 block w-full rounded-2xl border-gray-300 bg-slate-50 shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Lokasi pengiriman yang dipilih akan tampil di sini"
                        >{{ $alamatMapsValue }}</textarea>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 shadow-sm">
                        <div id="admin-address-map" class="w-full bg-gray-100" style="height: 340px;"></div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="alamat_lat" class="block text-sm font-bold text-gray-700">Latitude</label>
                            <input
                                id="alamat_lat"
                                name="alamat_lat"
                                type="text"
                                value="{{ $alamatLatValue }}"
                                inputmode="decimal"
                                class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="-6.20000000"
                            >
                            <x-input-error class="mt-2" :messages="$errors->get('alamat_lat')" />
                        </div>
                        <div>
                            <label for="alamat_lng" class="block text-sm font-bold text-gray-700">Longitude</label>
                            <input
                                id="alamat_lng"
                                name="alamat_lng"
                                type="text"
                                value="{{ $alamatLngValue }}"
                                inputmode="decimal"
                                class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="106.80000000"
                            >
                            <x-input-error class="mt-2" :messages="$errors->get('alamat_lng')" />
                        </div>
                    </div>

                    <div>
                        <label for="alamat_detail" class="block text-sm font-bold text-gray-700">Detail Alamat / Patokan</label>
                        <textarea
                            id="alamat_detail"
                            name="alamat_detail"
                            rows="3"
                            class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Contoh: dekat gerbang utama, lantai 2, blok A2"
                        >{{ $alamatDetailValue }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('alamat_detail')" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="alamat_provinsi" class="block text-sm font-bold text-gray-700">Provinsi</label>
                            <input type="text" id="alamat_provinsi" name="alamat_provinsi" value="{{ $alamatProvinsiValue }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                            <x-input-error class="mt-2" :messages="$errors->get('alamat_provinsi')" />
                        </div>
                        <div>
                            <label for="alamat_kota" class="block text-sm font-bold text-gray-700">Kota / Kabupaten</label>
                            <input type="text" id="alamat_kota" name="alamat_kota" value="{{ $alamatKotaValue }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                            <x-input-error class="mt-2" :messages="$errors->get('alamat_kota')" />
                        </div>
                        <div>
                            <label for="alamat_kecamatan" class="block text-sm font-bold text-gray-700">Kecamatan</label>
                            <input type="text" id="alamat_kecamatan" name="alamat_kecamatan" value="{{ $alamatKecamatanValue }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                            <x-input-error class="mt-2" :messages="$errors->get('alamat_kecamatan')" />
                        </div>
                        <div>
                            <label for="alamat_kode_pos" class="block text-sm font-bold text-gray-700">Kode Pos</label>
                            <input type="text" id="alamat_kode_pos" name="alamat_kode_pos" value="{{ $alamatKodePosValue }}" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                            <x-input-error class="mt-2" :messages="$errors->get('alamat_kode_pos')" />
                        </div>
                    </div>

                    <input type="hidden" id="alamat_maps" name="alamat_maps" value="{{ $alamatMapsValue }}">
                    <input type="hidden" id="rajaongkir_destination_id" name="rajaongkir_destination_id" value="{{ $rajaOngkirDestinationIdValue }}">
                    <input type="hidden" id="rajaongkir_destination_label" name="rajaongkir_destination_label" value="{{ $rajaOngkirDestinationLabelValue }}">
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 text-xs font-black uppercase tracking-widest text-slate-500 transition hover:text-slate-900">
                    Batal
                </a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-red-700 px-8 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                const addressSuggestUrl = @js(route('order.address.suggest'));
                const destinationSuggestUrl = @js(route('rajaongkir.destination'));
                const defaultLat = -6.2088;
                const defaultLng = 106.8456;

                const form = document.getElementById('admin-pelanggan-edit-form');
                const locationSearchInput = document.getElementById('admin-location-search');
                const addressInput = document.getElementById('alamat_maps');
                const selectedAddressDisplay = document.getElementById('alamat_selected_display');
                const latInput = document.getElementById('alamat_lat');
                const lngInput = document.getElementById('alamat_lng');
                const helper = document.getElementById('admin-location-helper');
                const suggestions = document.getElementById('admin-location-suggestions');
                const provinsiInput = document.getElementById('alamat_provinsi');
                const kotaInput = document.getElementById('alamat_kota');
                const kecamatanInput = document.getElementById('alamat_kecamatan');
                const kodePosInput = document.getElementById('alamat_kode_pos');
                const destinationIdInput = document.getElementById('rajaongkir_destination_id');
                const destinationLabelInput = document.getElementById('rajaongkir_destination_label');

                if (!form || !locationSearchInput || !addressInput || !latInput || !lngInput || !suggestions) {
                    return;
                }

                if (typeof L === 'undefined') {
                    helper.textContent = 'Peta gagal dimuat. Silakan refresh halaman dan coba lagi.';
                    helper.className = 'mt-2 text-xs font-medium text-red-600';
                    return;
                }

                let map;
                let marker;
                let debounceTimer;
                let suggestionItems = [];

                function updateHelper(text, tone) {
                    helper.textContent = text;
                    helper.className = 'mt-2 text-xs font-medium ';

                    if (tone === 'error') {
                        helper.className += 'text-red-600';
                        return;
                    }

                    if (tone === 'success') {
                        helper.className += 'text-emerald-600';
                        return;
                    }

                    if (tone === 'info') {
                        helper.className += 'text-blue-600';
                        return;
                    }

                    helper.className += 'text-gray-500';
                }

                function updateSelectedAddress(value) {
                    if (selectedAddressDisplay) {
                        selectedAddressDisplay.value = value || '';
                    }
                }

                function hideSuggestions() {
                    suggestions.classList.add('hidden');
                    suggestions.innerHTML = '';
                    suggestionItems = [];
                }

                function clearStructuredAddress() {
                    provinsiInput.value = '';
                    kotaInput.value = '';
                    kecamatanInput.value = '';
                    kodePosInput.value = '';
                }

                function clearMarker() {
                    if (!marker) {
                        return;
                    }

                    map.removeLayer(marker);
                    marker = null;
                }

                function clearCoordinates() {
                    latInput.value = '';
                    lngInput.value = '';
                    clearMarker();
                }

                function clearSelectedLocation(preserveSearch = false) {
                    addressInput.value = '';
                    if (destinationIdInput) destinationIdInput.value = '';
                    if (destinationLabelInput) destinationLabelInput.value = '';
                    clearStructuredAddress();
                    clearCoordinates();
                    updateSelectedAddress('');

                    if (!preserveSearch) {
                        locationSearchInput.value = '';
                    }
                }

                function setCoordinates(lat, lng) {
                    const safeLat = Number(lat);
                    const safeLng = Number(lng);

                    if (!Number.isFinite(safeLat) || !Number.isFinite(safeLng)) {
                        clearCoordinates();
                        return;
                    }

                    latInput.value = safeLat.toFixed(8);
                    lngInput.value = safeLng.toFixed(8);
                }

                function setMarker(lat, lng) {
                    const markerIcon = L.divIcon({
                        className: 'admin-profile-map-marker',
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
                        return;
                    }

                    marker = L.marker([lat, lng], { draggable: true, icon: markerIcon }).addTo(map);
                    marker.on('dragend', function (event) {
                        const position = event.target.getLatLng();
                        setCoordinates(position.lat, position.lng);
                        updateHelper('Titik lokasi diperbarui dari peta.', 'success');
                    });
                }

                function renderSuggestions(items) {
                    if (!Array.isArray(items) || !items.length) {
                        hideSuggestions();
                        updateHelper('Lokasi pengiriman belum ditemukan. Coba kata kunci yang lebih spesifik.', 'error');
                        return;
                    }

                    suggestionItems = items;
                    suggestions.innerHTML = '';

                    items.forEach((item, index) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.dataset.index = String(index);
                        button.className = 'block w-full border-b border-gray-100 px-4 py-3 text-left text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 last:border-b-0';

                        const title = document.createElement('span');
                        title.className = 'block font-semibold';
                        title.textContent = item.label || '-';

                        const subtitle = document.createElement('span');
                        subtitle.className = 'mt-1 block text-xs font-medium text-gray-400';
                        subtitle.textContent = [item.subdistrict_name, item.district_name, item.city_name, item.province_name, item.zip_code]
                            .filter(Boolean)
                            .join(' • ');

                        button.appendChild(title);
                        button.appendChild(subtitle);
                        suggestions.appendChild(button);
                    });

                    suggestions.classList.remove('hidden');
                    updateHelper('Pilih salah satu lokasi pengiriman untuk melengkapi alamat pelanggan.', 'info');
                }

                async function fetchSuggestions(query) {
                    try {
                        const response = await fetch(`${destinationSuggestUrl}?search=${encodeURIComponent(query)}`, {
                            headers: { Accept: 'application/json' },
                            credentials: 'same-origin',
                        });
                        const payload = await response.json();
                        const items = response.ok && payload.success ? (payload.data || []) : [];

                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Gagal mencari lokasi pengiriman.');
                        }

                        renderSuggestions(items);
                    } catch (error) {
                        hideSuggestions();
                        updateHelper(error.message || 'Gagal mencari lokasi pengiriman.', 'error');
                    }
                }

                function scheduleSuggestionFetch() {
                    const query = String(locationSearchInput.value || '').trim();

                    if (debounceTimer) {
                        window.clearTimeout(debounceTimer);
                    }

                    clearSelectedLocation(true);

                    if (query.length < 3) {
                        hideSuggestions();
                        updateHelper('Ketik minimal 3 huruf untuk mencari lokasi pengiriman.', 'default');
                        return;
                    }

                    debounceTimer = window.setTimeout(() => fetchSuggestions(query), 350);
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
                    const response = await fetch(`${addressSuggestUrl}?q=${encodeURIComponent(query)}`, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                    });
                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        return null;
                    }

                    const firstResult = Array.isArray(payload.data) ? payload.data[0] : null;
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

                async function syncMapToLocation(item) {
                    const queries = buildGeocodingQueries(item);

                    for (const query of queries) {
                        const coordinate = await fetchMapCoordinate(query);
                        if (!coordinate) {
                            continue;
                        }

                        setCoordinates(coordinate.lat, coordinate.lng);
                        setMarker(coordinate.lat, coordinate.lng);
                        map.setView([coordinate.lat, coordinate.lng], 17);
                        updateHelper('Lokasi pengiriman dipilih dan titik peta sudah ditampilkan.', 'success');
                        return;
                    }

                    clearCoordinates();
                    updateHelper('Lokasi pengiriman tersimpan. Titik peta dapat disesuaikan manual.', 'info');
                }

                async function chooseSuggestion(index) {
                    const item = suggestionItems[index];
                    if (!item) {
                        return;
                    }

                    const selectedLabel = String(item.label || '').trim();

                    locationSearchInput.value = selectedLabel;
                    addressInput.value = selectedLabel;
                    updateSelectedAddress(selectedLabel);
                    provinsiInput.value = item.province_name || '';
                    kotaInput.value = item.city_name || '';
                    kecamatanInput.value = item.subdistrict_name || item.district_name || '';
                    kodePosInput.value = item.zip_code || '';
                    if (destinationIdInput) destinationIdInput.value = item.id || '';
                    if (destinationLabelInput) destinationLabelInput.value = selectedLabel;

                    hideSuggestions();
                    updateHelper('Lokasi pengiriman dipilih. Peta sedang menyesuaikan posisi.', 'info');
                    await syncMapToLocation(item);
                }

                function syncMarkerFromInputs() {
                    const lat = Number(latInput.value);
                    const lng = Number(lngInput.value);
                    const hasLat = String(latInput.value || '').trim() !== '';
                    const hasLng = String(lngInput.value || '').trim() !== '';

                    if (!hasLat && !hasLng) {
                        clearMarker();
                        return;
                    }

                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                        return;
                    }

                    setMarker(lat, lng);
                    map.setView([lat, lng], 17);
                }

                const startLat = Number(latInput.value || defaultLat);
                const startLng = Number(lngInput.value || defaultLng);
                const hasSavedCoordinates = String(latInput.value || '').trim() !== '' && String(lngInput.value || '').trim() !== '';

                map = L.map('admin-address-map', {
                    scrollWheelZoom: false,
                    zoomControl: true,
                }).setView([startLat, startLng], hasSavedCoordinates ? 17 : 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);

                if (hasSavedCoordinates) {
                    setMarker(startLat, startLng);
                }

                updateSelectedAddress(addressInput.value || '');
                window.setTimeout(() => map.invalidateSize(), 250);
                window.setTimeout(() => map.invalidateSize(), 700);

                map.on('click', function (event) {
                    if (!String(addressInput.value || '').trim()) {
                        updateHelper('Pilih lokasi pengiriman terlebih dahulu, lalu sesuaikan titiknya di peta.', 'error');
                        return;
                    }

                    setMarker(event.latlng.lat, event.latlng.lng);
                    setCoordinates(event.latlng.lat, event.latlng.lng);
                    updateHelper('Titik lokasi diperbarui dari peta.', 'success');
                });

                locationSearchInput.addEventListener('input', scheduleSuggestionFetch);

                locationSearchInput.addEventListener('focus', function () {
                    const query = String(locationSearchInput.value || '').trim();
                    if (query.length >= 3) {
                        fetchSuggestions(query);
                    }
                });

                locationSearchInput.addEventListener('blur', function () {
                    window.setTimeout(hideSuggestions, 180);
                });

                latInput.addEventListener('input', syncMarkerFromInputs);
                lngInput.addEventListener('input', syncMarkerFromInputs);

                suggestions.addEventListener('click', function (event) {
                    const button = event.target.closest('button[data-index]');
                    if (!button) {
                        return;
                    }

                    void chooseSuggestion(Number(button.dataset.index));
                });

                form.addEventListener('submit', function (event) {
                    if (String(addressInput.value || '').trim() === '') {
                        event.preventDefault();
                        updateHelper('Lokasi pengiriman belum lengkap. Silakan pilih lokasi pengiriman terlebih dahulu.', 'error');
                    }
                });

                if (String(destinationLabelInput?.value || '').trim() !== '') {
                    updateHelper('Lokasi pengiriman sudah tersimpan untuk pelanggan ini.', 'success');
                }
            })();
        </script>
    @endpush
</x-app-layout>
