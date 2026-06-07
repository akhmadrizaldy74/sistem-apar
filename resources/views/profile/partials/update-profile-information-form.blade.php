<section>
    @php
        $alamatMapsValue = old('alamat_maps', $pelanggan?->alamat_maps);
        $alamatDetailValue = old('alamat_detail', $pelanggan?->alamat_detail);
        $alamatLatValue = old('alamat_lat', $pelanggan?->alamat_lat);
        $alamatLngValue = old('alamat_lng', $pelanggan?->alamat_lng);
        $alamatProvinsiValue = old('alamat_provinsi', $pelanggan?->alamat_provinsi);
        $alamatKotaValue = old('alamat_kota', $pelanggan?->alamat_kota);
        $alamatKecamatanValue = old('alamat_kecamatan', $pelanggan?->alamat_kecamatan);
        $alamatKodePosValue = old('alamat_kode_pos', $pelanggan?->alamat_kode_pos);
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Lengkapi data pelanggan dan alamat default agar saat checkout alamat sudah otomatis terisi.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-8" id="profile-form">
        @csrf
        @method('patch')

        <div class="grid gap-6 md:grid-cols-3">
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="no_telpon" :value="__('Nomor Telepon / WhatsApp')" />
                <x-text-input id="no_telpon" name="no_telpon" type="tel" class="mt-1 block w-full" :value="old('no_telpon', $user->no_telpon)" required autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('no_telpon')" />
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 p-5 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Alamat Pengiriman Default</h3>
                    <p class="text-sm text-gray-600">Simpan alamat lengkap dengan titik maps supaya pesanan berikutnya tidak perlu input ulang.</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                    Dipakai otomatis saat checkout
                </span>
            </div>

            <div class="mt-6 space-y-6">
                <div>
                    <x-input-label for="alamat_maps" :value="__('Cari Alamat di Peta')" />
                    <input
                        id="alamat_maps"
                        name="alamat_maps"
                        type="text"
                        value="{{ $alamatMapsValue }}"
                        class="mt-1 block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ketik minimal 3 huruf, lalu pilih saran alamat"
                        autocomplete="off"
                    >
                    <div id="profile-address-suggestions" class="mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"></div>
                    <p id="profile-address-helper" class="mt-2 text-xs font-medium text-gray-500">
                        Pilih saran alamat dari maps agar koordinat tersimpan dengan benar.
                    </p>
                    <x-input-error class="mt-2" :messages="$errors->get('alamat_maps')" />
                    <x-input-error class="mt-2" :messages="collect($errors->get('alamat_lat'))->merge($errors->get('alamat_lng'))->unique()->values()->all()" />
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-500">Konfirmasi Titik Lokasi</p>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-700">
                            Geser pin atau klik peta untuk koreksi
                        </span>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 shadow-sm">
                        <div id="profile-address-map" class="w-full bg-gray-100" style="height: 320px;"></div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Latitude</p>
                        <p id="profile-lat-display" class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ $alamatLatValue ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Longitude</p>
                        <p id="profile-lng-display" class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ $alamatLngValue ?: '-' }}</p>
                    </div>
                    <div>
                        <x-input-label for="alamat_provinsi" :value="__('Provinsi')" />
                        <x-text-input id="alamat_provinsi" name="alamat_provinsi" type="text" class="mt-1 block w-full" :value="$alamatProvinsiValue" />
                        <x-input-error class="mt-2" :messages="$errors->get('alamat_provinsi')" />
                    </div>
                    <div>
                        <x-input-label for="alamat_kota" :value="__('Kota / Kabupaten')" />
                        <x-text-input id="alamat_kota" name="alamat_kota" type="text" class="mt-1 block w-full" :value="$alamatKotaValue" />
                        <x-input-error class="mt-2" :messages="$errors->get('alamat_kota')" />
                    </div>
                    <div>
                        <x-input-label for="alamat_kecamatan" :value="__('Kecamatan')" />
                        <x-text-input id="alamat_kecamatan" name="alamat_kecamatan" type="text" class="mt-1 block w-full" :value="$alamatKecamatanValue" />
                        <x-input-error class="mt-2" :messages="$errors->get('alamat_kecamatan')" />
                    </div>
                    <div>
                        <x-input-label for="alamat_kode_pos" :value="__('Kode Pos')" />
                        <x-text-input id="alamat_kode_pos" name="alamat_kode_pos" type="text" class="mt-1 block w-full" :value="$alamatKodePosValue" />
                        <x-input-error class="mt-2" :messages="$errors->get('alamat_kode_pos')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="alamat_detail" :value="__('Detail Alamat / Patokan')" />
                    <textarea
                        id="alamat_detail"
                        name="alamat_detail"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Contoh: Blok A2 No.10, dekat gerbang utama, lantai 2"
                    >{{ $alamatDetailValue }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('alamat_detail')" />
                </div>

                <input id="alamat_lat" name="alamat_lat" type="hidden" value="{{ $alamatLatValue }}">
                <input id="alamat_lng" name="alamat_lng" type="hidden" value="{{ $alamatLngValue }}">
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

        </div>
    </form>
</section>

@push('scripts')
<script>
    (function () {
        const addressSuggestUrl = '{{ route('order.address.suggest') }}';
        const defaultLat = -6.2088;
        const defaultLng = 106.8456;

        const form = document.getElementById('profile-form');
        const addressInput = document.getElementById('alamat_maps');
        const detailInput = document.getElementById('alamat_detail');
        const latInput = document.getElementById('alamat_lat');
        const lngInput = document.getElementById('alamat_lng');
        const latDisplay = document.getElementById('profile-lat-display');
        const lngDisplay = document.getElementById('profile-lng-display');
        const helper = document.getElementById('profile-address-helper');
        const suggestions = document.getElementById('profile-address-suggestions');
        const provinsiInput = document.getElementById('alamat_provinsi');
        const kotaInput = document.getElementById('alamat_kota');
        const kecamatanInput = document.getElementById('alamat_kecamatan');
        const kodePosInput = document.getElementById('alamat_kode_pos');

        if (!form || !addressInput || !latInput || !lngInput || !suggestions) {
            return;
        }

        if (typeof L === 'undefined') {
            updateHelper('Peta gagal dimuat. Coba refresh halaman.', 'error');
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

        function hideSuggestions() {
            suggestions.classList.add('hidden');
            suggestions.innerHTML = '';
            suggestionItems = [];
        }

        function updateCoordinateDisplay(lat, lng) {
            latDisplay.textContent = lat ? Number(lat).toFixed(8) : '-';
            lngDisplay.textContent = lng ? Number(lng).toFixed(8) : '-';
        }

        function setCoordinates(lat, lng) {
            const safeLat = Number(lat);
            const safeLng = Number(lng);

            if (!Number.isFinite(safeLat) || !Number.isFinite(safeLng)) {
                latInput.value = '';
                lngInput.value = '';
                updateCoordinateDisplay(null, null);
                return;
            }

            latInput.value = safeLat.toFixed(8);
            lngInput.value = safeLng.toFixed(8);
            updateCoordinateDisplay(safeLat, safeLng);
        }

        function setMarker(lat, lng) {
            const markerIcon = L.divIcon({
                className: 'profile-map-marker',
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

        function clearStructuredAddress() {
            provinsiInput.value = '';
            kotaInput.value = '';
            kecamatanInput.value = '';
            kodePosInput.value = '';
        }

        async function fetchSuggestions(query) {
            try {
                const response = await fetch(`${addressSuggestUrl}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const payload = await response.json();
                const items = response.ok && payload.success ? (payload.data || []) : [];

                if (!items.length) {
                    hideSuggestions();
                    updateHelper('Alamat belum ditemukan. Coba kata kunci yang lebih spesifik.', 'error');
                    return;
                }

                suggestionItems = items;
                suggestions.innerHTML = '';

                items.forEach((item, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.index = String(index);
                    button.className = 'block w-full border-b border-gray-100 px-4 py-3 text-left text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 last:border-b-0';
                    button.textContent = item.display_name || '';
                    suggestions.appendChild(button);
                });

                suggestions.classList.remove('hidden');
                updateHelper('Pilih salah satu saran alamat untuk mengunci titik lokasi.', 'info');
            } catch (error) {
                hideSuggestions();
                updateHelper('Gagal mengambil saran alamat. Silakan coba lagi.', 'error');
            }
        }

        function scheduleSuggestionFetch() {
            const query = String(addressInput.value || '').trim();

            if (debounceTimer) {
                window.clearTimeout(debounceTimer);
            }

            setCoordinates(null, null);
            clearStructuredAddress();

            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }

            if (query.length < 3) {
                hideSuggestions();
                updateHelper('Ketik minimal 3 huruf, lalu pilih saran alamat dari peta.', 'default');
                return;
            }

            debounceTimer = window.setTimeout(() => fetchSuggestions(query), 350);
        }

        function chooseSuggestion(index) {
            const item = suggestionItems[index];
            if (!item) {
                return;
            }

            addressInput.value = item.display_name || '';
            provinsiInput.value = item.provinsi || '';
            kotaInput.value = item.kota || '';
            kecamatanInput.value = item.kecamatan || '';
            kodePosInput.value = item.kode_pos || '';

            const lat = Number(item.lat || 0);
            const lng = Number(item.lng || item.lon || 0);

            if (Number.isFinite(lat) && Number.isFinite(lng) && lat !== 0 && lng !== 0) {
                setCoordinates(lat, lng);
                setMarker(lat, lng);
                map.setView([lat, lng], 17);
            }

            hideSuggestions();
            updateHelper('Alamat berhasil dipilih. Anda bisa geser pin bila perlu koreksi titik.', 'success');
        }

        const startLat = Number(latInput.value || defaultLat);
        const startLng = Number(lngInput.value || defaultLng);
        const hasSavedCoordinates = String(latInput.value || '').trim() !== '' && String(lngInput.value || '').trim() !== '';

        map = L.map('profile-address-map', {
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

        updateCoordinateDisplay(latInput.value, lngInput.value);
        window.setTimeout(() => map.invalidateSize(), 250);
        window.setTimeout(() => map.invalidateSize(), 700);

        map.on('click', function (event) {
            if (!String(addressInput.value || '').trim()) {
                updateHelper('Pilih alamat dari saran peta terlebih dahulu, baru koreksi titiknya.', 'error');
                return;
            }

            setMarker(event.latlng.lat, event.latlng.lng);
            setCoordinates(event.latlng.lat, event.latlng.lng);
            updateHelper('Titik lokasi diperbarui dari peta.', 'success');
        });

        addressInput.addEventListener('input', scheduleSuggestionFetch);

        addressInput.addEventListener('focus', function () {
            const query = String(addressInput.value || '').trim();
            if (query.length >= 3) {
                fetchSuggestions(query);
            }
        });

        addressInput.addEventListener('blur', function () {
            window.setTimeout(hideSuggestions, 180);
        });

        detailInput?.addEventListener('input', function () {
            if (String(detailInput.value || '').trim() !== '') {
                updateHelper('Detail alamat akan dipakai otomatis saat checkout.', 'default');
            }
        });

        suggestions.addEventListener('click', function (event) {
            const button = event.target.closest('button[data-index]');
            if (!button) {
                return;
            }

            chooseSuggestion(Number(button.dataset.index));
        });

        form.addEventListener('submit', function (event) {
            const hasAddress = String(addressInput.value || '').trim() !== '';
            const hasCoordinates = String(latInput.value || '').trim() !== '' && String(lngInput.value || '').trim() !== '';

            if (hasAddress && !hasCoordinates) {
                event.preventDefault();
                updateHelper('Pilih alamat dari saran peta agar koordinat tersimpan.', 'error');
            }
        });
    })();
</script>
@endpush
