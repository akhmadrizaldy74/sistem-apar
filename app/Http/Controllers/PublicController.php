<?php

namespace App\Http\Controllers;

use App\Events\PesananBaru;
use App\Models\JenisRefill;
use App\Models\JenisApar;
use App\Models\Keranjang;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Complain;
use App\Models\Service;
use App\Models\ServicePaket;
use App\Models\StockMovement;
use App\Models\Testimoni;
use App\Models\UnitApar;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicController extends Controller
{
    private function authenticatedCustomerProfile(): ?array
    {
        if (!Auth::check()) {
            return null;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isAdmin() || $user->isTeknisi()) {
            return null;
        }

        $user->loadMissing('pelanggan');
        $pelanggan = $user->pelanggan;

        $profile = [
            'nama' => (string) ($pelanggan?->nama ?: $user->name ?: ''),
            'no_wa' => (string) ($pelanggan?->no_wa ?: $user->no_telpon ?: ''),
            'perusahaan' => (string) ($pelanggan?->perusahaan ?: ''),
            'alamat_maps' => (string) ($pelanggan?->alamat_maps ?: ''),
            'alamat_detail' => (string) ($pelanggan?->alamat_detail ?: ''),
            'alamat_provinsi' => (string) ($pelanggan?->alamat_provinsi ?: ''),
            'alamat_kota' => (string) ($pelanggan?->alamat_kota ?: ''),
            'alamat_kecamatan' => (string) ($pelanggan?->alamat_kecamatan ?: ''),
            'alamat_kode_pos' => (string) ($pelanggan?->alamat_kode_pos ?: ''),
            'alamat_lat' => $pelanggan?->alamat_lat,
            'alamat_lng' => $pelanggan?->alamat_lng,
        ];

        $profile['is_complete'] = filled($profile['nama'])
            && filled($profile['no_wa'])
            && filled($profile['alamat_maps'])
            && filled($profile['alamat_detail']);

        return $profile;
    }

    private function pendingPaymentOrderForAuthenticatedCustomer(): ?Pesanan
    {
        if (!Auth::check()) {
            return null;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isAdmin() || $user->isTeknisi()) {
            return null;
        }

        $user->loadMissing('pelanggan');
        if (!$user->pelanggan) {
            return null;
        }

        return Pesanan::where('pelanggan_id', $user->pelanggan->id)
            ->whereNotIn('status', [
                Pesanan::STATUS_SELESAI,
                Pesanan::STATUS_SELESAI_FINAL,
                Pesanan::STATUS_DITOLAK,
            ])
            ->latest()
            ->get()
            ->first(fn (Pesanan $pesanan) => !$pesanan->isPaymentConfirmed());
    }

    private function applyAuthenticatedCustomerProfileToRequest(Request $request): ?array
    {
        $profile = $this->authenticatedCustomerProfile();

        if (!$profile) {
            return null;
        }

        if (!$profile['is_complete']) {
            throw ValidationException::withMessages([
                'profile' => 'Lengkapi data alamat pelanggan di halaman profil terlebih dahulu sebelum membuat pesanan.',
            ]);
        }

        $request->merge([
            'nama' => $profile['nama'],
            'no_wa' => $profile['no_wa'],
            'alamat_maps' => $profile['alamat_maps'],
            'alamat_detail' => $profile['alamat_detail'],
            'alamat_provinsi' => $profile['alamat_provinsi'],
            'alamat_kota' => $profile['alamat_kota'],
            'alamat_kecamatan' => $profile['alamat_kecamatan'],
            'alamat_kode_pos' => $profile['alamat_kode_pos'],
            'alamat_lat' => $profile['alamat_lat'],
            'alamat_lng' => $profile['alamat_lng'],
            'perusahaan' => $profile['perusahaan'],
        ]);

        return $profile;
    }

    private function buildOrderCode(Pesanan $pesanan): string
    {
        return 'TNTI' . $pesanan->tanggal->format('dmY') . 'AJ' . str_pad((string) $pesanan->id, 3, '0', STR_PAD_LEFT);
    }

    private function bankAccounts(): array
    {
        return [
            'bca' => [
                'nama_bank' => 'Bank BCA',
                'no_rekening' => '1234567890',
                'pemilik' => 'PD. Anugrah Utama',
            ],
            'bri' => [
                'nama_bank' => 'Bank BRI',
                'no_rekening' => '0987654321',
                'pemilik' => 'PD. Anugrah Utama',
            ],
            'mandiri' => [
                'nama_bank' => 'Bank Mandiri',
                'no_rekening' => '8877665544',
                'pemilik' => 'PD. Anugrah Utama',
            ],
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }
        if (str_starts_with($digits, '8')) {
            return '0' . $digits;
        }

        return $digits;
    }

    private function normalizeCustomerName(string $name): string
    {
        $normalized = mb_strtolower(trim($name));
        $normalized = preg_replace('/[^[:alnum:]\s]/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function ensurePhoneOwnedBySameCustomer(string $phone, string $name): void
    {
        $pelanggan = Pelanggan::query()
            ->where('no_wa', $phone)
            ->first();

        if (!$pelanggan) {
            return;
        }

        $existingName = $this->normalizeCustomerName((string) $pelanggan->nama);
        $incomingName = $this->normalizeCustomerName($name);

        if ($existingName === '' || $incomingName === '' || $existingName === $incomingName) {
            return;
        }

        throw ValidationException::withMessages([
            'no_wa' => 'Nomor WhatsApp ini sudah terdaftar atas nama ' . $pelanggan->nama . '. Gunakan nomor lain agar data pelanggan tidak bentrok.',
        ]);
    }

    private function officeCoordinates(): array
    {
        return [
            'lat' => (float) env('STORE_LAT', -6.494778),
            'lng' => (float) env('STORE_LNG', 106.816635),
        ];
    }

    private function sanitizeCoordinate(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function extractAparCapacityKg(?string $value): ?float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (!preg_match('/(\d+(?:[.,]\d+)?)/', $raw, $matches)) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }

    private function serviceUkuranOptions(): array
    {
        return Produk::query()
            ->whereNotNull('kapasitas')
            ->pluck('kapasitas')
            ->map(fn ($kapasitas) => trim((string) $kapasitas))
            ->filter()
            ->unique(fn ($kapasitas) => mb_strtolower($kapasitas))
            ->sort(function (string $left, string $right) {
                $leftValue = $this->extractAparCapacityKg($left) ?? 9999;
                $rightValue = $this->extractAparCapacityKg($right) ?? 9999;

                if ($leftValue === $rightValue) {
                    return strcasecmp($left, $right);
                }

                return $leftValue <=> $rightValue;
            })
            ->values()
            ->all();
    }

    private function cleanAddressPart(?string $value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        return trim($text, ", \t\n\r\0\x0B");
    }

    private function canonicalAddressPart(string $value): string
    {
        $text = mb_strtolower($this->cleanAddressPart($value));
        $text = preg_replace('/[^a-z0-9\s]/iu', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        return trim($text);
    }

    private function firstAddressValue(array $address, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $this->cleanAddressPart((string) ($address[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function normalizeAdministrativeName(string $value): string
    {
        $text = $this->cleanAddressPart($value);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\s*\([^)]*\)\s*/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        return trim($text);
    }

    private function normalizeProvinceName(string $value): string
    {
        $text = $this->normalizeAdministrativeName($value);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/^provinsi\s+/iu', '', $text) ?? $text;
        return trim($text);
    }

    private function normalizeCityName(string $value): string
    {
        $text = $this->normalizeAdministrativeName($value);
        if ($text === '') {
            return '';
        }

        if (preg_match('/^kab(\.|upaten)?\s+/iu', $text)) {
            $text = preg_replace('/^kab(\.|upaten)?\s+/iu', '', $text) ?? $text;
            return 'Kabupaten ' . trim($text);
        }

        if (preg_match('/^kota\s+/iu', $text)) {
            $text = preg_replace('/^kota\s+/iu', '', $text) ?? $text;
            return 'Kota ' . trim($text);
        }

        return trim($text);
    }

    private function normalizeDistrictName(string $value): string
    {
        $text = $this->normalizeAdministrativeName($value);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/^(kec(\.|amatan)?|district|subdistrict)\s+/iu', '', $text) ?? $text;
        return trim($text);
    }

    private function normalizePostalCode(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (!empty($digits)) {
            return $digits;
        }

        return $this->cleanAddressPart($value);
    }

    private function isSameAdministrativeValue(string $left, string $right): bool
    {
        if ($left === '' || $right === '') {
            return false;
        }

        $normalize = function (string $value): string {
            $normalized = mb_strtolower($value);
            $normalized = preg_replace('/^(kota|kabupaten)\s+/iu', '', $normalized) ?? $normalized;
            $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
            return trim($normalized);
        };

        return $normalize($left) === $normalize($right);
    }

    private function shouldSkipAddressPart(string $part, array $selectedParts): bool
    {
        $candidate = $this->canonicalAddressPart($part);
        if ($candidate === '') {
            return true;
        }

        $genericRegionNames = [
            'jawa',
            'sumatera',
            'kalimantan',
            'sulawesi',
            'papua',
            'maluku',
            'bali',
            'nusa tenggara',
        ];

        if (in_array($candidate, $genericRegionNames, true)) {
            return true;
        }

        foreach ($selectedParts as $selected) {
            $existing = $this->canonicalAddressPart($selected);
            if ($existing === '') {
                continue;
            }

            if ($existing === $candidate) {
                return true;
            }

            if (mb_strlen($candidate) >= 4 && str_contains($existing, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function extractAdministrativeAddress(array $address): array
    {
        $provinsi = $this->normalizeProvinceName(
            $this->firstAddressValue($address, ['state', 'province', 'region'])
        );

        $kota = '';
        foreach (['city', 'regency', 'county', 'town', 'municipality'] as $key) {
            $candidate = $this->normalizeCityName($this->firstAddressValue($address, [$key]));
            if ($candidate === '') {
                continue;
            }

            if ($this->isSameAdministrativeValue($candidate, $provinsi)) {
                continue;
            }

            $kota = $candidate;
            break;
        }

        $kecamatan = '';
        foreach (['city_district', 'district', 'subdistrict', 'suburb', 'borough', 'quarter', 'village'] as $key) {
            $candidate = $this->normalizeDistrictName($this->firstAddressValue($address, [$key]));
            if ($candidate === '') {
                continue;
            }

            if (
                $this->isSameAdministrativeValue($candidate, $kota)
                || $this->isSameAdministrativeValue($candidate, $provinsi)
            ) {
                continue;
            }

            $kecamatan = $candidate;
            break;
        }

        $kodePos = $this->normalizePostalCode($this->firstAddressValue($address, ['postcode']));

        return [
            'provinsi' => $provinsi,
            'kota' => $kota,
            'kecamatan' => $kecamatan,
            'kode_pos' => $kodePos,
        ];
    }

    private function normalizedDisplayAddress(array $item, array $address): string
    {
        $admin = $this->extractAdministrativeAddress($address);
        $lokasiKecil = $this->normalizeAdministrativeName(
            $this->firstAddressValue($address, ['road', 'residential', 'neighbourhood', 'hamlet', 'village', 'suburb'])
        );
        $negara = $this->normalizeAdministrativeName(
            $this->firstAddressValue($address, ['country'])
        ) ?: 'Indonesia';

        $candidateParts = array_filter([
            $lokasiKecil,
            $admin['kecamatan'] ?? '',
            $admin['kota'] ?? '',
            $admin['provinsi'] ?? '',
            $admin['kode_pos'] ?? '',
            $negara,
        ], fn ($part) => $part !== '');

        $normalizedParts = [];
        foreach ($candidateParts as $part) {
            if ($this->shouldSkipAddressPart($part, $normalizedParts)) {
                continue;
            }
            $normalizedParts[] = $part;
        }

        $normalized = implode(', ', $normalizedParts);
        if ($normalized !== '') {
            return $normalized;
        }

        return (string) ($item['display_name'] ?? '');
    }

    private function calculateDistanceKm(float $latFrom, float $lngFrom, float $latTo, float $lngTo): float
    {
        $earthRadiusKm = 6371;

        $latFromRad = deg2rad($latFrom);
        $lngFromRad = deg2rad($lngFrom);
        $latToRad = deg2rad($latTo);
        $lngToRad = deg2rad($lngTo);

        $latDelta = $latToRad - $latFromRad;
        $lngDelta = $lngToRad - $lngFromRad;

        $a = sin($latDelta / 2) ** 2
            + cos($latFromRad) * cos($latToRad) * sin($lngDelta / 2) ** 2;
        $c = 2 * asin(min(1, sqrt($a)));

        return round($earthRadiusKm * $c, 2);
    }

    private function estimateOngkir(float $distanceKm, int $itemCount = 1): float
    {
        $ratePerKm = (float) env('SHIPPING_RATE_PER_KM', 5000);
        $minCost = (float) env('SHIPPING_MIN_COST', 10000);

        $raw = $distanceKm * $ratePerKm;
        return (float) max($minCost, round($raw, 0));
    }

    private function normalizeShippingMethod(string $method): string
    {
        $method = strtolower(trim($method));

        if ($method === 'diantar_internal') {
            return 'diantar';
        }

        return $method === 'diantar' ? 'diantar' : 'pickup';
    }

    private function shippingMethodForStorage(string $method): string
    {
        return $this->normalizeShippingMethod($method) === 'diantar'
            ? 'diantar_internal'
            : 'pickup';
    }

    private function shippingMethodLabel(?string $method): string
    {
        return $this->normalizeShippingMethod((string) $method) === 'diantar'
            ? 'DIANTAR'
            : 'PICKUP';
    }

    private function buildCombinedAddress(string $mapsAddress, string $detailAddress): string
    {
        $parts = array_filter([
            trim($mapsAddress),
            trim($detailAddress),
        ], fn ($value) => $value !== '');

        return implode(' | Detail: ', $parts);
    }

    private function syncServiceLogForPackageOrder(Pesanan $pesanan): void
    {
        if (!$pesanan->isPackageServiceOrder()) {
            return;
        }

        $pesanan->loadMissing(['servicePaket.peralatans', 'service']);
        $existingService = $pesanan->service;

        Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            $pesanan->serviceLogPayload([
                'status_konfirmasi' => $existingService?->status_konfirmasi ?: 'pending',
                'actual_peralatan_json' => $existingService?->actual_peralatan_json,
                'catatan_teknisi' => $existingService?->catatan_teknisi,
                'laporan_foto' => $existingService?->laporan_foto,
                'tgl_selesai_admin' => $existingService?->tgl_selesai_admin,
                'stok_kurang_history_json' => $existingService?->stok_kurang_history_json,
            ]),
        );
    }

    private function syncPelangganAddress(Pelanggan $pelanggan, Request $request, string $alamatGabungan): void
    {
        $lat = $this->sanitizeCoordinate($request->input('alamat_lat'));
        $lng = $this->sanitizeCoordinate($request->input('alamat_lng'));

        $update = [];

        // Hanya overwrite field kosong, JANGAN timpa data existing
        if (filled(trim((string) $request->input('alamat_maps', '')))) {
            $update['alamat_maps'] = trim((string) $request->input('alamat_maps'));
        }
        if (filled(trim((string) $request->input('alamat_detail', '')))) {
            $update['alamat_detail'] = trim((string) $request->input('alamat_detail'));
        }
        if (!is_null($lat)) {
            $update['alamat_lat'] = $lat;
        }
        if (!is_null($lng)) {
            $update['alamat_lng'] = $lng;
        }
        if (filled(trim((string) $request->input('alamat_provinsi', '')))) {
            $update['alamat_provinsi'] = trim((string) $request->input('alamat_provinsi'));
        }
        if (filled(trim((string) $request->input('alamat_kota', '')))) {
            $update['alamat_kota'] = trim((string) $request->input('alamat_kota'));
        }
        if (filled(trim((string) $request->input('alamat_kecamatan', '')))) {
            $update['alamat_kecamatan'] = trim((string) $request->input('alamat_kecamatan'));
        }
        if (filled(trim((string) $request->input('alamat_kode_pos', '')))) {
            $update['alamat_kode_pos'] = trim((string) $request->input('alamat_kode_pos'));
        }

        if (!empty($update)) {
            $update['alamat'] = $alamatGabungan;
            $pelanggan->update($update);
        }
    }

    private function syncPelangganAddressFromValidated(Pelanggan $pelanggan, array $validated, string $alamatGabungan): void
    {
        $lat = $this->sanitizeCoordinate($validated['alamat_lat'] ?? null);
        $lng = $this->sanitizeCoordinate($validated['alamat_lng'] ?? null);

        $update = [];

        if (filled(trim((string) ($validated['alamat_maps'] ?? '')))) {
            $update['alamat_maps'] = trim((string) ($validated['alamat_maps']));
        }
        if (filled(trim((string) ($validated['alamat_detail'] ?? '')))) {
            $update['alamat_detail'] = trim((string) ($validated['alamat_detail']));
        }
        if (!is_null($lat)) {
            $update['alamat_lat'] = $lat;
        }
        if (!is_null($lng)) {
            $update['alamat_lng'] = $lng;
        }

        if (!empty($update)) {
            $update['alamat'] = $alamatGabungan;
            $pelanggan->update($update);
        }
    }

    private function buildDeliveryMeta(Request $request, array $items): array
    {
        $method = $this->normalizeShippingMethod((string) $request->input('metode_pengiriman', 'pickup'));

        $mapsAddress = trim((string) $request->input('alamat_maps', ''));
        $detailAddress = trim((string) $request->input('alamat_detail', ''));
        $lat = $this->sanitizeCoordinate($request->input('alamat_lat'));
        $lng = $this->sanitizeCoordinate($request->input('alamat_lng'));

        $meta = [
            'metode_pengiriman' => $this->shippingMethodForStorage($method),
            'ongkir' => 0.0,
            'distance_km' => null,
            'alamat_maps' => $mapsAddress ?: null,
            'alamat_detail' => $detailAddress ?: null,
            'alamat_lat' => $lat,
            'alamat_lng' => $lng,
        ];

        if ($method === 'pickup') {
            return $meta;
        }

        if ($mapsAddress === '' || $detailAddress === '') {
            throw new \RuntimeException('Alamat pengiriman belum lengkap.');
        }

        if (is_null($lat) || is_null($lng) || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            throw new \RuntimeException('Koordinat alamat tidak valid. Pilih alamat dari OpenStreetMap.');
        }

        $office = $this->officeCoordinates();
        $distanceKm = $this->calculateDistanceKm($office['lat'], $office['lng'], $lat, $lng);
        $itemCount = array_sum(array_map(fn ($item) => (int) ($item['jumlah'] ?? 0), $items));

        $meta['distance_km'] = $distanceKm;
        $meta['ongkir'] = $this->estimateOngkir($distanceKm, max(1, $itemCount));

        return $meta;
    }

    private function normalizeNegoCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    private function isValidNegoCodeFormat(string $code): bool
    {
        return (bool) preg_match('/^ANUTA-\d{3}$/', $code);
    }

    private function buildRequestItemSignature(array $items): array
    {
        $signature = [];
        foreach ($items as $item) {
            $produkId = (int) ($item['produk_id'] ?? 0);
            $jumlah = (int) ($item['jumlah'] ?? 0);
            if ($produkId <= 0 || $jumlah <= 0) {
                continue;
            }
            $signature[$produkId] = ($signature[$produkId] ?? 0) + $jumlah;
        }

        ksort($signature);
        return $signature;
    }

    private function buildPesananItemSignature(Pesanan $pesanan): array
    {
        $signature = [];
        foreach ($pesanan->details as $detail) {
            $produkId = (int) $detail->produk_id;
            $jumlah = (int) $detail->jumlah;
            if ($produkId <= 0 || $jumlah <= 0) {
                continue;
            }
            $signature[$produkId] = ($signature[$produkId] ?? 0) + $jumlah;
        }

        ksort($signature);
        return $signature;
    }

    private function isNegoMatchedToCurrentOrder(Pesanan $sourceNego, string $noWa, array $requestItems): bool
    {
        $sourcePhone = $this->normalizePhone((string) ($sourceNego->pelanggan?->no_wa ?? ''));
        $currentPhone = $this->normalizePhone($noWa);
        if ($sourcePhone === '' || $currentPhone === '' || $sourcePhone !== $currentPhone) {
            return false;
        }

        $requestSignature = $this->buildRequestItemSignature($requestItems);
        $sourceSignature = $this->buildPesananItemSignature($sourceNego);

        return $requestSignature === $sourceSignature;
    }

    private function findValidNegoSource(string $kode): ?Pesanan
    {
        $kode = $this->normalizeNegoCode($kode);
        return Pesanan::query()
            ->with(['pelanggan', 'details.produk'])
            ->where('kode_nego', $kode)
            ->whereNotNull('harga_usulan')
            ->whereIn('status', ['pending', 'diproses', 'selesai'])
            ->whereNull('kode_nego_terpakai_at')
            ->whereNull('bukti_pembayaran')
            ->latest('tanggal')
            ->first();
    }

    private function getNormalOrderTotal(Pesanan $pesanan): float
    {
        $subtotalBarang = (float) $pesanan->details()->sum('subtotal');
        $ongkir = (float) ($pesanan->ongkir ?? 0);

        if (!is_null($pesanan->total) && (float) $pesanan->total > 0) {
            return (float) $pesanan->total;
        }

        return $subtotalBarang + $ongkir;
    }

    private function authenticatedCartItems(): Collection
    {
        if (!Auth::check()) {
            return collect();
        }

        return Keranjang::with('produk.jenisApar')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    private function buildCartOrderItems(Collection $cartItems): array
    {
        return $cartItems
            ->filter(fn ($item) => $item->produk)
            ->map(function ($item) {
                return [
                    'produk_id' => (int) $item->produk_id,
                    'jumlah' => (int) $item->qty,
                    'harga' => (float) $item->harga,
                    'nama' => (string) ($item->produk?->nama ?? 'Produk'),
                    'jenis' => (string) ($item->produk?->jenisApar?->nama ?? 'APAR'),
                    'kapasitas' => (string) ($item->produk?->kapasitas ?? '-'),
                    'merek' => (string) ($item->produk?->merek ?? 'SAFETY'),
                ];
            })
            ->values()
            ->all();
    }

    public function orderCreate()
    {
        if ($this->pendingPaymentOrderForAuthenticatedCustomer()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Selesaikan pembayaran sebelumnya sebelum membuat pesanan baru.');
        }

        $produks = Produk::whereHas('stokBatches', function ($q) {
                $q->where('sisa_qty', '>', 0)
                  ->where('tgl_expired', '>=', now()->toDateString());
            })
            ->with(['jenisApar', 'stokBatches'])
            ->get();

        $jenisApars = JenisApar::orderBy('nama')->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $servicePakets = ServicePaket::with(['peralatans', 'jenisRefill'])
            ->orderBy('harga')
            ->get()
            ->reject(fn (ServicePaket $servicePaket) => $servicePaket->isLegacyTemplate())
            ->values();
        $serviceUkuranOptions = $this->serviceUkuranOptions();
        $customerProfile = $this->authenticatedCustomerProfile();
        $useAuthenticatedCustomer = !is_null($customerProfile);
        $cartItems = collect();
        $cartTotal = 0;
        $cartItemCount = 0;
        $canUseCartCheckout = Auth::check() && $useAuthenticatedCustomer;

        if ($canUseCartCheckout) {
            $cartItems = $this->authenticatedCartItems();
            $cartTotal = $cartItems->sum(fn ($item) => $item->harga * $item->qty);
            $cartItemCount = (int) $cartItems->sum('qty');
        }

        return view('public.order.create', compact(
            'produks',
            'jenisApars',
            'jenisRefills',
            'servicePakets',
            'serviceUkuranOptions',
            'customerProfile',
            'useAuthenticatedCustomer',
            'cartItems',
            'cartTotal',
            'cartItemCount',
            'canUseCartCheckout',
        ));
    }

    public function orderStore(Request $request, InventoryService $inventoryService)
    {
        if ($this->pendingPaymentOrderForAuthenticatedCustomer()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Selesaikan pembayaran sebelumnya sebelum membuat pesanan baru.');
        }

        $this->applyAuthenticatedCustomerProfileToRequest($request);
        $isCartCheckout = $request->input('tipe_layanan') === 'beli'
            && $request->boolean('use_cart_checkout')
            && !is_null($this->authenticatedCustomerProfile());
        $cartItems = $isCartCheckout ? $this->authenticatedCartItems() : collect();
        $productItems = $isCartCheckout
            ? $this->buildCartOrderItems($cartItems)
            : (array) $request->input('items', []);
        $serviceUkuranOptions = $this->serviceUkuranOptions();

        $rules = [
            'nama'               => 'required|string|max:255',
            'no_wa'              => 'required|string|max:20',
            'alamat_maps'        => 'required|string|max:255',
            'alamat_detail'      => 'required|string|max:1000',
            'alamat_provinsi'    => 'nullable|string|max:255',
            'alamat_kota'        => 'nullable|string|max:255',
            'alamat_kecamatan'   => 'nullable|string|max:255',
            'alamat_kode_pos'    => 'nullable|string|max:50',
            'alamat_lat'         => 'nullable|numeric|between:-90,90',
            'alamat_lng'         => 'nullable|numeric|between:-180,180',
            'tipe_layanan'       => 'required|in:beli,service',
            'metode_pengiriman'  => 'nullable|in:pickup,diantar,diantar_internal',
            'bank'               => 'nullable|in:bca,mandiri,bri',
            'harga_usulan'       => 'nullable|numeric|min:0',
            'kode_nego'          => 'nullable|string|max:50',
            'is_nego_deal'       => 'nullable|boolean',
            'submit_source'      => 'nullable|in:normal,ask_wa',
            'service_jenis_layanan' => 'nullable|required_if:tipe_layanan,service|in:service,refill',
            'service_jenis_apar' => 'nullable|string|max:120',
            'service_jumlah_unit' => 'nullable|required_if:tipe_layanan,service|integer|min:1|max:1000',
            'service_keluhan' => 'nullable|string|max:2000',
            'service_foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'service_metode_penanganan' => 'nullable|required_if:tipe_layanan,service|in:dijemput,antar sendiri',
            'service_jenis_refill_id' => 'nullable|exists:jenis_refills,id',
            'service_paket_id' => 'nullable|exists:service_pakets,id',
            'service_ukuran_apar' => ['nullable', Rule::in($serviceUkuranOptions)],
        ];

        if ($isCartCheckout) {
            $rules['use_cart_checkout'] = 'required|accepted';
        } else {
            $rules['items'] = 'required_if:tipe_layanan,beli|array|min:1';
            $rules['items.*.produk_id'] = 'required_with:items|exists:produks,id';
            $rules['items.*.jumlah'] = 'required_with:items|integer|min:1';
        }

        $request->validate($rules);

        if ($isCartCheckout && empty($productItems)) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang Anda kosong. Tambahkan produk terlebih dahulu sebelum membuat pesanan.',
            ]);
        }

        $normalizedNoWa = $this->normalizePhone((string) $request->no_wa);
        $this->ensurePhoneOwnedBySameCustomer($normalizedNoWa, (string) $request->nama);
        $alamatGabungan = $this->buildCombinedAddress(
            (string) $request->input('alamat_maps', ''),
            (string) $request->input('alamat_detail', ''),
        );

        DB::beginTransaction();
        $successMessage = null;
        try {
            $pelanggan = Pelanggan::firstOrCreate(
                ['no_wa' => $normalizedNoWa],
                ['nama' => $request->nama, 'alamat' => $alamatGabungan, 'status' => 'calon']
            );
            if (empty($pelanggan->status)) {
                $pelanggan->update(['status' => 'calon']);
            }
            // Hanya perbarui nama/alamat jika pelanggan baru dibuat,
            // atau jika nama di DB masih kosong — JANGAN timpa data pelanggan lama!
            if ($pelanggan->wasRecentlyCreated || empty($pelanggan->nama)) {
                $pelanggan->nama   = $request->nama;
                $pelanggan->alamat = $alamatGabungan;
            }
            // Sync data alamat lengkap ke tabel pelanggan (isi apa yang available)
            $this->syncPelangganAddress($pelanggan, $request, $alamatGabungan);

            $pesanan = new Pesanan();
                $pesanan->pelanggan_id = $pelanggan->id;
                $pesanan->tanggal = now();

            $redirectToPayment = false;

            if ($request->tipe_layanan === 'beli') {
                $isNegoDeal = $request->boolean('is_nego_deal');
                $kodeNegoInput = $request->filled('kode_nego') ? $this->normalizeNegoCode((string) $request->kode_nego) : null;
                $hargaDeal = null;
                $sumberNego = null;

                if ($isNegoDeal) {
                    if (!$kodeNegoInput) {
                        throw new \RuntimeException('Kode negosiasi wajib diisi untuk harga deal.');
                    }
                    if (!$this->isValidNegoCodeFormat($kodeNegoInput)) {
                        throw new \RuntimeException('Format kode negosiasi tidak valid.');
                    }

                    $sumberNego = $this->findValidNegoSource($kodeNegoInput);

                    if (!$sumberNego) {
                        throw new \RuntimeException('Kode negosiasi tidak valid atau sudah digunakan.');
                    }

                    if (!$this->isNegoMatchedToCurrentOrder($sumberNego, $normalizedNoWa, $productItems)) {
                        throw new \RuntimeException('Kode tidak sesuai dengan pesanan Anda.');
                    }

                    $pesanan = $sumberNego;
                    $hargaDeal = (float) $pesanan->harga_usulan;
                } else {
                    // Use the newly instantiated pesanan
                }

                $deliveryMeta = $this->buildDeliveryMeta($request, $productItems);
                $pesanan->tipe = 'produk';
                $pesanan->sumber_pesanan = 'website';
                $pesanan->metode_pengiriman = $deliveryMeta['metode_pengiriman'];
                $pesanan->bank = (string) $request->input('bank', '');
                $pesanan->ongkir = (float) $deliveryMeta['ongkir'];
                $pesanan->shipping_distance_km = $deliveryMeta['distance_km'];
                $pesanan->alamat_maps = $deliveryMeta['alamat_maps'];
                $pesanan->alamat_detail = $deliveryMeta['alamat_detail'];
                $pesanan->alamat_lat = $deliveryMeta['alamat_lat'];
                $pesanan->alamat_lng = $deliveryMeta['alamat_lng'];
                $bankTujuan = strtoupper((string) $request->input('bank', '-'));

                if ($isNegoDeal) {
                    $pesanan->harga_usulan = $hargaDeal;
                    $pesanan->is_nego = true;
                    $pesanan->kode_nego = $kodeNegoInput;
                    $pesanan->kode_nego_terpakai_at = now();
                    $pesanan->status = 'pending';
                    $pesanan->tipe_harga = 'deal';
                    $pesanan->keterangan = "Pembelian Produk [Kode: {$kodeNegoInput}] [Sumber: Kode Nego Admin] [Pengiriman: " . $this->shippingMethodLabel((string) $pesanan->metode_pengiriman) . "] [Bank Tujuan: {$bankTujuan}]";
                } else {
                    $pesanan->total = 0;
                    $pesanan->status = 'pending';
                    $pesanan->tipe_harga = 'normal';
                    $pesanan->keterangan = "Pembelian Produk [Pengiriman: " . $this->shippingMethodLabel((string) $pesanan->metode_pengiriman) . "] [Bank Tujuan: {$bankTujuan}]";
                }

                $pesanan->save();

                if ($isNegoDeal && $sumberNego) {
                    $pesanan->details()->delete();
                }

                $totalHarga = 0;
                foreach ($productItems as $item) {
                    $produk = Produk::findOrFail($item['produk_id']);
                    $hargaSatuan = (float) ($item['harga'] ?? $produk->harga);
                    $stokTersedia = (int) $produk->stok_tersedia;

                    if ($stokTersedia < (int) $item['jumlah']) {
                        throw new \RuntimeException('Stok siap jual "' . $produk->nama . '" tidak mencukupi. Tersedia: ' . $stokTersedia);
                    }

                    $subtotal = $hargaSatuan * $item['jumlah'];
                    $totalHarga += $subtotal;

                    $pesanan->details()->create([
                        'produk_id' => $produk->id,
                        'merek' => $produk->merek ?? 'SAFETY',
                        'kapasitas' => $produk->kapasitas,
                        'jumlah' => $item['jumlah'],
                        'harga' => $hargaSatuan,
                        'subtotal' => $subtotal,
                    ]);
                }

                if ($isCartCheckout && Auth::check()) {
                    Keranjang::where('user_id', Auth::id())->delete();
                }

                $totalFinal = ($isNegoDeal && !is_null($hargaDeal))
                    ? $hargaDeal
                    : ($totalHarga + (float) ($pesanan->ongkir ?? 0));
                
                $pesanan->update([
                    'total' => $totalFinal,
                    'total_harga' => $totalFinal,
                ]);

                $redirectToPayment = true;

            } else {
                $serviceJenisLayanan = strtolower(trim((string) $request->input('service_jenis_layanan', 'service')));
                if (!in_array($serviceJenisLayanan, ['service', 'refill'], true)) {
                    $serviceJenisLayanan = 'service';
                }
                $serviceUkuranApar = trim((string) $request->input('service_ukuran_apar', ''));
                $serviceJumlahUnit = max(1, (int) $request->input('service_jumlah_unit', 1));
                $serviceKeluhan = trim((string) $request->input('service_keluhan', (string) $request->input('keterangan_service', '-')));
                $serviceMetode = strtolower(trim((string) $request->input('service_metode_penanganan', 'dijemput')));
                if (!in_array($serviceMetode, ['dijemput', 'antar sendiri'], true)) {
                    $serviceMetode = 'dijemput';
                }
                $serviceFotoPath = $request->hasFile('service_foto')
                    ? $request->file('service_foto')->store('service-request', 'public')
                    : null;
                $serviceUkuranKg = $this->extractAparCapacityKg($serviceUkuranApar);

                if ($serviceUkuranApar === '' || !in_array($serviceUkuranApar, $serviceUkuranOptions, true)) {
                    throw ValidationException::withMessages([
                        'service_ukuran_apar' => 'Ukuran APAR wajib dipilih dari daftar yang tersedia.',
                    ]);
                }

                $pesanan->tipe = 'service';
                $pesanan->sumber_pesanan = 'website';
                $pesanan->total = 0;
                $pesanan->total_harga = 0;
                $pesanan->tipe_harga = 'normal';
                $pesanan->metode_pengiriman = $serviceMetode === 'antar sendiri' ? 'pickup' : 'diantar_internal';
                $pesanan->bank = (string) $request->input('bank', '');
                $pesanan->ongkir = 0;
                $pesanan->alamat_maps = (string) $request->input('alamat_maps', '');
                $pesanan->alamat_detail = (string) $request->input('alamat_detail', '');
                $pesanan->alamat_lat = $this->sanitizeCoordinate($request->input('alamat_lat'));
                $pesanan->alamat_lng = $this->sanitizeCoordinate($request->input('alamat_lng'));
                $pesanan->service_jenis_layanan = $serviceJenisLayanan;
                $pesanan->service_jenis_apar = 'APAR ' . $serviceUkuranApar;
                $pesanan->service_ukuran_apar = $serviceUkuranApar;
                $pesanan->service_jumlah_unit = $serviceJumlahUnit;
                $pesanan->service_keluhan = $serviceKeluhan !== '' ? $serviceKeluhan : '-';
                $pesanan->service_foto = $serviceFotoPath;
                $pesanan->service_metode_penanganan = $serviceMetode;
                $pesanan->service_admin_catatan = null;
                $pesanan->status = Pesanan::STATUS_PENDING;

                if ($serviceJenisLayanan === 'refill') {
                    $jenisRefill = JenisRefill::find($request->input('service_jenis_refill_id'));

                    if (!$jenisRefill) {
                        throw ValidationException::withMessages([
                            'service_jenis_refill_id' => 'Jenis refill wajib dipilih.',
                        ]);
                    }

                    if (!$serviceUkuranKg || $serviceUkuranKg <= 0) {
                        throw ValidationException::withMessages([
                            'service_ukuran_apar' => 'Ukuran APAR belum bisa dihitung ke satuan Kg.',
                        ]);
                    }

                    $hargaSatuan = $jenisRefill->resolveServicePrice($serviceUkuranApar);
                    if (is_null($hargaSatuan) || $hargaSatuan <= 0) {
                        throw ValidationException::withMessages([
                            'service_ukuran_apar' => 'Harga refill standar untuk ukuran APAR ini belum tersedia.',
                        ]);
                    }

                    $totalKebutuhanKg = round($serviceUkuranKg * $serviceJumlahUnit, 2);
                    if ((float) $jenisRefill->stok < $totalKebutuhanKg) {
                        throw ValidationException::withMessages([
                            'service_jenis_refill_id' => 'Stok refill ' . $jenisRefill->nama_label . ' tidak mencukupi.',
                        ]);
                    }

                    $estimasiBiaya = $hargaSatuan * $serviceJumlahUnit;

                    $pesanan->service_jenis_refill_id = $jenisRefill->id;
                    $pesanan->service_paket_id = null;
                    $pesanan->service_total_kg = $totalKebutuhanKg;
                    $pesanan->service_estimasi_biaya = $estimasiBiaya;
                    $pesanan->total = $estimasiBiaya;
                    $pesanan->total_harga = $estimasiBiaya;
                    $pesanan->keterangan = "Permintaan REFILL {$jenisRefill->nama_label}"
                        . " | Ukuran: {$serviceUkuranApar}"
                        . " | Jumlah: {$serviceJumlahUnit} unit"
                        . " | Kebutuhan: {$totalKebutuhanKg} {$jenisRefill->satuan_label}"
                        . " | Metode: {$serviceMetode}"
                        . " | Catatan: " . ($pesanan->service_keluhan ?: '-');
                    $pesanan->save();
                    $successMessage = 'Pesanan refill berhasil dibuat dengan estimasi ' . number_format($estimasiBiaya, 0, ',', '.')
                        . '. Silakan lanjutkan pembayaran untuk mengaktifkan proses pengerjaan.';
                } else {
                    $paket = ServicePaket::with(['peralatans', 'jenisRefill'])->find($request->input('service_paket_id'));

                    if (!$paket) {
                        throw ValidationException::withMessages([
                            'service_paket_id' => 'Paket service wajib dipilih.',
                        ]);
                    }

                    $estimasiBiaya = $paket->harga * $serviceJumlahUnit;
                    $estimasiRefillKg = null;
                    if ($serviceUkuranKg && $serviceUkuranKg > 0 && $paket->refill_ratio && $paket->refill_ratio > 0) {
                        $estimasiRefillKg = round($serviceUkuranKg * $serviceJumlahUnit * $paket->refill_ratio, 2);
                    }

                    $pesanan->service_paket_id = $paket->id;
                    $pesanan->service_jenis_refill_id = $paket->jenis_refill_id;
                    $pesanan->service_total_kg = $estimasiRefillKg;
                    $pesanan->service_estimasi_biaya = $estimasiBiaya;
                    $pesanan->total = $estimasiBiaya;
                    $pesanan->total_harga = $estimasiBiaya;
                    $pesanan->keterangan = "Permintaan SERVICE {$paket->nama}"
                        . " | Ukuran: {$serviceUkuranApar}"
                        . " | Jumlah: {$serviceJumlahUnit} unit"
                        . " | Metode: {$serviceMetode}"
                        . " | Catatan: " . ($pesanan->service_keluhan ?: '-');
                    $pesanan->save();

                    $successMessage = 'Pesanan service berhasil dibuat dengan estimasi ' . number_format($estimasiBiaya, 0, ',', '.')
                        . '. Silakan lanjutkan pembayaran untuk mengaktifkan proses pengerjaan.';
                }

                $redirectToPayment = true;
            }

            DB::commit();

            // Broadcast ke admin real-time
            broadcast(new PesananBaru($pesanan))->toOthers();

            if ($redirectToPayment) {
                return redirect()->route('order.payment', $pesanan)->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');
            }

            return redirect()->route('order.create')->with('success', ($successMessage ?: 'Pesanan berhasil dikirim.') . ' ID: ' . $pesanan->id);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }

    public function orderAskWhatsapp(Request $request)
    {
        $this->applyAuthenticatedCustomerProfileToRequest($request);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_wa' => 'required|string|max:20',
            'alamat_maps' => 'required|string|max:255',
            'alamat_detail' => 'required|string|max:1000',
            'alamat_provinsi' => 'nullable|string|max:255',
            'alamat_kota' => 'nullable|string|max:255',
            'alamat_kecamatan' => 'nullable|string|max:255',
            'alamat_kode_pos' => 'nullable|string|max:50',
            'alamat_lat' => 'nullable|numeric|between:-90,90',
            'alamat_lng' => 'nullable|numeric|between:-180,180',
            'metode_pengiriman' => 'nullable|in:pickup,diantar',
            'bank' => 'required|in:bca,mandiri,bri',
            'perusahaan' => 'nullable|string|max:255',
            'harga_usulan' => 'nullable|numeric|min:0',
            'sumber_negosiasi' => 'nullable|in:sistem,whatsapp',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        $normalizedNoWa = $this->normalizePhone((string) $validated['no_wa']);
        $this->ensurePhoneOwnedBySameCustomer($normalizedNoWa, (string) $validated['nama']);
        $alamatGabungan = $this->buildCombinedAddress(
            (string) ($validated['alamat_maps'] ?? ''),
            (string) ($validated['alamat_detail'] ?? ''),
        );
        $totalQty = collect((array) ($validated['items'] ?? []))
            ->sum(fn ($item) => (int) ($item['jumlah'] ?? 0));

        if (!empty($validated['harga_usulan']) && $totalQty < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Harga usulan hanya bisa diajukan jika total pembelian minimal 10 unit.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $deliveryMeta = $this->buildDeliveryMeta($request, (array) ($validated['items'] ?? []));

            $pelanggan = Pelanggan::firstOrCreate(
                ['no_wa' => $normalizedNoWa],
                [
                    'nama' => $validated['nama'],
                    'alamat' => $alamatGabungan,
                    'status' => 'calon',
                ]
            );

            if (empty($pelanggan->status)) {
                $pelanggan->status = 'calon';
            }

            // Hanya perbarui nama/alamat jika pelanggan baru dibuat,
            // atau jika nama di DB masih kosong — JANGAN timpa data pelanggan lama!
            if ($pelanggan->wasRecentlyCreated || empty($pelanggan->nama)) {
                $pelanggan->nama   = $validated['nama'];
                $pelanggan->alamat = $alamatGabungan;
            }
            // Sync data alamat lengkap ke tabel pelanggan
            $this->syncPelangganAddressFromValidated($pelanggan, $validated, $alamatGabungan);

            // ─── Cegah duplikasi: cek apakah sudah ada pesanan menunggu persetujuan
            // dari pelanggan yang sama dalam 10 menit terakhir (anti double-submit)
            $existingPesanan = Pesanan::where('pelanggan_id', $pelanggan->id)
                ->where('tipe', 'produk')
                ->where('status', 'menunggu persetujuan')
                ->whereNull('kode_nego')
                ->where('created_at', '>=', now()->subMinutes(10))
                ->latest()
                ->first();

            if ($existingPesanan) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Inquiry negosiasi sudah ada dan sedang menunggu proses.',
                    'data' => [
                        'pesanan_id' => $existingPesanan->id,
                        'kode_pesanan' => $this->buildOrderCode($existingPesanan),
                    ],
                ]);
            }
            // ─────────────────────────────────────────────────────────────────

            $pesanan = new Pesanan();
            $pesanan->pelanggan_id = $pelanggan->id;
            $pesanan->tanggal = now();
            $pesanan->tipe = 'produk';
            $pesanan->sumber_pesanan = 'website';
            $pesanan->status = 'menunggu persetujuan';
            $pesanan->is_nego = true;
            $pesanan->harga_usulan = $validated['harga_usulan'] ?? null;
            $pesanan->harga_penawaran_pelanggan = $validated['harga_usulan'] ?? null;
            $pesanan->tipe_harga = 'normal';
            $pesanan->total = 0;
            $pesanan->total_harga = 0;
            $pesanan->metode_pengiriman = $deliveryMeta['metode_pengiriman'];
            $pesanan->bank = (string) $validated['bank'];
            $pesanan->ongkir = (float) $deliveryMeta['ongkir'];
            $pesanan->shipping_distance_km = $deliveryMeta['distance_km'];
            $pesanan->alamat_maps = $deliveryMeta['alamat_maps'];
            $pesanan->alamat_detail = $deliveryMeta['alamat_detail'];
            $pesanan->alamat_lat = $deliveryMeta['alamat_lat'];
            $pesanan->alamat_lng = $deliveryMeta['alamat_lng'];
            $sumberNegosiasi = $validated['sumber_negosiasi'] ?? 'whatsapp';
            $labelSumberNegosiasi = $sumberNegosiasi === 'sistem' ? 'Sistem Pelanggan' : 'WhatsApp';
                $pesanan->keterangan = 'Inquiry Negosiasi via ' . $labelSumberNegosiasi
                . ($pesanan->harga_usulan ? ' [Harga Usulan: Rp ' . number_format($pesanan->harga_usulan, 0, ',', '.') . ']' : '')
                . ' [Pengiriman: ' . $this->shippingMethodLabel((string) $deliveryMeta['metode_pengiriman']) . ']'
                . ' [Bank Tujuan: ' . strtoupper((string) $validated['bank']) . ']'
                . (!empty($validated['perusahaan']) ? ' [Perusahaan: ' . $validated['perusahaan'] . ']' : '');
            $pesanan->save();

            $total = 0;
            foreach ((array) $validated['items'] as $item) {
                $produk = Produk::findOrFail((int) $item['produk_id']);
                $jumlah = (int) $item['jumlah'];
                $stokTersedia = (int) $produk->stok_tersedia;

                if ($stokTersedia < $jumlah) {
                    throw new \RuntimeException('Stok siap jual "' . $produk->nama . '" tidak mencukupi. Tersedia: ' . $stokTersedia);
                }

                $subtotal = ((float) $produk->harga) * $jumlah;

                $pesanan->details()->create([
                    'produk_id' => $produk->id,
                    'merek' => $produk->merek ?? 'SAFETY',
                    'kapasitas' => $produk->kapasitas,
                    'jumlah' => $jumlah,
                    'harga' => $produk->harga,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $pesanan->update([
                'total' => $total + (float) $pesanan->ongkir,
                'total_harga' => $total + (float) $pesanan->ongkir,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data negosiasi berhasil disimpan.',
                'data' => [
                    'pesanan_id' => $pesanan->id,
                    'kode_pesanan' => $this->buildOrderCode($pesanan),
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data negosiasi. ' . $th->getMessage(),
            ], 500);
        }
    }

    public function orderShippingQuote(Request $request)
    {
        $validated = $request->validate([
            'metode_pengiriman' => 'required|in:diantar',
            'alamat_maps' => 'required|string|max:255',
            'alamat_detail' => 'required|string|max:1000',
            'alamat_lat' => 'required|numeric|between:-90,90',
            'alamat_lng' => 'required|numeric|between:-180,180',
            'items' => 'nullable|array',
            'items.*.jumlah' => 'nullable|integer|min:1',
        ]);

        $office = $this->officeCoordinates();
        $destinationLat = (float) $validated['alamat_lat'];
        $destinationLng = (float) $validated['alamat_lng'];
        $distanceKm = $this->calculateDistanceKm($office['lat'], $office['lng'], $destinationLat, $destinationLng);

        $itemCount = array_sum(array_map(fn ($item) => (int) ($item['jumlah'] ?? 0), (array) ($validated['items'] ?? [])));
        $ongkir = $this->estimateOngkir($distanceKm, max(1, $itemCount));

        return response()->json([
            'success' => true,
            'message' => 'Estimasi ongkir berhasil dihitung.',
            'data' => [
                'provider' => 'Lalamove API (estimasi)',
                'metode_pengiriman' => 'diantar',
                'distance_km' => $distanceKm,
                'ongkir' => $ongkir,
                'origin' => $office,
                'destination' => [
                    'lat' => $destinationLat,
                    'lng' => $destinationLng,
                    'alamat_maps' => (string) $validated['alamat_maps'],
                    'alamat_detail' => (string) $validated['alamat_detail'],
                ],
            ],
        ]);
    }

public function orderAddressSuggest(Request $request)
{
    $validated = $request->validate([
        'q' => 'required|string|min:3|max:255',
    ]);

    $query = trim((string) $validated['q']);
    if ($query === '') {
        return response()->json(['success' => true, 'data' => []]);
    }

    try {
        // Coba Nominatim dulu
        $results = $this->fetchNominatim($query);

        // Kalau hasil kurang dari 2, fallback ke Photon
        if (count($results) < 2) {
            $photon = $this->fetchPhoton($query);
            $results = array_merge($results, $photon);
        }

        // Dedupe berdasarkan display_name
        $seen = [];
        $results = array_filter($results, function ($item) use (&$seen) {
            if (in_array($item['display_name'], $seen)) return false;
            $seen[] = $item['display_name'];
            return true;
        });

        return response()->json([
            'success' => true,
            'data' => array_values($results),
        ]);

    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil saran alamat.',
            'data' => [],
        ], 500);
    }
}

private function fetchNominatim(string $query): array
{
    try {
        $response = Http::timeout(8)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => (string) env('OSM_USER_AGENT', 'PD-Anugrah-Utama/1.0'),
                'Referer'    => (string) config('app.url'),
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q'               => $query . ' Indonesia',
                'format'          => 'jsonv2',
                'addressdetails'  => 1,
                'namedetails'     => 1,
                'limit'           => 8,
                'countrycodes'    => 'id',
                'accept-language' => 'id',
                'dedupe'          => 1,
            ]);

        if (!$response->ok()) return [];

        return collect($response->json() ?? [])
            ->map(function ($item) {
                $addr  = is_array($item['address'] ?? null) ? $item['address'] : [];
                $admin = $this->extractAdministrativeAddress($addr);

                return [
                    'display_name' => $this->normalizedDisplayAddress($item, $addr),
                    'lat'          => isset($item['lat']) ? (float) $item['lat'] : null,
                    'lng'          => isset($item['lon']) ? (float) $item['lon'] : null,
                    'provinsi'     => $admin['provinsi'] ?? '',
                    'kota'         => $admin['kota']     ?? '',
                    'kecamatan'    => $admin['kecamatan'] ?? '',
                    'kode_pos'     => $admin['kode_pos']  ?? '',
                ];
            })
            ->filter(fn ($i) => $i['display_name'] !== '' && !is_null($i['lat']))
            ->values()
            ->all();

    } catch (\Throwable) {
        return [];
    }
}

private function fetchPhoton(string $query): array
{
    try {
        $response = Http::timeout(8)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => (string) env('OSM_USER_AGENT', 'PD-Anugrah-Utama/1.0'),
            ])
            ->get('https://photon.komoot.io/api/', [
                'q'    => $query,
                'lang' => 'id',
                'limit'=> 6,
                'bbox' => '95.0,-11.0,141.0,6.0', // bounding box Indonesia
            ]);

        if (!$response->ok()) return [];

        $features = $response->json()['features'] ?? [];

        return collect($features)
            ->map(function ($feature) {
                $props = $feature['properties'] ?? [];
                $coords = $feature['geometry']['coordinates'] ?? [null, null];

                // Bangun display_name dari properties Photon
                $parts = array_filter([
                    $props['name']     ?? null,
                    $props['street']   ?? null,
                    $props['district'] ?? null,
                    $props['city']     ?? ($props['county'] ?? null),
                    $props['state']    ?? null,
                    $props['postcode'] ?? null,
                    'Indonesia',
                ]);

                $displayName = implode(', ', $parts);

                return [
                    'display_name' => $displayName,
                    'lat'          => isset($coords[1]) ? (float) $coords[1] : null,
                    'lng'          => isset($coords[0]) ? (float) $coords[0] : null,
                    'provinsi'     => $props['state']    ?? '',
                    'kota'         => $props['city']     ?? ($props['county'] ?? ''),
                    'kecamatan'    => $props['district'] ?? '',
                    'kode_pos'     => $props['postcode'] ?? '',
                ];
            })
            ->filter(fn ($i) => $i['display_name'] !== '' && !is_null($i['lat']))
            ->values()
            ->all();

    } catch (\Throwable) {
        return [];
    }
}

    public function orderPayment(Pesanan $pesanan)
    {
        $pesanan->loadMissing(['details.produk', 'serviceJenisRefill', 'servicePaket']);
        $banks = $this->bankAccounts();

        return view('public.order.payment', compact('pesanan', 'banks'));
    }

    public function orderPaymentStore(Request $request, Pesanan $pesanan, InventoryService $inventoryService)
    {
        $banks = $this->bankAccounts();

        $validated = $request->validate([
            'metode_pembayaran' => 'required|in:transfer',
            'bank' => 'nullable|string',
            'bukti_pembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $lockedBank = (string) ($pesanan->bank ?: $validated['bank'] ?: array_key_first($banks));

        if (!array_key_exists($lockedBank, $banks)) {
            return back()->withErrors(['bank' => 'Bank transfer tidak valid.'])->withInput();
        }

        $kodeNego = $this->normalizeNegoCode((string) ($pesanan->kode_nego ?? ''));
        if ($kodeNego !== '') {
            $kodeSudahDipakaiOrderLain = Pesanan::query()
                ->where('kode_nego', $kodeNego)
                ->where(function ($query) {
                    $query->whereNotNull('kode_nego_terpakai_at')
                        ->orWhereNotNull('bukti_pembayaran');
                })
                ->where('id', '!=', $pesanan->id)
                ->exists();

            if (!empty($pesanan->bukti_pembayaran) || $kodeSudahDipakaiOrderLain) {
                $message = 'Kode negosiasi sudah dipakai sebelumnya dan tidak dapat digunakan lagi.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return back()->withErrors(['kode_nego' => $message])->withInput();
            }
        }

        $proofPath = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        $pesanan->loadMissing(['details.produk', 'pelanggan', 'serviceJenisRefill', 'servicePaket.peralatans', 'service']);

        $isDealOrder = !empty($pesanan->kode_nego) && !is_null($pesanan->harga_usulan);
        $statusAfterPayment = $pesanan->tipe === 'service'
            ? ($pesanan->service_metode_penanganan === 'antar sendiri'
                ? Pesanan::STATUS_MENUNGGU_KEDATANGAN_UNIT
                : Pesanan::STATUS_MENUNGGU_PENGAMBILAN)
            : Pesanan::STATUS_DIPROSES;

        $payableTotal = $pesanan->payableTotal();

        DB::transaction(function () use ($pesanan, $validated, $lockedBank, $isDealOrder, $proofPath, $inventoryService, $statusAfterPayment, $payableTotal) {
            $pesanan->update([
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'bank' => $lockedBank,
                'total_harga' => $payableTotal ?: ($pesanan->total_harga ?: $pesanan->total),
                'tipe_harga' => $isDealOrder ? 'deal' : ($pesanan->tipe_harga ?: 'normal'),
                'status' => $statusAfterPayment,
                'bukti_pembayaran' => $proofPath,
                'pembayaran_terkonfirmasi_at' => now(),
                'kode_nego_terpakai_at' => !empty($pesanan->kode_nego)
                    ? ($pesanan->kode_nego_terpakai_at ?: now())
                    : $pesanan->kode_nego_terpakai_at,
            ]);

            if ($pesanan->tipe === 'produk') {
                $pesanan->reduceStock();
            } elseif (
                !$pesanan->stok_dikurangi
                && $pesanan->serviceJenisRefill
                && (float) ($pesanan->service_total_kg ?? 0) > 0
            ) {
                $inventoryService->decreaseRefillStock(
                    jenisRefill: $pesanan->serviceJenisRefill,
                    qty: (float) $pesanan->service_total_kg,
                    sourceType: StockMovement::SOURCE_REFILL_PELANGGAN,
                    reference: $pesanan,
                    keterangan: "Pemakaian {$pesanan->serviceJenisRefill->nama_label} untuk pesanan layanan #{$pesanan->id}",
                    tanggal: now(),
                );

                $pesanan->update(['stok_dikurangi' => true]);
            }

            if ($pesanan->isPackageServiceOrder()) {
                $this->syncServiceLogForPackageOrder($pesanan);
            }
        });

        $pesanan->refresh()->loadMissing(['details.produk', 'pelanggan', 'serviceJenisRefill', 'servicePaket.peralatans', 'service']);
        $pesanan->pelanggan?->update(['status' => 'tetap']);
        if ($pesanan->tipe === 'produk') {
            $this->syncUnitAparsForPesanan($pesanan->fresh(['details.produk.jenisApar', 'unitApars']));
        }

        $orderCode = $this->buildOrderCode($pesanan);
        $tipeHargaLabel = $pesanan->tipe_harga === 'deal' ? 'Harga Deal' : 'Harga Normal';
        $total = number_format($pesanan->payableTotal(), 0, ',', '.');
        $ongkir = number_format((float) ($pesanan->ongkir ?: 0), 0, ',', '.');
        $metodePengiriman = $this->shippingMethodLabel((string) ($pesanan->metode_pengiriman ?: 'pickup'));
        $bankName = '-';
        if ($pesanan->metode_pembayaran === 'transfer' && $pesanan->bank && isset($banks[$pesanan->bank])) {
            $bankName = $banks[$pesanan->bank]['nama_bank'];
        }

        if ($pesanan->tipe === 'service') {
            $layananUtama = $pesanan->service_jenis_layanan === 'refill'
                ? ($pesanan->serviceJenisRefill?->nama_label ?: 'Refill APAR')
                : ($pesanan->servicePaket?->nama ?: 'Service APAR');
            $detailItems = "- {$layananUtama}\n"
                . "- Ukuran: " . ($pesanan->service_ukuran_apar ?: '-') . "\n"
                . "- Jumlah Unit: " . ((int) ($pesanan->service_jumlah_unit ?? 0)) . "\n"
                . "- Metode: " . ($pesanan->service_metode_penanganan === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput');
        } else {
            $lines = [];
            foreach ($pesanan->details as $detail) {
                $namaProduk = $detail->produk?->nama ?? 'Produk';
                $subtotal = number_format((float) $detail->subtotal, 0, ',', '.');
                $lines[] = "- {$namaProduk} x {$detail->jumlah} = Rp {$subtotal}";
            }
            $detailItems = implode("\n", $lines);
        }

        $waNumber = preg_replace('/^0/', '62', env('WHATSAPP_CONTACT', '082124716109'));
        $waMessage = "Halo Admin, saya sudah mengonfirmasi pembayaran pesanan.\n\n"
            . "Kode Pesanan: {$orderCode}\n"
            . "Nama: " . ($pesanan->pelanggan?->nama ?? '-') . "\n"
            . "No WA: " . ($pesanan->pelanggan?->no_wa ?? '-') . "\n"
            . "Kategori: " . $pesanan->trackingTypeLabel() . "\n"
            . "Tipe Harga: {$tipeHargaLabel}\n"
            . "Total Bayar: Rp {$total}\n"
            . "Metode: " . strtoupper($pesanan->metode_pembayaran) . "\n"
            . "Pengiriman: {$metodePengiriman}\n"
            . "Ongkir: Rp {$ongkir}\n"
            . "Bank: {$bankName}\n\n"
            . "Detail Item:\n{$detailItems}";
        $waUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waMessage);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tersimpan.',
                'wa_url' => $waUrl,
                'redirect_url' => route('cek-apar'),
            ]);
        }

        return redirect()
            ->route('cek-apar')
            ->with('success', 'Pembayaran tersimpan. Bukti berhasil dikirim ke admin.')
            ->with('pelanggan_id', $pesanan->pelanggan_id)
            ->withInput(['no_wa' => $pesanan->pelanggan?->no_wa]);
    }

    public function checkNegoCode(Request $request)
    {
        $validated = validator($request->all(), [
            'kode_nego' => 'required|string|max:20|regex:/^ANUTA-\d{3}$/i',
            'no_wa' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ], [
            'kode_nego.regex' => 'Format kode harus ANUTA-xxx (contoh: ANUTA-123).',
        ])->validate();

        $kode = $this->normalizeNegoCode((string) $validated['kode_nego']);
        $noWa = $this->normalizePhone((string) $validated['no_wa']);
        $requestItems = (array) ($validated['items'] ?? []);

        $kodePernahDipakai = Pesanan::query()
            ->where('kode_nego', $kode)
            ->where(function ($query) {
                $query->whereNotNull('kode_nego_terpakai_at')
                    ->orWhereNotNull('bukti_pembayaran');
            })
            ->exists();

        if ($kodePernahDipakai) {
            return response()->json([
                'success' => false,
                'message' => 'Kode negosiasi sudah pernah digunakan.',
            ], 422);
        }

        $pesanan = $this->findValidNegoSource($kode);

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Kode negosiasi tidak valid atau belum disetujui admin.',
            ], 404);
        }

        if (!$this->isNegoMatchedToCurrentOrder($pesanan, $noWa, $requestItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode tidak sesuai dengan pesanan Anda.',
            ], 422);
        }

        $totalNormal = $this->getNormalOrderTotal($pesanan);
        $hargaDeal = (float) $pesanan->harga_usulan;
        
        return response()->json([
            'success' => true,
            'message' => 'Kode negosiasi valid.',
            'data' => [
                'pesanan_id' => $pesanan->id,
                'kode_nego' => $pesanan->kode_nego,
                'harga_deal' => $hargaDeal,
                'total_normal' => $totalNormal,
                'deal_includes_shipping' => true,
                'status' => $pesanan->status,
                'pelanggan' => [
                    'nama' => $pesanan->pelanggan?->nama,
                    'no_wa' => $pesanan->pelanggan?->no_wa,
                ],
                'items' => $pesanan->details->map(function ($d) {
                    return [
                        'produk' => $d->produk?->nama ?? 'Produk',
                        'qty' => (int) $d->jumlah,
                        'subtotal' => (float) $d->subtotal,
                    ];
                })->values(),
            ],
        ]);
    }

    private function syncUnitAparsForPesanan(Pesanan $pesanan): void
    {
        if ($pesanan->tipe !== 'produk' || !in_array($pesanan->status, ['diproses', 'selesai', 'selesai final'], true)) {
            return;
        }

        $pesanan->loadMissing(['details.produk.jenisApar', 'unitApars']);

        foreach ($pesanan->details as $detail) {
            $existingCount = $pesanan->unitApars
                ->where('produk_id', $detail->produk_id)
                ->count();

            $missingCount = max(0, ((int) $detail->jumlah) - $existingCount);

            if ($missingCount <= 0 || !$detail->produk) {
                continue;
            }

            $this->createUnitAparsFromDetail($pesanan, $detail->produk, $missingCount, $existingCount + 1);
            $pesanan->load('unitApars');
        }
    }

    private function createUnitAparsFromDetail(Pesanan $pesanan, Produk $produk, int $jumlah, int $startFrom = 1): void
    {
        for ($urutan = $startFrom; $urutan < $startFrom + $jumlah; $urutan++) {
            $serial = $this->generateSerialNumber($pesanan, $produk, $urutan);

            UnitApar::create([
                'pelanggan_id' => $pesanan->pelanggan_id,
                'pesanan_id' => $pesanan->id,
                'produk_id' => $produk->id,
                'no_seri' => $serial,
                'tgl_beli' => $pesanan->tanggal,
                'tgl_produksi' => $pesanan->tanggal,
                'ukuran' => $produk->kapasitas ?? '-',
                'bahan' => $produk->jenisApar?->nama ?? '-',
                'tgl_expired' => UnitApar::calculateExpiry($pesanan->tanggal, $produk->kapasitas ?? '-', $produk->jenisApar?->nama ?? '-'),
            ]);
        }
    }

    private function generateSerialNumber(Pesanan $pesanan, Produk $produk, int $urutan): string
    {
        $pesanan->loadMissing('pelanggan');
        return UnitApar::generateSerialNumber($pesanan->pelanggan, $pesanan->tanggal);
    }

    public function complainCreate()
    {
        return view('public.complain.create');
    }

    public function complainStore(Request $request)
    {
        $request->validate([
            'no_wa' => 'required|string',
            'pesanan_id' => 'nullable|exists:pesanans,id',
            'isi_complain' => 'required|string',
        ]);

        $normalizedNoWa = $this->normalizePhone((string) $request->no_wa);
        $pelanggan = Pelanggan::where('no_wa', $normalizedNoWa)->first();

        if (!$pelanggan) {
            return back()->with('error', 'Data pelanggan tidak ditemukan. Pastikan nomor WA sudah pernah transaksi.')->withInput();
        }

        Complain::create([
            'pelanggan_id' => $pelanggan->id,
            'pesanan_id' => $request->pesanan_id,
            'isi_complain' => $request->isi_complain,
            'tanggal' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Komplain Anda telah kami terima dan akan segera kami tindaklanjuti.');
    }

    public function testimoniCreate()
    {
        return view('public.testimoni.create');
    }

    public function testimoniStore(Request $request)
    {
        $request->validate([
            'no_wa' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
        ]);

        $normalizedNoWa = $this->normalizePhone((string) $request->no_wa);
        $pelanggan = Pelanggan::where('no_wa', $normalizedNoWa)->first();

        if (!$pelanggan) {
            return back()->with('error', 'Data pelanggan tidak ditemukan. Pastikan nomor WA sudah pernah transaksi.')->withInput();
        }

        Testimoni::create([
            'pelanggan_id' => $pelanggan->id,
            'rating' => $request->rating,
            'review' => $request->review,
            'tanggal' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Terima kasih atas testimoni Anda!');
    }
}
