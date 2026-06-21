<?php

namespace App\Http\Controllers;

use App\Events\PesananBaru;
use App\Models\ActivityLog;
use App\Models\JenisRefill;
use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Complain;
use App\Models\Service;
use App\Models\ServicePaket;
use App\Models\Testimoni;
use App\Models\UnitApar;
use App\Services\FinalTransactionStockService;
use App\Services\InventoryService;
use App\Services\OrderPricingService;
use App\Services\PaidOrderStockService;
use App\Services\RajaOngkirService;
use App\Services\ServicePickupPricingService;
use App\Services\ServiceMasterSyncService;
use App\Services\ServicePackagePricingService;
use App\Support\RegisteredRefillUnitSupport;
use App\Support\SessionCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PublicController extends Controller
{
    private function safelyBroadcastPesananBaru(Pesanan $pesanan): void
    {
        try {
            $pending = broadcast(new PesananBaru($pesanan))->toOthers();
            unset($pending);
        } catch (\Throwable) {
            // Abaikan jika websocket lokal tidak aktif agar alur transaksi tetap sukses.
        }
    }

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
            'rajaongkir_destination_id' => (string) ($pelanggan?->rajaongkir_destination_id ?: ''),
            'rajaongkir_destination_label' => (string) ($pelanggan?->rajaongkir_destination_label ?: ''),
            'alamat_lat' => $pelanggan?->alamat_lat,
            'alamat_lng' => $pelanggan?->alamat_lng,
        ];

        $profile['is_complete'] = filled($profile['nama'])
            && filled($profile['no_wa'])
            && filled($profile['alamat_maps'])
            && filled($profile['alamat_detail']);
        $profile['has_rajaongkir_destination'] = filled($profile['rajaongkir_destination_id']);

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
            'rajaongkir_destination_id' => $profile['rajaongkir_destination_id'],
            'rajaongkir_destination_label' => $profile['rajaongkir_destination_label'],
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

    private function normalizeMoneyInput(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/[^\d]/', '', trim((string) $value));
        if ($digits === '') {
            return null;
        }

        return (float) $digits;
    }

    private function canAccessProductOrder(Pesanan $pesanan): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeknisi()) {
            return false;
        }

        $ownerUserId = $pesanan->user_id ?? $pesanan->pelanggan?->user_id;

        return !is_null($ownerUserId) && (int) $ownerUserId === (int) $user->id;
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

    private function authenticatedCustomer(): ?Pelanggan
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

        return $user->pelanggan;
    }

    private function resolveFeedbackCustomer(Request $request): ?Pelanggan
    {
        return $this->authenticatedCustomer();
    }

    private function feedbackLinkDescription(Pelanggan $pelanggan, Pesanan $pesanan): string
    {
        return 'testimoni-order:' . $pesanan->id . ':pelanggan:' . $pelanggan->id;
    }

    private function resolveFeedbackOrder(Request $request, ?Pelanggan $pelanggan = null): ?Pesanan
    {
        $pesananId = $request->input('pesanan_id', $request->query('pesanan'));

        if (blank($pesananId)) {
            return null;
        }

        $pesanan = Pesanan::with([
            'complain',
            'pelanggan',
            'details.produk',
            'service',
            'serviceJenisRefill',
            'servicePaket',
        ])->find($pesananId);
        if (!$pesanan) {
            return null;
        }

        if ($pelanggan && (int) $pesanan->pelanggan_id !== (int) $pelanggan->id) {
            return null;
        }

        return $pesanan;
    }

    private function resolveLinkedTestimoniForOrder(Pelanggan $pelanggan, Pesanan $pesanan): ?Testimoni
    {
        $directTestimoni = Testimoni::query()
            ->where('pelanggan_id', $pelanggan->id)
            ->where('transaksi_type', Pesanan::class)
            ->where('transaksi_id', $pesanan->id)
            ->latest('id')
            ->first();

        if ($directTestimoni) {
            return $directTestimoni;
        }

        $link = ActivityLog::query()
            ->where('log_name', 'feedback')
            ->where('event', 'linked_to_order')
            ->where('subject_type', Testimoni::class)
            ->where('description', $this->feedbackLinkDescription($pelanggan, $pesanan))
            ->latest('id')
            ->first();

        if (!$link || !$link->subject_id) {
            return null;
        }

        return Testimoni::find($link->subject_id);
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

    private function normalizeShippingMethod(string $method): string
    {
        $method = strtolower(trim($method));

        if ($method === 'diantar_internal') {
            return 'diantar';
        }
        if ($method === 'ambil_sendiri') {
            return 'pickup';
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
            ? 'Diantar (Ekspedisi)'
            : 'Ambil Sendiri';
    }

    private function buildCombinedAddress(string $mapsAddress, string $detailAddress): string
    {
        $parts = array_filter([
            trim($mapsAddress),
            trim($detailAddress),
        ], fn ($value) => $value !== '');

        return implode(' | Detail: ', $parts);
    }

    private function rajaOngkirService(): RajaOngkirService
    {
        return app(RajaOngkirService::class);
    }

    private function servicePickupPricingService(): ServicePickupPricingService
    {
        return app(ServicePickupPricingService::class);
    }

    private function authenticatedCheckoutCustomer(): ?Pelanggan
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

        return $user->pelanggan;
    }

    private function shippingWeightFromSize(?string $size): int
    {
        $weightKg = $this->extractAparCapacityKg($size);
        if (!is_null($weightKg) && $weightKg > 0) {
            return max(1000, (int) round($weightKg * 1000));
        }

        return $this->rajaOngkirService()->defaultWeight();
    }

    private function calculateProductShippingWeight(array $items): int
    {
        $productIds = collect($items)
            ->pluck('produk_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $products = Produk::query()
            ->whereIn('id', $productIds->all())
            ->get(['id', 'kapasitas'])
            ->keyBy('id');

        $totalWeight = collect($items)->sum(function (array $item) use ($products) {
            $qty = max(1, (int) ($item['jumlah'] ?? 0));
            $produk = $products->get((int) ($item['produk_id'] ?? 0));
            $perUnitWeight = $this->shippingWeightFromSize($produk?->kapasitas);

            return $perUnitWeight * $qty;
        });

        return max(1000, (int) $totalWeight);
    }

    private function calculateServiceShippingWeight(Collection $selectedUnitApars, ?string $manualSize, int $unitCount): int
    {
        if ($selectedUnitApars->isNotEmpty()) {
            $totalWeight = $selectedUnitApars->sum(function (UnitApar $unitApar) {
                return $this->shippingWeightFromSize(
                    $unitApar->ukuran ?: $unitApar->produk?->kapasitas
                );
            });

            return max(1000, (int) $totalWeight);
        }

        $perUnitWeight = $this->shippingWeightFromSize($manualSize);

        return max(1000, $perUnitWeight * max(1, $unitCount));
    }

    private function resolveCheckoutDestinationData(Request $request, ?Pelanggan $pelanggan = null): array
    {
        $destinationId = trim((string) $request->input(
            'rajaongkir_destination_id',
            (string) ($pelanggan?->rajaongkir_destination_id ?? '')
        ));

        $destinationLabel = trim((string) $request->input(
            'rajaongkir_destination_label',
            (string) ($pelanggan?->rajaongkir_destination_label ?? '')
        ));

        return [
            'id' => $destinationId !== '' ? $destinationId : null,
            'label' => $destinationLabel !== '' ? $destinationLabel : null,
        ];
    }

    private function baseShippingMeta(Request $request, string $storageMethod, ?Pelanggan $pelanggan = null): array
    {
        $mapsAddress = trim((string) $request->input('alamat_maps', (string) ($pelanggan?->alamat_maps ?? '')));
        $detailAddress = trim((string) $request->input('alamat_detail', (string) ($pelanggan?->alamat_detail ?? '')));
        $lat = $this->sanitizeCoordinate($request->input('alamat_lat'));
        $lng = $this->sanitizeCoordinate($request->input('alamat_lng'));

        if (is_null($lat) && $pelanggan) {
            $lat = $this->sanitizeCoordinate($pelanggan->alamat_lat);
        }

        if (is_null($lng) && $pelanggan) {
            $lng = $this->sanitizeCoordinate($pelanggan->alamat_lng);
        }

        $destination = $this->resolveCheckoutDestinationData($request, $pelanggan);

        return [
            'metode_pengiriman' => $storageMethod,
            'ongkir' => 0.0,
            'shipping_courier' => null,
            'shipping_service' => null,
            'shipping_etd' => null,
            'shipping_destination_id' => $destination['id'],
            'shipping_destination_label' => $destination['label'],
            'shipping_weight' => 0,
            'distance_km' => null,
            'alamat_maps' => $mapsAddress ?: null,
            'alamat_detail' => $detailAddress ?: null,
            'alamat_lat' => $lat,
            'alamat_lng' => $lng,
        ];
    }

    private function attachRajaOngkirQuote(array $meta, int $weight, ?string $courier = null): array
    {
        $destinationId = trim((string) ($meta['shipping_destination_id'] ?? ''));
        if ($destinationId === '') {
            throw new RuntimeException('Lokasi pengiriman belum dapat digunakan untuk menghitung ongkir. Silakan perbarui alamat pengiriman Anda.');
        }

        $quote = $this->rajaOngkirService()->getCheapestCost($destinationId, $weight, $courier);

        $meta['ongkir'] = (float) ($quote['cost'] ?? 0);
        $meta['shipping_courier'] = (string) ($quote['courier_code'] ?? '');
        $meta['shipping_service'] = trim(implode(' - ', array_filter([
            (string) ($quote['service'] ?? ''),
            (string) ($quote['service_description'] ?? ''),
        ])));
        $meta['shipping_etd'] = (string) ($quote['etd'] ?? '');
        $meta['shipping_weight'] = (int) ($quote['weight'] ?? $weight);

        return $meta;
    }

    private function attachServicePickupQuote(array $meta): array
    {
        $quote = $this->servicePickupPricingService()->quote(
            $meta['alamat_lat'] ?? null,
            $meta['alamat_lng'] ?? null,
        );

        $meta['ongkir'] = (float) ($quote['cost'] ?? 0);
        $meta['shipping_courier'] = null;
        $meta['shipping_service'] = null;
        $meta['shipping_etd'] = null;
        $meta['shipping_weight'] = 0;
        $meta['distance_km'] = (float) ($quote['round_trip_distance_km'] ?? 0);
        $meta['pickup_quote'] = $quote;

        return $meta;
    }

    private function buildDeliveryMeta(Request $request, array $items, ?Pelanggan $pelanggan = null): array
    {
        $method = $this->normalizeShippingMethod((string) $request->input('metode_pengiriman', 'pickup'));
        $meta = $this->baseShippingMeta(
            request: $request,
            storageMethod: $this->shippingMethodForStorage($method),
            pelanggan: $pelanggan,
        );

        if ($method === 'pickup') {
            return $meta;
        }

        return $this->attachRajaOngkirQuote(
            meta: $meta,
            weight: $this->calculateProductShippingWeight($items),
            courier: trim((string) $request->input('shipping_courier', '')) ?: null,
        );
    }

    private function buildServiceDeliveryMeta(
        Request $request,
        Collection $selectedUnitApars,
        string $serviceUkuranApar,
        int $unitCount,
        string $serviceMetode,
        ?Pelanggan $pelanggan = null
    ): array {
        unset($selectedUnitApars, $serviceUkuranApar, $unitCount);

        $isPickup = $serviceMetode === 'antar sendiri';

        $meta = $this->baseShippingMeta(
            request: $request,
            storageMethod: $isPickup ? 'pickup' : 'diantar_internal',
            pelanggan: $pelanggan,
        );

        if ($isPickup) {
            return $meta;
        }

        return $this->attachServicePickupQuote($meta);
    }

    private function resolveRajaOngkirWeightFromRequest(Request $request): int
    {
        $orderType = mb_strtolower(trim((string) $request->input('order_type', $request->input('tipe_layanan', ''))));

        if (in_array($orderType, ['beli', 'produk'], true)) {
            return $this->calculateProductShippingWeight((array) $request->input('items', []));
        }

        $selectedUnitIds = collect((array) $request->input('service_unit_apar_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $selectedUnitApars = UnitApar::query()
            ->with('produk')
            ->whereIn('id', $selectedUnitIds->all())
            ->get();

        $manualSize = trim((string) $request->input('service_ukuran_apar', ''));
        $unitCount = max(1, (int) $request->input('service_jumlah_unit', 1));

        return $this->calculateServiceShippingWeight($selectedUnitApars, $manualSize, $unitCount);
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
        if (filled(trim((string) $request->input('rajaongkir_destination_id', '')))) {
            $update['rajaongkir_destination_id'] = trim((string) $request->input('rajaongkir_destination_id'));
        }
        if (filled(trim((string) $request->input('rajaongkir_destination_label', '')))) {
            $update['rajaongkir_destination_label'] = trim((string) $request->input('rajaongkir_destination_label'));
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
        if (filled(trim((string) ($validated['rajaongkir_destination_id'] ?? '')))) {
            $update['rajaongkir_destination_id'] = trim((string) ($validated['rajaongkir_destination_id']));
        }
        if (filled(trim((string) ($validated['rajaongkir_destination_label'] ?? '')))) {
            $update['rajaongkir_destination_label'] = trim((string) ($validated['rajaongkir_destination_label']));
        }

        if (!empty($update)) {
            $update['alamat'] = $alamatGabungan;
            $pelanggan->update($update);
        }
    }

    // Removed negotiation helpers

    private function authenticatedCartItems(): Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        return SessionCart::items();
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
                    'merek' => (string) ($item->produk?->merek ?? 'FIREFIX'),
                ];
            })
            ->values()
            ->all();
    }

    private function registeredUnitAparQuery(Pelanggan $pelanggan)
    {
        return UnitApar::query()
            ->visible()
            ->with(['produk.jenisApar', 'pesanan'])
            ->where('pelanggan_id', $pelanggan->id)
            ->where(function ($query) {
                $query->whereNull('kondisi_awal')
                    ->orWhereRaw('LOWER(kondisi_awal) <> ?', ['tidak_aktif']);
            })
            ->where(function ($query) {
                $query->whereDoesntHave('pesanan')
                    ->orWhereHas('pesanan', function ($pesananQuery) {
                        $pesananQuery->whereIn('status', [
                            Pesanan::STATUS_SELESAI,
                            Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
                            Pesanan::STATUS_DIKONFIRMASI_ADMIN,
                            Pesanan::STATUS_SELESAI_FINAL,
                        ]);
                    });
            });
    }

    private function registeredUnitAparLabel(UnitApar $unitApar): string
    {
        $unitApar->loadMissing('produk.jenisApar');

        $parts = [
            $unitApar->no_seri ?: 'UNIT-' . $unitApar->id,
            $unitApar->produk?->nama ?: 'Produk APAR',
            $unitApar->produk?->jenisApar?->nama ?: $unitApar->bahan,
            $unitApar->ukuran ?: $unitApar->produk?->kapasitas,
        ];

        return collect($parts)
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->implode(' - ');
    }

    private function registeredUnitAparExpiryLabel(UnitApar $unitApar): string
    {
        return $unitApar->tgl_expired
            ? $unitApar->tgl_expired->translatedFormat('d F Y')
            : '-';
    }

    private function registeredUnitAparTypeLabel(UnitApar $unitApar): string
    {
        $unitApar->loadMissing('produk.jenisApar');

        return trim((string) (
            $unitApar->produk?->jenisApar?->nama
            ?: $unitApar->bahan
            ?: 'APAR'
        ));
    }

    private function registeredUnitAparPurchaseKey(UnitApar $unitApar): string
    {
        return $unitApar->tgl_beli ? $unitApar->tgl_beli->toDateString() : 'tanpa-tanggal';
    }

    private function registeredUnitAparPurchaseLabel(UnitApar $unitApar): string
    {
        return $unitApar->tgl_beli
            ? $unitApar->tgl_beli->translatedFormat('d F Y')
            : 'Tanpa tanggal pembelian';
    }

    private function registeredUnitAparStatusLabel(UnitApar $unitApar): string
    {
        $status = mb_strtolower(trim((string) ($unitApar->kondisi_awal ?? '')));

        return match ($status) {
            'tidak_aktif' => 'Tidak Aktif',
            'perlu_servis' => 'Perlu Servis',
            'aktif', 'valid', 'layak', '' => 'Aktif',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    private function normalizeRefillMatchingText(?string $value): string
    {
        $text = mb_strtolower(trim((string) $value));
        $text = preg_replace('/[^a-z0-9]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function matchJenisRefillForRegisteredUnit(UnitApar $unitApar, ?Collection $jenisRefills = null): ?JenisRefill
    {
        $jenisRefills ??= JenisRefill::query()->get();
        $source = $this->normalizeRefillMatchingText(
            $this->registeredUnitAparTypeLabel($unitApar) . ' ' . ($unitApar->produk?->nama ?? '')
        );

        if ($source === '') {
            return null;
        }

        return $jenisRefills->first(function (JenisRefill $jenisRefill) use ($source) {
            $nama = $this->normalizeRefillMatchingText((string) $jenisRefill->nama);
            $label = $this->normalizeRefillMatchingText((string) $jenisRefill->nama_label);

            return ($nama !== '' && (str_contains($source, $nama) || str_contains($nama, $source)))
                || ($label !== '' && (str_contains($source, $label) || str_contains($label, $source)));
        });
    }

    private function resolveRefillPriceForUkuran(JenisRefill $jenisRefill, string $ukuran): ?float
    {
        $price = $jenisRefill->resolveServicePrice($ukuran);
        if (!is_null($price) && $price > 0) {
            return (float) $price;
        }

        $ukuranKg = $this->extractAparCapacityKg($ukuran);
        if (!is_null($ukuranKg) && $ukuranKg > 0 && (float) $jenisRefill->harga > 0) {
            return (float) ($ukuranKg * (float) $jenisRefill->harga);
        }

        return null;
    }

    private function formatKgNumber(float $value): string
    {
        $formatted = number_format($value, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted !== '' ? $formatted : '0';
    }

    private function summarizeRegisteredRefillUnits(Collection $unitApars): array
    {
        $jenisRefills = JenisRefill::query()->get();
        $lineAmounts = [];
        $unitDetails = [];
        $stockRequirements = [];
        $totalKebutuhanKg = 0.0;
        $estimasiBiaya = 0.0;

        foreach ($unitApars as $unitApar) {
            $jenisRefill = $this->matchJenisRefillForRegisteredUnit($unitApar, $jenisRefills);
            if (!$jenisRefill) {
                throw ValidationException::withMessages([
                    'service_unit_apar_ids' => 'Jenis refill otomatis belum ditemukan dari Unit APAR terdaftar yang dipilih.',
                ]);
            }

            $ukuran = trim((string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: ''));
            $unitPrice = $this->resolveRefillPriceForUkuran($jenisRefill, $ukuran);
            if (is_null($unitPrice) || $unitPrice <= 0) {
                throw ValidationException::withMessages([
                    'service_unit_apar_ids' => 'Harga refill otomatis untuk salah satu Unit APAR terdaftar belum tersedia.',
                ]);
            }

            $unitKg = (float) ($this->extractAparCapacityKg($ukuran) ?? 0);
            $totalKebutuhanKg += $unitKg;
            $estimasiBiaya += $unitPrice;
            $lineAmounts[(int) $unitApar->id] = (float) $unitPrice;
            $unitDetails[(int) $unitApar->id] = [
                'refill_id' => (int) $jenisRefill->id,
                'refill_label' => (string) $jenisRefill->nama_label,
                'usage_kg' => (float) round($unitKg, 2),
                'unit_price' => (float) $unitPrice,
            ];

            if (! isset($stockRequirements[(int) $jenisRefill->id])) {
                $stockRequirements[(int) $jenisRefill->id] = [
                    'jenis_refill' => $jenisRefill,
                    'qty' => 0.0,
                ];
            }

            $stockRequirements[(int) $jenisRefill->id]['qty'] += (float) round($unitKg, 2);
        }

        $matchedRefillIds = collect(array_keys($stockRequirements))->filter()->values();
        /** @var JenisRefill|null $jenisRefill */
        $jenisRefill = $matchedRefillIds->count() === 1
            ? $jenisRefills->firstWhere('id', (int) $matchedRefillIds->first())
            : null;

        return [
            'jenis_refill' => $jenisRefill,
            'line_amounts' => $lineAmounts,
            'unit_details' => $unitDetails,
            'stock_requirements' => collect($stockRequirements)
                ->map(function (array $requirement) {
                    $requirement['qty'] = (float) round((float) ($requirement['qty'] ?? 0), 2);

                    return $requirement;
                })
                ->values()
                ->all(),
            'is_mixed' => $matchedRefillIds->count() > 1,
            'total_kg' => round($totalKebutuhanKg, 2),
            'estimasi_biaya' => (float) round($estimasiBiaya, 0),
        ];
    }

    private function buildRegisteredServicePackageLineSpecs(Collection $unitApars): array
    {
        return $unitApars
            ->map(function (UnitApar $unitApar) {
                $ukuran = trim((string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: ''));
                $media = $this->registeredUnitAparTypeLabel($unitApar);

                return [
                    'unit_id' => (int) $unitApar->id,
                    'label' => $this->registeredUnitAparLabel($unitApar),
                    'media' => $media,
                    'ukuran' => $ukuran,
                    'qty' => 1,
                ];
            })
            ->values()
            ->all();
    }

    private function buildManualServicePackageLineSpec(string $media, string $ukuran, int $jumlahUnit): array
    {
        $media = trim($media);
        $ukuran = trim($ukuran);

        return [[
            'label' => trim('APAR ' . $media . ' ' . $ukuran),
            'media' => $media,
            'ukuran' => $ukuran,
            'qty' => max(1, $jumlahUnit),
        ]];
    }

    private function buildServicePackageOrderNote(
        ServicePaket $paket,
        array $lineItems,
        array $peralatanItems,
        float $total,
        string $metode,
        string $customerNote,
        ?string $purchaseLabel = null
    ): string {
        $lines = [];

        $lines[] = 'Rincian Service ' . trim(($paket->label ?: 'Paket') . ' - ' . $paket->nama);

        foreach (array_values($lineItems) as $index => $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $qtyLabel = $qty > 1 ? " x {$qty} unit" : '';
            $lines[] = ($index + 1) . '. '
                . trim((string) ($item['label'] ?? '-'))
                . $qtyLabel
                . ' - Rp'
                . number_format((float) ($item['total'] ?? 0), 0, ',', '.');
        }

        if (!empty($peralatanItems)) {
            $lines[] = 'Peralatan Paket:';
            foreach ($peralatanItems as $peralatanItem) {
                $lines[] = '- '
                    . trim((string) ($peralatanItem['nama'] ?? '-'))
                    . ' x'
                    . (int) ($peralatanItem['jumlah'] ?? 0);
            }
        }

        $lines[] = 'Total Service: Rp' . number_format($total, 0, ',', '.');
        $lines[] = 'Metode Penanganan: ' . ($metode === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput');
        $lines[] = 'Catatan Pelanggan: ' . (trim($customerNote) !== '' ? trim($customerNote) : '-');

        return implode("\n", $lines);
    }

    private function normalizeManualRefillItems(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                return [
                    'jenis_refill_id' => (int) ($item['jenis_refill_id'] ?? 0),
                    'ukuran_apar' => trim((string) ($item['ukuran_apar'] ?? '')),
                    'jumlah_unit' => max(1, (int) ($item['jumlah_unit'] ?? 1)),
                ];
            })
            ->filter(fn (array $item) => $item['jenis_refill_id'] > 0 || $item['ukuran_apar'] !== '')
            ->values()
            ->all();
    }

    private function normalizeManualServiceItems(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                return [
                    'jenis_apar' => trim((string) ($item['jenis_apar'] ?? '')),
                    'service_paket_id' => (int) ($item['service_paket_id'] ?? 0),
                    'ukuran_apar' => trim((string) ($item['ukuran_apar'] ?? '')),
                    'jumlah_unit' => max(1, (int) ($item['jumlah_unit'] ?? 1)),
                ];
            })
            ->filter(fn (array $item) => $item['service_paket_id'] > 0 || $item['ukuran_apar'] !== '' || $item['jenis_apar'] !== '')
            ->values()
            ->all();
    }

    private function buildManualRefillOrderSummary(array $items, array $serviceUkuranOptions): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'service_refill_items' => 'Tambahkan minimal satu item refill terlebih dahulu.',
            ]);
        }

        $sizeMap = collect($serviceUkuranOptions)
            ->mapWithKeys(function ($size) {
                $label = trim((string) $size);

                return [mb_strtolower($label) => $label];
            })
            ->all();
        $jenisRefills = JenisRefill::query()
            ->whereIn('id', collect($items)->pluck('jenis_refill_id')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        $lineItems = [];
        $stockRequirements = [];
        $totalUnits = 0;
        $totalKg = 0.0;
        $totalPrice = 0.0;

        foreach (array_values($items) as $index => $item) {
            $jenisRefillId = (int) ($item['jenis_refill_id'] ?? 0);
            $ukuranKey = mb_strtolower(trim((string) ($item['ukuran_apar'] ?? '')));
            $jumlahUnit = max(1, (int) ($item['jumlah_unit'] ?? 1));

            $jenisRefill = $jenisRefills->get($jenisRefillId);
            if (! $jenisRefill) {
                throw ValidationException::withMessages([
                    'service_refill_items' => 'Jenis refill pada salah satu item belum valid.',
                ]);
            }

            if ($ukuranKey === '' || ! isset($sizeMap[$ukuranKey])) {
                throw ValidationException::withMessages([
                    'service_refill_items' => 'Ukuran APAR pada salah satu item refill belum valid.',
                ]);
            }

            $ukuran = $sizeMap[$ukuranKey];
            $ukuranKg = (float) ($this->extractAparCapacityKg($ukuran) ?? 0);
            if ($ukuranKg <= 0) {
                throw ValidationException::withMessages([
                    'service_refill_items' => 'Ukuran APAR pada salah satu item refill belum bisa dihitung ke satuan Kg.',
                ]);
            }

            $unitPrice = $this->resolveRefillPriceForUkuran($jenisRefill, $ukuran);
            if (is_null($unitPrice) || $unitPrice <= 0) {
                throw ValidationException::withMessages([
                    'service_refill_items' => 'Harga standar refill untuk salah satu item belum tersedia.',
                ]);
            }

            $itemTotalKg = round($ukuranKg * $jumlahUnit, 2);
            $itemTotalPrice = (float) round($unitPrice * $jumlahUnit, 0);

            $lineItems[] = [
                'index' => $index + 1,
                'refill' => $jenisRefill,
                'refill_id' => (int) $jenisRefill->id,
                'label' => (string) $jenisRefill->nama_label,
                'display_label' => $jenisRefill->nama_label . ' | ' . $ukuran,
                'ukuran' => $ukuran,
                'qty' => $jumlahUnit,
                'unit_price' => (float) $unitPrice,
                'total' => $itemTotalPrice,
                'total_kg' => $itemTotalKg,
            ];

            if (! isset($stockRequirements[(int) $jenisRefill->id])) {
                $stockRequirements[(int) $jenisRefill->id] = [
                    'jenis_refill' => $jenisRefill,
                    'qty' => 0.0,
                ];
            }

            $stockRequirements[(int) $jenisRefill->id]['qty'] += $itemTotalKg;
            $totalUnits += $jumlahUnit;
            $totalKg += $itemTotalKg;
            $totalPrice += $itemTotalPrice;
        }

        $stockRequirements = collect($stockRequirements)
            ->map(function (array $requirement) {
                $requirement['qty'] = (float) round((float) ($requirement['qty'] ?? 0), 2);

                return $requirement;
            })
            ->values();

        $singleRefill = $stockRequirements->count() === 1
            ? $stockRequirements->first()['jenis_refill']
            : null;

        return [
            'line_items' => $lineItems,
            'stock_requirements' => $stockRequirements->all(),
            'single_refill_id' => $singleRefill?->id,
            'headline' => collect($lineItems)->pluck('label')->unique()->implode(', '),
            'display_summary' => collect($lineItems)->pluck('label')->unique()->implode(', '),
            'ukuran_summary' => collect($lineItems)->pluck('ukuran')->unique()->implode(', '),
            'total_units' => $totalUnits,
            'total_kg' => (float) round($totalKg, 2),
            'estimasi_biaya' => (float) round($totalPrice, 0),
            'stock_unit' => $singleRefill?->satuan_label ?: 'Kg',
        ];
    }

    private function aggregatePeralatanSummary(array $peralatanItems): array
    {
        return collect($peralatanItems)
            ->reduce(function (array $carry, array $item) {
                $peralatanId = (int) ($item['peralatan_id'] ?? 0);
                $nameKey = mb_strtolower(trim((string) ($item['nama'] ?? '-')));
                $key = $peralatanId > 0 ? 'id:' . $peralatanId : 'name:' . $nameKey;

                if (! isset($carry[$key])) {
                    $carry[$key] = [
                        'peralatan_id' => $peralatanId,
                        'nama' => trim((string) ($item['nama'] ?? '-')) ?: '-',
                        'jumlah' => 0,
                        'stok' => (float) ($item['stok'] ?? 0),
                        'stok_minimum' => (float) ($item['stok_minimum'] ?? 0),
                    ];
                }

                $carry[$key]['jumlah'] += (int) ($item['jumlah'] ?? 0);
                $carry[$key]['stok'] = (float) ($item['stok'] ?? $carry[$key]['stok']);
                $carry[$key]['stok_minimum'] = (float) ($item['stok_minimum'] ?? $carry[$key]['stok_minimum']);

                return $carry;
            }, []);
    }

    private function buildManualServiceOrderSummary(
        array $items,
        ServicePackagePricingService $servicePackagePricingService,
        array $serviceUkuranOptions
    ): array {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'service_service_items' => 'Tambahkan minimal satu item service terlebih dahulu.',
            ]);
        }

        $sizeMap = collect($serviceUkuranOptions)
            ->mapWithKeys(function ($size) {
                $label = trim((string) $size);

                return [mb_strtolower($label) => $label];
            })
            ->all();
        $requestedPaketIds = collect($items)->pluck('service_paket_id')->filter()->unique()->values();
        $paketMap = app(ServiceMasterSyncService::class)
            ->visibleServicePakets(['peralatans', 'jenisRefill'])
            ->whereIn('id', $requestedPaketIds->all())
            ->keyBy('id');

        $missingPaketIds = $requestedPaketIds->filter(fn ($id) => ! $paketMap->has($id));
        if ($missingPaketIds->isNotEmpty()) {
            $fallbackPakets = ServicePaket::query()
                ->with(['peralatans', 'jenisRefill'])
                ->whereIn('id', $missingPaketIds->all())
                ->get()
                ->keyBy('id');

            $paketMap = $paketMap->merge($fallbackPakets);
        }

        $lineItems = [];
        $peralatanItems = [];
        $totalUnits = 0;
        $totalPrice = 0.0;

        foreach (array_values($items) as $index => $item) {
            $paketId = (int) ($item['service_paket_id'] ?? 0);
            $ukuranKey = mb_strtolower(trim((string) ($item['ukuran_apar'] ?? '')));
            $jumlahUnit = max(1, (int) ($item['jumlah_unit'] ?? 1));

            $jenisApar = trim((string) ($item['jenis_apar'] ?? ''));
            $paket = $paketMap->get($paketId);
            if (! $paket) {
                throw ValidationException::withMessages([
                    'service_service_items' => 'Paket service pada salah satu item belum valid.',
                ]);
            }

            if ($ukuranKey === '' || ! isset($sizeMap[$ukuranKey])) {
                throw ValidationException::withMessages([
                    'service_service_items' => 'Ukuran APAR pada salah satu item service belum valid.',
                ]);
            }

            if ($jenisApar === '') {
                throw ValidationException::withMessages([
                    'service_service_items' => 'Jenis APAR pada salah satu item service belum valid.',
                ]);
            }

            $ukuran = $sizeMap[$ukuranKey];
            $packageSummary = $servicePackagePricingService->summarizePackageOrder($paket, [[
                'label' => 'APAR ' . $jenisApar . ' ' . $ukuran,
                'media' => $jenisApar,
                'ukuran' => $ukuran,
                'qty' => $jumlahUnit,
            ]]);
            $lineItem = $packageSummary['line_items'][0] ?? null;
            $itemTotalPrice = (float) ($lineItem['total'] ?? $packageSummary['total_price'] ?? 0);
            $unitPrice = (float) ($lineItem['unit_price'] ?? 0);

            if ($itemTotalPrice <= 0 || $unitPrice <= 0) {
                throw ValidationException::withMessages([
                    'service_service_items' => 'Harga standar service untuk salah satu item belum tersedia.',
                ]);
            }

            $lineItems[] = [
                'index' => $index + 1,
                'paket' => $paket,
                'paket_id' => (int) $paket->id,
                'label' => (string) $paket->nama,
                'display_label' => $paket->nama . ' | ' . $ukuran,
                'ukuran' => $ukuran,
                'qty' => $jumlahUnit,
                'unit_price' => $unitPrice,
                'total' => (float) round($itemTotalPrice, 0),
                'peralatan_items' => $packageSummary['peralatan_items'] ?? [],
            ];

            $peralatanItems = array_merge($peralatanItems, $packageSummary['peralatan_items'] ?? []);
            $totalUnits += $jumlahUnit;
            $totalPrice += $itemTotalPrice;
        }

        $aggregatedPeralatan = collect($this->aggregatePeralatanSummary($peralatanItems))
            ->values();
        $uniquePaketIds = collect($lineItems)->pluck('paket_id')->unique()->values();

        return [
            'line_items' => $lineItems,
            'peralatan_items' => $aggregatedPeralatan->all(),
            'single_paket_id' => $uniquePaketIds->count() === 1 ? (int) $uniquePaketIds->first() : null,
            'headline' => collect($lineItems)->pluck('label')->unique()->implode(', '),
            'display_summary' => collect($lineItems)->pluck('label')->unique()->implode(', '),
            'ukuran_summary' => collect($lineItems)->pluck('ukuran')->unique()->implode(', '),
            'total_units' => $totalUnits,
            'estimasi_biaya' => (float) round($totalPrice, 0),
        ];
    }

    private function buildManualRefillOrderNote(array $summary, string $metode, string $customerNote): string
    {
        $lines = ['Rincian Refill Manual'];

        foreach (array_values($summary['line_items'] ?? []) as $index => $item) {
            $lines[] = ($index + 1) . '. '
                . trim((string) ($item['label'] ?? 'Refill'))
                . ' | '
                . trim((string) ($item['ukuran'] ?? '-'))
                . ' | '
                . max(1, (int) ($item['qty'] ?? 1))
                . ' unit - Rp'
                . number_format((float) ($item['total'] ?? 0), 0, ',', '.');
        }

        $lines[] = 'Total Refill: Rp' . number_format((float) ($summary['estimasi_biaya'] ?? 0), 0, ',', '.');
        $lines[] = 'Total Kebutuhan Refill: ' . $this->formatKgNumber((float) ($summary['total_kg'] ?? 0)) . ' ' . ($summary['stock_unit'] ?? 'Kg');
        $lines[] = 'Metode Penanganan: ' . ($metode === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput');
        $lines[] = 'Catatan Pelanggan: ' . (trim($customerNote) !== '' ? trim($customerNote) : '-');

        return implode("\n", $lines);
    }

    private function buildManualServiceOrderNote(array $summary, string $metode, string $customerNote): string
    {
        $lines = ['Rincian Service Manual'];

        foreach (array_values($summary['line_items'] ?? []) as $index => $item) {
            $lines[] = ($index + 1) . '. '
                . trim((string) ($item['label'] ?? 'Service'))
                . ' | '
                . trim((string) ($item['ukuran'] ?? '-'))
                . ' | '
                . max(1, (int) ($item['qty'] ?? 1))
                . ' unit - Rp'
                . number_format((float) ($item['total'] ?? 0), 0, ',', '.');
        }

        $peralatanItems = $summary['peralatan_items'] ?? [];
        if (! empty($peralatanItems)) {
            $lines[] = 'Peralatan Paket:';
            foreach ($peralatanItems as $peralatanItem) {
                $lines[] = '- '
                    . trim((string) ($peralatanItem['nama'] ?? '-'))
                    . ' x'
                    . (int) ($peralatanItem['jumlah'] ?? 0);
            }
        }

        $lines[] = 'Total Service: Rp' . number_format((float) ($summary['estimasi_biaya'] ?? 0), 0, ',', '.');
        $lines[] = 'Metode Penanganan: ' . ($metode === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput');
        $lines[] = 'Catatan Pelanggan: ' . (trim($customerNote) !== '' ? trim($customerNote) : '-');

        return implode("\n", $lines);
    }

    private function selectedRegisteredUnitsNote(
        Collection $unitApars,
        string $purchaseLabel,
        string $totalLabel,
        float $total,
        string $metode,
        string $customerNote,
        array $lineAmounts = [],
        array $unitDetails = []
    ): string {
        $lines = [];

        foreach ($unitApars->values() as $index => $unitApar) {
            $amount = $lineAmounts[$unitApar->id] ?? null;
            $detail = $unitDetails[$unitApar->id] ?? [];
            $refillLabel = trim((string) ($detail['refill_label'] ?? ''));
            $usageKg = (float) ($detail['usage_kg'] ?? 0);
            $amountLabel = is_numeric($amount)
                ? ' - Rp' . number_format((float) $amount, 0, ',', '.')
                : '';

            $lines[] = ($index + 1) . '. '
                . $this->registeredUnitAparLabel($unitApar)
                . ' - Masa berlaku: '
                . $this->registeredUnitAparExpiryLabel($unitApar)
                . ($refillLabel !== '' ? ' - Refill: ' . $refillLabel : '')
                . ($usageKg > 0 ? ' - Kebutuhan: ' . $this->formatKgNumber($usageKg) . ' Kg' : '')
                . $amountLabel;
        }

        $lines[] = "{$totalLabel}: Rp" . number_format($total, 0, ',', '.');
        $lines[] = 'Metode Penanganan: ' . ($metode === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput');

        $customerNote = trim($customerNote);
        $lines[] = 'Catatan Pelanggan: ' . ($customerNote !== '' ? $customerNote : '-');

        return implode("\n", $lines);
    }

    public function orderCreate(
        Request $request,
        OrderPricingService $orderPricingService,
        ServicePackagePricingService $servicePackagePricingService
    )
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && ($user->isAdmin() || $user->isTeknisi())) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Pemesanan pelanggan hanya tersedia untuk akun pelanggan.');
        }

        $requestedProductId = max(0, (int) request()->integer('produk'));
        $requestedQty = max(1, (int) request()->integer('qty', 1));

        if (!Auth::check() && $requestedProductId > 0) {
            return redirect()->guest(route('login'));
        }

        if ($this->pendingPaymentOrderForAuthenticatedCustomer()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Selesaikan pembayaran sebelumnya sebelum membuat pesanan baru.');
        }

        $produks = Produk::with(['jenisApar', 'stokBatches'])
            ->whereHas('stokBatches', function ($q) {
                $q->where('sisa_qty', '>', 0)
                  ->where('tgl_expired', '>=', now()->toDateString());
            })
            ->get()
            ->filter(fn (Produk $produk) => $produk->hasResolvedImage())
            ->values();

        $serviceMasterSyncService = app(ServiceMasterSyncService::class);
        $jenisApars = JenisApar::orderBy('nama')->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $servicePakets = $serviceMasterSyncService->visibleServicePakets(['peralatans', 'jenisRefill']);
        $serviceMediaOptions = $servicePackagePricingService->availableMediaOptions();
        $servicePackageCatalog = $servicePackagePricingService->packageCatalog($servicePakets, $serviceMediaOptions);
        $serviceUkuranOptions = $this->serviceUkuranOptions();
        $customerProfile = $this->authenticatedCustomerProfile();
        $useAuthenticatedCustomer = !is_null($customerProfile);
        $selectedOrderProduct = null;
        $prefilledOrderItems = collect();
        $prefillFromProduct = false;
        $prefillServiceOrder = null;
        $cartItems = collect();
        $cartTotal = 0;
        $cartItemCount = 0;
        $canUseCartCheckout = Auth::check() && $useAuthenticatedCustomer;

        if ($requestedProductId > 0) {
            $selectedOrderProduct = Produk::with(['jenisApar', 'stokBatches'])->find($requestedProductId);

            if ($selectedOrderProduct) {
                $stokTersedia = (int) ($selectedOrderProduct->stok_tersedia ?? 0);
                if ($stokTersedia <= 0) {
                    return redirect()
                        ->route('produk.show', $selectedOrderProduct)
                        ->with('error', 'Stok produk tidak tersedia untuk dipesan.');
                }

                $prefillQty = min($requestedQty, $stokTersedia);
                $prefilledOrderItems = collect([[
                    'produk_id' => (int) $selectedOrderProduct->id,
                    'jumlah' => $prefillQty,
                    'harga' => (float) $selectedOrderProduct->harga,
                    'nama' => (string) $selectedOrderProduct->nama,
                    'jenis' => (string) ($selectedOrderProduct->jenisApar?->nama ?? 'APAR'),
                    'kapasitas' => (string) ($selectedOrderProduct->kapasitas ?? '-'),
                    'merek' => (string) ($selectedOrderProduct->merek ?? 'FIREFIX'),
                    'gambar' => (string) ($selectedOrderProduct->gambar ?? ''),
                    'gambar_url' => (string) ($selectedOrderProduct->resolved_image_url ?? ''),
                ]]);
                $prefillFromProduct = true;
                $canUseCartCheckout = false;
            }
        }

        $orderSummary = $orderPricingService->summarizeProductItems([]);

        if ($canUseCartCheckout) {
            $cartItems = $this->authenticatedCartItems();
            $orderSummary = $orderPricingService->summarizeCart($cartItems);
            $cartTotal = (float) $orderSummary['totalPembayaran'];
        } elseif ($prefillFromProduct && count($prefilledOrderItems) > 0) {
            $orderSummary = $orderPricingService->summarizeProductItems($prefilledOrderItems);
            $cartTotal = (float) $orderSummary['totalPembayaran'];
        }

        $subtotalProduk = (float) $orderSummary['subtotalProduk'];
        $totalUnit = (int) $orderSummary['totalUnit'];
        $diskonPersen = (int) $orderSummary['diskonPersen'];
        $nominalDiskon = (float) $orderSummary['nominalDiskon'];
        $ongkir = (float) $orderSummary['ongkir'];
        $totalPembayaran = (float) $orderSummary['totalPembayaran'];
        $cartItemCount = $totalUnit;
        $subtotal = $subtotalProduk;
        $cartTotal = $totalPembayaran;

        $registeredUnitApars = collect();
        $authenticatedCustomer = $this->authenticatedCustomer();
        if ($authenticatedCustomer) {
            $refillLocks = RegisteredRefillUnitSupport::activeRefillLocks($authenticatedCustomer);
            $registeredUnitApars = $this->registeredUnitAparQuery($authenticatedCustomer)
                ->orderByDesc('tgl_beli')
                ->orderBy('no_seri')
                ->get()
                ->map(function (UnitApar $unitApar) use ($refillLocks) {
                    $statusMeta = RegisteredRefillUnitSupport::statusMeta($unitApar);
                    $unitLock = $refillLocks[$unitApar->id] ?? null;

                    $unitApar->setAttribute('refill_status_label', $statusMeta['status_label'] ?? 'Aman');
                    $unitApar->setAttribute('needs_refill', (bool) ($statusMeta['needs_refill'] ?? false));
                    $unitApar->setAttribute('is_refill_locked', ! is_null($unitLock));
                    $unitApar->setAttribute('refill_lock_message', (string) ($unitLock['message'] ?? ''));

                    return $unitApar;
                })
                ->values();

            $prefilledRegisteredRefill = (array) session('prefill_registered_refill', []);
            $prefilledUnitIds = collect((array) ($prefilledRegisteredRefill['selected_unit_ids'] ?? []))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            if ($prefilledUnitIds->isNotEmpty()) {
                $selectedUnits = $registeredUnitApars
                    ->whereIn('id', $prefilledUnitIds->all())
                    ->sortBy(fn (UnitApar $unitApar) => $prefilledUnitIds->search((int) $unitApar->id))
                    ->values();

                if ($selectedUnits->count() !== $prefilledUnitIds->count()) {
                    return redirect()
                        ->route('riwayat-apar')
                        ->with('error', 'Ada Unit APAR yang tidak valid atau sudah tidak tersedia untuk diajukan refill.');
                }

                $blockedUnit = $selectedUnits->first(fn (UnitApar $unitApar) => (bool) $unitApar->getAttribute('is_refill_locked'));
                if ($blockedUnit) {
                    return redirect()
                        ->route('riwayat-apar')
                        ->with('error', (($blockedUnit->getAttribute('refill_lock_message') ?: 'Unit ini sedang dalam proses refill.') . ' Nomor Unit: ' . ($blockedUnit->no_seri ?: ('UNIT-' . $blockedUnit->id))));
                }

                $safeUnit = $selectedUnits->first(fn (UnitApar $unitApar) => ! (bool) $unitApar->getAttribute('needs_refill'));
                if ($safeUnit) {
                    return redirect()
                        ->route('riwayat-apar')
                        ->with('error', 'Unit ' . ($safeUnit->no_seri ?: ('UNIT-' . $safeUnit->id)) . ' masih berstatus aman. Gunakan menu layanan APAR biasa jika ingin refill manual.');
                }

                try {
                    $prefillSummary = $this->summarizeRegisteredRefillUnits($selectedUnits);
                } catch (ValidationException $exception) {
                    return redirect()
                        ->route('riwayat-apar')
                        ->with('error', collect($exception->errors())->flatten()->first() ?: 'Data refill otomatis untuk unit yang dipilih belum lengkap.');
                }

                $prefillLines = $selectedUnits->map(function (UnitApar $unitApar) use ($prefillSummary) {
                    $detail = $prefillSummary['unit_details'][$unitApar->id] ?? [];
                    $produkNama = (string) ($unitApar->produk?->nama ?: 'APAR');
                    $jenisApar = (string) ($unitApar->produk?->jenisApar?->nama ?: $unitApar->bahan ?: '-');
                    $ukuran = (string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: '-');
                    $unitPrice = (float) ($detail['unit_price'] ?? 0);

                    return [
                        'id' => (int) $unitApar->id,
                        'nomor_unit' => (string) ($unitApar->no_seri ?: ('UNIT-' . $unitApar->id)),
                        'nama_apar' => $produkNama,
                        'jenis_apar' => $jenisApar,
                        'ukuran' => $ukuran,
                        'jenis_refill' => (string) ($detail['refill_label'] ?? '-'),
                        'harga_per_unit' => $unitPrice,
                        'subtotal' => $unitPrice,
                    ];
                })->values();

                $uniquePurchaseDates = $selectedUnits
                    ->map(fn (UnitApar $unitApar) => $unitApar->tgl_beli?->translatedFormat('d F Y'))
                    ->filter()
                    ->unique()
                    ->values();

                $prefillServiceOrder = [
                    'group_key' => RegisteredRefillUnitSupport::PREFILL_GROUP_KEY,
                    'group_label' => 'Pilihan dari Riwayat APAR - ' . $selectedUnits->count() . ' Unit',
                    'selected_unit_ids' => $prefilledUnitIds->all(),
                    'selected_units' => $prefillLines->all(),
                    'total_unit' => $selectedUnits->count(),
                    'total_price' => (float) ($prefillSummary['estimasi_biaya'] ?? 0),
                    'total_kg' => (float) ($prefillSummary['total_kg'] ?? 0),
                    'purchase_label' => $uniquePurchaseDates->count() === 1
                        ? (string) $uniquePurchaseDates->first()
                        : $uniquePurchaseDates->count() . ' batch pembelian',
                    'is_mixed_refill' => (bool) ($prefillSummary['is_mixed'] ?? false),
                ];
            }
        }

        return view('public.order.create', compact(
            'produks',
            'jenisApars',
            'jenisRefills',
            'servicePakets',
            'serviceMediaOptions',
            'servicePackageCatalog',
            'serviceUkuranOptions',
            'customerProfile',
            'useAuthenticatedCustomer',
            'cartItems',
            'orderSummary',
            'subtotalProduk',
            'totalUnit',
            'subtotal',
            'diskonPersen',
            'nominalDiskon',
            'ongkir',
            'totalPembayaran',
            'cartTotal',
            'cartItemCount',
            'canUseCartCheckout',
            'prefilledOrderItems',
            'prefillFromProduct',
            'prefillServiceOrder',
            'selectedOrderProduct',
            'registeredUnitApars',
        ));
    }

    public function orderStore(
        Request $request,
        InventoryService $inventoryService,
        OrderPricingService $orderPricingService,
        ServicePackagePricingService $servicePackagePricingService
    )
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && ($user->isAdmin() || $user->isTeknisi())) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Pemesanan pelanggan hanya tersedia untuk akun pelanggan.');
        }

        if ($request->input('tipe_layanan') === 'beli' && !Auth::check()) {
            return redirect()->guest(route('login'));
        }

        if ($this->pendingPaymentOrderForAuthenticatedCustomer()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Selesaikan pembayaran sebelumnya sebelum membuat pesanan baru.');
        }

        app(ServiceMasterSyncService::class)->sync();
        $this->applyAuthenticatedCustomerProfileToRequest($request);
        $isCartCheckout = $request->input('tipe_layanan') === 'beli'
            && $request->boolean('use_cart_checkout')
            && !is_null($this->authenticatedCustomerProfile());
        $cartItems = $isCartCheckout ? $this->authenticatedCartItems() : collect();
        $productItems = $isCartCheckout
            ? $this->buildCartOrderItems($cartItems)
            : (array) $request->input('items', []);
        $serviceUkuranOptions = $this->serviceUkuranOptions();
        $serviceMediaOptions = $servicePackagePricingService->availableMediaOptions();
        $serviceMediaSizeMap = collect($serviceMediaOptions)
            ->mapWithKeys(function (array $media) {
                return [
                    mb_strtolower((string) ($media['label'] ?? '')) => array_values($media['sizes'] ?? []),
                ];
            })
            ->all();

        $rules = [
            'nama'               => 'required|string|max:255',
            'no_wa'              => 'required|string|max:20',
            'alamat_maps'        => 'required|string|max:255',
            'alamat_detail'      => 'required|string|max:1000',
            'alamat_provinsi'    => 'nullable|string|max:255',
            'alamat_kota'        => 'nullable|string|max:255',
            'alamat_kecamatan'   => 'nullable|string|max:255',
            'alamat_kode_pos'    => 'nullable|string|max:50',
            'rajaongkir_destination_id' => 'nullable|string|max:50',
            'rajaongkir_destination_label' => 'nullable|string|max:255',
            'alamat_lat'         => 'nullable|numeric|between:-90,90',
            'alamat_lng'         => 'nullable|numeric|between:-180,180',
            'tipe_layanan'       => 'required|in:beli,service',
            'metode_pengiriman'  => 'nullable|required_if:tipe_layanan,beli|in:pickup,ambil_sendiri,diantar,diantar_internal',
            'bank_tujuan'        => 'nullable|required_if:tipe_layanan,beli|in:bca,mandiri,bri',
            'submit_source'      => 'nullable|in:normal,ask_wa,special_price_request',
            'harga_pengajuan'    => 'nullable',
            'catatan_pelanggan'  => 'nullable|string|max:1000',
            'service_jenis_layanan' => 'nullable|required_if:tipe_layanan,service|in:service,refill',
            'service_jenis_apar' => 'nullable|string|max:120',
            'service_jumlah_unit' => 'nullable|integer|min:1|max:1000',
            'service_keluhan' => 'nullable|string|max:2000',
            'service_foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'service_metode_penanganan' => 'nullable|required_if:tipe_layanan,service|in:dijemput,antar sendiri',
            'service_jenis_refill_id' => 'nullable|exists:jenis_refills,id',
            'service_paket_id' => 'nullable|exists:service_pakets,id',
            'service_ukuran_apar' => 'nullable|string|max:120',
            'service_refill_items' => 'nullable|array',
            'service_refill_items.*.jenis_refill_id' => 'nullable|integer|exists:jenis_refills,id',
            'service_refill_items.*.ukuran_apar' => 'nullable|string|max:120',
            'service_refill_items.*.jumlah_unit' => 'nullable|integer|min:1|max:1000',
            'service_service_items' => 'nullable|array',
            'service_service_items.*.jenis_apar' => 'nullable|string|max:120',
            'service_service_items.*.service_paket_id' => 'nullable|integer|exists:service_pakets,id',
            'service_service_items.*.ukuran_apar' => 'nullable|string|max:120',
            'service_service_items.*.jumlah_unit' => 'nullable|integer|min:1|max:1000',
            'service_unit_status' => 'nullable|in:terdaftar,belum_terdaftar',
            'service_unit_apar_id' => 'nullable|integer|exists:unit_apars,id',
            'service_purchase_group' => 'nullable|string|max:120',
            'service_unit_apar_ids' => 'nullable|array',
            'service_unit_apar_ids.*' => 'integer|exists:unit_apars,id',
            'shipping_courier' => 'nullable|string|max:80',
            'shipping_service' => 'nullable|string|max:120',
            'shipping_etd' => 'nullable|string|max:120',
            'shipping_weight' => 'nullable|integer|min:0',
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
            if ($user && !$user->isAdmin() && !$user->isTeknisi() && empty($pelanggan->user_id)) {
                $pelanggan->user_id = $user->id;
            }
            // Hanya perbarui nama/alamat jika pelanggan baru dibuat,
            // atau jika nama di DB masih kosong — JANGAN timpa data pelanggan lama!
            if ($pelanggan->wasRecentlyCreated || empty($pelanggan->nama)) {
                $pelanggan->nama   = $request->nama;
                $pelanggan->alamat = $alamatGabungan;
            }
            $pelanggan->save();
            // Sync data alamat lengkap ke tabel pelanggan (isi apa yang available)
            $this->syncPelangganAddress($pelanggan, $request, $alamatGabungan);

            $pesanan = new Pesanan();
                $pesanan->pelanggan_id = $pelanggan->id;
                $pesanan->user_id = $user?->id;
                $pesanan->tanggal = now();

            $redirectToPayment = false;

            if ($request->tipe_layanan === 'beli') {
                $isSpecialPriceRequest = $request->input('submit_source') === 'special_price_request';
                $productPriceMap = Produk::query()
                    ->whereIn('id', collect($productItems)->pluck('produk_id')->filter()->map(fn ($id) => (int) $id)->unique()->all())
                    ->get(['id', 'harga'])
                    ->keyBy('id');
                $productItems = collect($productItems)
                    ->map(function (array $item) use ($productPriceMap) {
                        $produkId = (int) ($item['produk_id'] ?? 0);
                        $qty = max(0, (int) ($item['jumlah'] ?? 0));
                        $harga = (float) ($item['harga'] ?? ($productPriceMap->get($produkId)?->harga ?? 0));

                        return [
                            'produk_id' => $produkId,
                            'jumlah' => $qty,
                            'harga' => $harga,
                            'subtotal' => $harga * $qty,
                        ];
                    })
                    ->filter(fn (array $item) => $item['produk_id'] > 0 && $item['jumlah'] > 0)
                    ->values()
                    ->all();

                $deliveryMeta = $this->buildDeliveryMeta($request, $productItems, $pelanggan);
                $draftPricing = $orderPricingService->summarizeProductItems($productItems, (float) $deliveryMeta['ongkir']);
                $hargaPengajuan = $this->normalizeMoneyInput($request->input('harga_pengajuan'));
                $catatanPelanggan = trim((string) $request->input('catatan_pelanggan'));

                if ($isSpecialPriceRequest) {
                    if ((float) ($draftPricing['subtotalProduk'] ?? 0) <= 5000000) {
                        throw ValidationException::withMessages([
                            'harga_pengajuan' => 'Pengajuan Harga Pembelian hanya tersedia untuk subtotal produk di atas Rp 5.000.000.',
                        ]);
                    }

                    if (is_null($hargaPengajuan) || $hargaPengajuan <= 0) {
                        throw ValidationException::withMessages([
                            'harga_pengajuan' => 'Harga Pengajuan wajib diisi dengan angka yang valid.',
                        ]);
                    }

                    if ($hargaPengajuan > (float) ($draftPricing['subtotalProduk'] ?? 0)) {
                        throw ValidationException::withMessages([
                            'harga_pengajuan' => 'Harga Pengajuan tidak boleh lebih besar dari subtotal harga dasar.',
                        ]);
                    }
                }

                $pesanan->tipe = 'produk';
                $pesanan->sumber_pesanan = 'website';
                $pesanan->metode_pengiriman = (string) $deliveryMeta['metode_pengiriman'];
                $pesanan->bank = (string) $request->input('bank_tujuan', '');
                $pesanan->ongkir = (float) $deliveryMeta['ongkir'];
                $pesanan->shipping_courier = $deliveryMeta['shipping_courier'];
                $pesanan->shipping_service = $deliveryMeta['shipping_service'];
                $pesanan->shipping_etd = $deliveryMeta['shipping_etd'];
                $pesanan->shipping_destination_id = $deliveryMeta['shipping_destination_id'];
                $pesanan->shipping_destination_label = $deliveryMeta['shipping_destination_label'];
                $pesanan->shipping_weight = $deliveryMeta['shipping_weight'];
                $pesanan->shipping_distance_km = $deliveryMeta['distance_km'];
                $pesanan->alamat_maps = $deliveryMeta['alamat_maps'];
                $pesanan->alamat_detail = $deliveryMeta['alamat_detail'];
                $pesanan->alamat_lat = $deliveryMeta['alamat_lat'];
                $pesanan->alamat_lng = $deliveryMeta['alamat_lng'];
                $bankTujuan = strtoupper((string) $request->input('bank_tujuan', '-'));

                $pesanan->total = 0;
                $pesanan->status = $isSpecialPriceRequest
                    ? Pesanan::STATUS_MENUNGGU_PERSETUJUAN_HARGA
                    : Pesanan::STATUS_PENDING;
                $pesanan->tipe_harga = 'normal';
                $pesanan->fill(Pesanan::purchasePriceAttributes([
                    'status' => $isSpecialPriceRequest ? Pesanan::PRICE_REQUEST_PENDING : null,
                    'requested_price' => $isSpecialPriceRequest ? $hargaPengajuan : null,
                    'final_price' => null,
                    'customer_note' => $isSpecialPriceRequest && $catatanPelanggan !== '' ? $catatanPelanggan : null,
                    'admin_note' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'rejected_by' => null,
                    'rejected_at' => null,
                    'normal_subtotal' => (float) ($draftPricing['subtotalProduk'] ?? 0),
                    'discounted_total' => (float) ($draftPricing['totalSetelahPromo'] ?? 0),
                    'initial_total' => (float) ($draftPricing['totalPembayaran'] ?? 0),
                    'used' => false,
                ]));
                
                if ((int) $draftPricing['diskonPersen'] > 0) {
                    $pesanan->keterangan = "Pembelian Produk [Promo Diskon {$draftPricing['diskonPersen']}%] [Pengiriman: " . $this->shippingMethodLabel((string) $pesanan->metode_pengiriman) . "] [Bank Tujuan: {$bankTujuan}]";
                } else {
                    $pesanan->keterangan = "Pembelian Produk [Pengiriman: " . $this->shippingMethodLabel((string) $pesanan->metode_pengiriman) . "] [Bank Tujuan: {$bankTujuan}]";
                }

                $pesanan->save();

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
                        'merek' => $produk->merek ?? 'FIREFIX',
                        'kapasitas' => $produk->kapasitas,
                        'jumlah' => $item['jumlah'],
                        'harga' => $hargaSatuan,
                        'subtotal' => $subtotal,
                    ]);
                }

                if ($isCartCheckout && Auth::check()) {
                    SessionCart::clear();
                }

                $pricingSummary = $orderPricingService->summarizePesanan($pesanan->fresh('details'));

                if ((int) $pricingSummary['diskonPersen'] > 0) {
                    $pesanan->keterangan = str_replace(
                        "[Promo Diskon {$pricingSummary['diskonPersen']}%]",
                        "[Promo Diskon {$pricingSummary['diskonPersen']}%: -Rp " . number_format((float) $pricingSummary['nominalDiskon'], 0, ',', '.') . "]",
                        $pesanan->keterangan
                    );
                }

                $pesanan->update(array_merge([
                    'total' => (float) $pricingSummary['totalPembayaran'],
                    'total_harga' => (float) $pricingSummary['totalPembayaran'],
                    'keterangan' => $pesanan->keterangan,
                ], Pesanan::purchasePriceAttributes([
                    'status' => $isSpecialPriceRequest ? Pesanan::PRICE_REQUEST_PENDING : null,
                    'requested_price' => $isSpecialPriceRequest ? $hargaPengajuan : null,
                    'final_price' => null,
                    'customer_note' => $isSpecialPriceRequest && $catatanPelanggan !== '' ? $catatanPelanggan : null,
                    'admin_note' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'rejected_by' => null,
                    'rejected_at' => null,
                    'normal_subtotal' => (float) ($pricingSummary['subtotalProduk'] ?? 0),
                    'discounted_total' => (float) ($pricingSummary['totalSetelahPromo'] ?? 0),
                    'initial_total' => (float) ($pricingSummary['totalPembayaran'] ?? 0),
                    'used' => false,
                ])));

                if ($isSpecialPriceRequest) {
                    $successMessage = 'Pengajuan Harga Pembelian berhasil dikirim. Silakan tunggu persetujuan admin sebelum melanjutkan pembayaran.';
                } else {
                    $redirectToPayment = true;
                }

            } else {
                $serviceJenisLayanan = strtolower(trim((string) $request->input('service_jenis_layanan', 'service')));
                if (!in_array($serviceJenisLayanan, ['service', 'refill'], true)) {
                    $serviceJenisLayanan = 'service';
                }
                $serviceUnitStatus = strtolower(trim((string) $request->input('service_unit_status', 'belum_terdaftar')));
                if (!in_array($serviceUnitStatus, ['terdaftar', 'belum_terdaftar'], true)) {
                    $serviceUnitStatus = 'belum_terdaftar';
                }

                $selectedUnitApars = collect();
                $servicePurchaseGroup = trim((string) $request->input('service_purchase_group', ''));
                $serviceUkuranApar = trim((string) $request->input('service_ukuran_apar', ''));
                $manualServiceJenisApar = trim((string) $request->input('service_jenis_apar', ''));
                $rawServiceJumlahUnit = (int) $request->input('service_jumlah_unit', 0);
                $serviceJumlahUnit = max(1, $rawServiceJumlahUnit);
                $originalServiceKeluhan = trim((string) $request->input('service_keluhan', (string) $request->input('keterangan_service', '-')));
                $serviceKeluhan = $originalServiceKeluhan;
                $requestedShippingMethod = trim((string) $request->input('metode_pengiriman', ''));
                $serviceMetode = strtolower(trim((string) $request->input('service_metode_penanganan', '')));
                if ($requestedShippingMethod !== '') {
                    $serviceMetode = $this->normalizeShippingMethod($requestedShippingMethod) === 'diantar'
                        ? 'dijemput'
                        : 'antar sendiri';
                }
                if (!in_array($serviceMetode, ['dijemput', 'antar sendiri'], true)) {
                    $serviceMetode = 'dijemput';
                }
                $serviceFotoPath = $request->hasFile('service_foto')
                    ? $request->file('service_foto')->store('service-request', 'public')
                    : null;
                $manualRefillItems = $this->normalizeManualRefillItems((array) $request->input('service_refill_items', []));
                $manualServiceItems = $this->normalizeManualServiceItems((array) $request->input('service_service_items', []));
                $usesManualMultiItemFlow = ($serviceJenisLayanan === 'refill' && ! empty($manualRefillItems))
                    || ($serviceJenisLayanan === 'service' && ! empty($manualServiceItems));

                if ($serviceUnitStatus === 'terdaftar' && ! $usesManualMultiItemFlow) {
                    $authenticatedCustomer = $this->authenticatedCustomer();
                    if (!$authenticatedCustomer || (int) $authenticatedCustomer->id !== (int) $pelanggan->id) {
                        throw ValidationException::withMessages([
                            'service_unit_apar_id' => 'APAR Terdaftar hanya bisa dipilih oleh pelanggan yang sedang login.',
                        ]);
                    }

                    if ($servicePurchaseGroup === '') {
                        throw ValidationException::withMessages([
                            'service_purchase_group' => 'Pilih Riwayat Pembelian APAR terlebih dahulu.',
                        ]);
                    }

                    $selectedUnitAparIds = collect((array) $request->input('service_unit_apar_ids', []))
                        ->map(fn ($id) => (int) $id)
                        ->filter(fn ($id) => $id > 0)
                        ->unique()
                        ->values();

                    if ($selectedUnitAparIds->isEmpty() && (int) $request->input('service_unit_apar_id') > 0) {
                        $selectedUnitAparIds = collect([(int) $request->input('service_unit_apar_id')]);
                    }

                    if ($selectedUnitAparIds->isEmpty()) {
                        throw ValidationException::withMessages([
                            'service_unit_apar_ids' => 'Minimal satu Unit APAR wajib dicentang.',
                        ]);
                    }

                    $selectedUnitApars = $this->registeredUnitAparQuery($pelanggan)
                        ->whereIn('id', $selectedUnitAparIds->all())
                        ->get()
                        ->sortBy(fn (UnitApar $unitApar) => $selectedUnitAparIds->search((int) $unitApar->id))
                        ->values();

                    if ($selectedUnitApars->count() !== $selectedUnitAparIds->count()) {
                        throw ValidationException::withMessages([
                            'service_unit_apar_ids' => 'Ada Unit APAR yang tidak valid atau bukan milik akun pelanggan ini.',
                        ]);
                    }

                    $activeRefillLocks = RegisteredRefillUnitSupport::activeRefillLocks($pelanggan);
                    $lockedUnit = $selectedUnitApars->first(fn (UnitApar $unitApar) => isset($activeRefillLocks[$unitApar->id]));

                    if ($lockedUnit) {
                        throw ValidationException::withMessages([
                            'service_unit_apar_ids' => ($activeRefillLocks[$lockedUnit->id]['message'] ?? 'Unit ini sedang dalam proses refill.')
                                . ' Nomor Unit: '
                                . ($lockedUnit->no_seri ?: ('UNIT-' . $lockedUnit->id)),
                        ]);
                    }

                    if ($servicePurchaseGroup !== RegisteredRefillUnitSupport::PREFILL_GROUP_KEY) {
                        $mismatchedGroup = $selectedUnitApars
                            ->first(fn (UnitApar $unitApar) => $this->registeredUnitAparPurchaseKey($unitApar) !== $servicePurchaseGroup);

                        if ($mismatchedGroup) {
                            throw ValidationException::withMessages([
                                'service_unit_apar_ids' => 'Unit APAR yang dipilih harus berasal dari riwayat pembelian yang sama.',
                            ]);
                        }
                    }

                    $missingSize = $selectedUnitApars->first(function (UnitApar $unitApar) {
                        return trim((string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: '')) === '';
                    });

                    if ($missingSize) {
                        throw ValidationException::withMessages([
                            'service_unit_apar_ids' => 'Ada Unit APAR terdaftar yang belum memiliki data ukuran. Hubungi admin atau hapus centang unit tersebut.',
                        ]);
                    }

                    $serviceJumlahUnit = $selectedUnitApars->count();
                    $serviceUkuranApar = $selectedUnitApars
                        ->map(fn (UnitApar $unitApar) => trim((string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: '')))
                        ->filter()
                        ->unique(fn ($ukuran) => mb_strtolower($ukuran))
                        ->implode(', ');
                } elseif (! $usesManualMultiItemFlow) {
                    if ($rawServiceJumlahUnit < 1) {
                        throw ValidationException::withMessages([
                            'service_jumlah_unit' => 'Jumlah unit wajib diisi minimal 1.',
                        ]);
                    }

                    if ($serviceJenisLayanan === 'service') {
                        $serviceMediaSizes = $serviceMediaSizeMap[mb_strtolower($manualServiceJenisApar)] ?? [];

                        if ($manualServiceJenisApar === '' || empty($serviceMediaSizes)) {
                            throw ValidationException::withMessages([
                                'service_jenis_apar' => 'Jenis media APAR wajib dipilih dari data yang tersedia.',
                            ]);
                        }

                        if ($serviceUkuranApar === '' || !in_array($serviceUkuranApar, $serviceMediaSizes, true)) {
                            throw ValidationException::withMessages([
                                'service_ukuran_apar' => 'Ukuran APAR wajib dipilih sesuai media APAR yang tersedia di sistem.',
                            ]);
                        }
                    } elseif ($serviceUkuranApar === '' || !in_array($serviceUkuranApar, $serviceUkuranOptions, true)) {
                        throw ValidationException::withMessages([
                            'service_ukuran_apar' => 'Ukuran APAR wajib dipilih dari daftar yang tersedia.',
                        ]);
                    }
                }

                $serviceUkuranKg = null;
                $serviceJenisAparLabel = $selectedUnitApars->isNotEmpty()
                    ? $selectedUnitApars
                        ->map(fn (UnitApar $unitApar) => $this->registeredUnitAparTypeLabel($unitApar))
                        ->filter()
                        ->unique(fn ($jenis) => mb_strtolower($jenis))
                        ->implode(', ')
                    : $manualServiceJenisApar;
                $servicePurchaseLabel = $selectedUnitApars->isNotEmpty()
                    ? $this->registeredUnitAparPurchaseLabel($selectedUnitApars->first())
                    : '';
                if (! $usesManualMultiItemFlow) {
                    $serviceUkuranKg = $selectedUnitApars->isNotEmpty()
                        ? $selectedUnitApars->sum(fn (UnitApar $unitApar) => (float) ($this->extractAparCapacityKg($unitApar->ukuran ?: $unitApar->produk?->kapasitas) ?? 0))
                        : $this->extractAparCapacityKg($serviceUkuranApar);
                }
                $serviceDeliveryMeta = $this->buildServiceDeliveryMeta(
                    request: $request,
                    selectedUnitApars: $selectedUnitApars,
                    serviceUkuranApar: $serviceUkuranApar,
                    unitCount: $serviceJumlahUnit,
                    serviceMetode: $serviceMetode,
                    pelanggan: $pelanggan,
                );
                $selectedBank = strtolower(trim((string) $request->input('bank_tujuan', (string) $request->input('bank', ''))));
                if (!in_array($selectedBank, ['bca', 'mandiri', 'bri'], true)) {
                    $selectedBank = '';
                }
                $serviceOngkir = (float) ($serviceDeliveryMeta['ongkir'] ?? 0);

                $pesanan->tipe = 'service';
                $pesanan->sumber_pesanan = 'website';
                $pesanan->total = 0;
                $pesanan->total_harga = 0;
                $pesanan->tipe_harga = 'normal';
                $pesanan->metode_pengiriman = (string) ($serviceDeliveryMeta['metode_pengiriman'] ?? ($serviceMetode === 'antar sendiri' ? 'pickup' : 'diantar_internal'));
                $pesanan->bank = $selectedBank;
                $pesanan->ongkir = $serviceOngkir;
                $pesanan->shipping_courier = $serviceDeliveryMeta['shipping_courier'];
                $pesanan->shipping_service = $serviceDeliveryMeta['shipping_service'];
                $pesanan->shipping_etd = $serviceDeliveryMeta['shipping_etd'];
                $pesanan->shipping_destination_id = $serviceDeliveryMeta['shipping_destination_id'];
                $pesanan->shipping_destination_label = $serviceDeliveryMeta['shipping_destination_label'];
                $pesanan->shipping_weight = $serviceDeliveryMeta['shipping_weight'];
                $pesanan->shipping_distance_km = $serviceDeliveryMeta['distance_km'];
                $pesanan->alamat_maps = (string) ($serviceDeliveryMeta['alamat_maps'] ?? $request->input('alamat_maps', ''));
                $pesanan->alamat_detail = (string) ($serviceDeliveryMeta['alamat_detail'] ?? $request->input('alamat_detail', ''));
                $pesanan->alamat_lat = $serviceDeliveryMeta['alamat_lat'] ?? $this->sanitizeCoordinate($request->input('alamat_lat'));
                $pesanan->alamat_lng = $serviceDeliveryMeta['alamat_lng'] ?? $this->sanitizeCoordinate($request->input('alamat_lng'));
                $pesanan->service_jenis_layanan = $serviceJenisLayanan;
                $pesanan->service_jenis_apar = $serviceJenisAparLabel;
                $pesanan->service_ukuran_apar = $serviceUkuranApar;
                $pesanan->service_jumlah_unit = $serviceJumlahUnit;
                $pesanan->service_keluhan = $serviceKeluhan !== '' ? $serviceKeluhan : '-';
                $pesanan->service_foto = $serviceFotoPath;
                $pesanan->service_metode_penanganan = $serviceMetode;
                $pesanan->service_admin_catatan = null;
                $pesanan->status = Pesanan::STATUS_PENDING;

                if ($serviceJenisLayanan === 'refill') {
                    if ($usesManualMultiItemFlow) {
                        $manualRefillSummary = $this->buildManualRefillOrderSummary($manualRefillItems, $serviceUkuranOptions);
                        $stockRequirements = collect($manualRefillSummary['stock_requirements'] ?? []);
                        $insufficientRequirement = $stockRequirements->first(function (array $requirement) {
                            /** @var JenisRefill|null $jenisRefill */
                            $jenisRefill = $requirement['jenis_refill'] ?? null;

                            return $jenisRefill && (float) $jenisRefill->stok < (float) ($requirement['qty'] ?? 0);
                        });

                        if ($insufficientRequirement) {
                            /** @var JenisRefill $insufficientJenisRefill */
                            $insufficientJenisRefill = $insufficientRequirement['jenis_refill'];
                            throw ValidationException::withMessages([
                                'service_refill_items' => 'Stok refill ' . $insufficientJenisRefill->nama_label . ' tidak mencukupi.',
                            ]);
                        }

                        $serviceJumlahUnit = (int) ($manualRefillSummary['total_units'] ?? 0);
                        $serviceUkuranApar = (string) ($manualRefillSummary['ukuran_summary'] ?? '');
                        $serviceJenisAparLabel = (string) ($manualRefillSummary['display_summary'] ?? '');
                        $totalKebutuhanKg = (float) ($manualRefillSummary['total_kg'] ?? 0);
                        $estimasiBiaya = (float) ($manualRefillSummary['estimasi_biaya'] ?? 0);
                        $refillUnitLabel = (string) ($manualRefillSummary['stock_unit'] ?? 'Kg');
                        $pesanan->service_jenis_apar = $serviceJenisAparLabel;
                        $pesanan->service_ukuran_apar = $serviceUkuranApar;
                        $pesanan->service_jumlah_unit = $serviceJumlahUnit;
                        $pesanan->service_keluhan = $this->buildManualRefillOrderNote(
                            $manualRefillSummary,
                            $serviceMetode,
                            $originalServiceKeluhan,
                        );

                        $serviceKeluhanForText = str_replace(["\r\n", "\r", "\n"], ' | ', $pesanan->service_keluhan ?: '-');
                        $totalPembayaranService = $estimasiBiaya + $serviceOngkir;
                        $pesanan->service_jenis_refill_id = $manualRefillSummary['single_refill_id'] ?? null;
                        $pesanan->service_paket_id = null;
                        $pesanan->service_total_kg = $totalKebutuhanKg;
                        $pesanan->service_estimasi_biaya = $estimasiBiaya;
                        $pesanan->total = $totalPembayaranService;
                        $pesanan->total_harga = $totalPembayaranService;

                        $refillSummaryLabel = trim((string) ($manualRefillSummary['headline'] ?? ''));
                        $pesanan->keterangan = 'Permintaan REFILL ' . ($refillSummaryLabel !== '' ? $refillSummaryLabel : 'APAR')
                            . " | Item: {$serviceJenisAparLabel}"
                            . " | Jumlah: {$serviceJumlahUnit} unit"
                            . " | Kebutuhan: {$totalKebutuhanKg} {$refillUnitLabel}"
                            . " | Metode: {$serviceMetode}"
                            . ($serviceOngkir > 0 ? ' | Ongkir: Rp' . number_format($serviceOngkir, 0, ',', '.') : '')
                            . ' | Catatan: ' . $serviceKeluhanForText;
                        $pesanan->save();
                        $successMessage = 'Pesanan refill berhasil dibuat dengan total estimasi ' . number_format($totalPembayaranService, 0, ',', '.')
                            . '. Silakan lanjutkan pembayaran untuk mengaktifkan proses pengerjaan.';
                    } else {
                    if (!$serviceUkuranKg || $serviceUkuranKg <= 0) {
                        throw ValidationException::withMessages([
                            'service_ukuran_apar' => 'Ukuran APAR belum bisa dihitung ke satuan Kg.',
                        ]);
                    }

                    if ($selectedUnitApars->isNotEmpty()) {
                        $registeredRefillSummary = $this->summarizeRegisteredRefillUnits($selectedUnitApars);
                        $registeredLineAmounts = $registeredRefillSummary['line_amounts'];
                        $registeredUnitDetails = $registeredRefillSummary['unit_details'];
                        $totalKebutuhanKg = (float) $registeredRefillSummary['total_kg'];
                        $estimasiBiaya = (float) $registeredRefillSummary['estimasi_biaya'];
                        $stockRequirements = collect($registeredRefillSummary['stock_requirements'] ?? []);
                    } else {
                        $jenisRefill = JenisRefill::find($request->input('service_jenis_refill_id'));

                        if (!$jenisRefill) {
                            throw ValidationException::withMessages([
                                'service_jenis_refill_id' => 'Jenis refill wajib dipilih.',
                            ]);
                        }

                        $hargaStandar = $this->resolveRefillPriceForUkuran($jenisRefill, $serviceUkuranApar);
                        if (is_null($hargaStandar) || $hargaStandar <= 0) {
                            throw ValidationException::withMessages([
                                'service_jenis_refill_id' => 'Harga standar jenis refil untuk ukuran APAR tersebut belum tersedia.',
                            ]);
                        }

                        $totalKebutuhanKg = round($serviceUkuranKg * $serviceJumlahUnit, 2);
                        $estimasiBiaya = (float) round($hargaStandar * $serviceJumlahUnit, 0);
                        $registeredLineAmounts = [];
                        $registeredUnitDetails = [];
                        $stockRequirements = collect([[
                            'jenis_refill' => $jenisRefill,
                            'qty' => $totalKebutuhanKg,
                        ]]);
                    }

                    $insufficientRequirement = $stockRequirements->first(function (array $requirement) {
                        /** @var JenisRefill|null $jenisRefill */
                        $jenisRefill = $requirement['jenis_refill'] ?? null;

                        return $jenisRefill && (float) $jenisRefill->stok < (float) ($requirement['qty'] ?? 0);
                    });

                    if ($insufficientRequirement) {
                        /** @var JenisRefill $insufficientJenisRefill */
                        $insufficientJenisRefill = $insufficientRequirement['jenis_refill'];
                        $stockErrorKey = $selectedUnitApars->isNotEmpty() ? 'service_unit_apar_ids' : 'service_jenis_refill_id';
                        throw ValidationException::withMessages([
                            $stockErrorKey => 'Stok refill ' . $insufficientJenisRefill->nama_label . ' tidak mencukupi.',
                        ]);
                    }

                    if ($selectedUnitApars->isNotEmpty()) {
                        $pesanan->service_keluhan = $this->selectedRegisteredUnitsNote(
                            unitApars: $selectedUnitApars,
                            purchaseLabel: $servicePurchaseLabel,
                            totalLabel: 'Total Refil',
                            total: $estimasiBiaya,
                            metode: $serviceMetode,
                            customerNote: $originalServiceKeluhan,
                            lineAmounts: $registeredLineAmounts,
                            unitDetails: $registeredUnitDetails,
                        );
                    }

                    $serviceKeluhanForText = str_replace(["\r\n", "\r", "\n"], ' | ', $pesanan->service_keluhan ?: '-');
                    $totalPembayaranService = $estimasiBiaya + $serviceOngkir;

                    $singleRegisteredRefill = $selectedUnitApars->isNotEmpty()
                        ? ($registeredRefillSummary['jenis_refill'] ?? null)
                        : null;
                    $refillUnitLabel = $selectedUnitApars->isNotEmpty()
                        ? (collect($registeredRefillSummary['stock_requirements'] ?? [])
                            ->map(fn (array $requirement) => $requirement['jenis_refill']->satuan_label ?? null)
                            ->filter()
                            ->first() ?: 'Kg')
                        : $jenisRefill->satuan_label;
                    $pesanan->service_jenis_refill_id = $selectedUnitApars->isNotEmpty()
                        ? ($singleRegisteredRefill ? $singleRegisteredRefill->id : null)
                        : $jenisRefill->id;
                    $pesanan->service_paket_id = null;
                    $pesanan->service_total_kg = $totalKebutuhanKg;
                    $pesanan->service_estimasi_biaya = $estimasiBiaya;
                    $pesanan->total = $totalPembayaranService;
                    $pesanan->total_harga = $totalPembayaranService;
                    $refillSummaryLabel = $selectedUnitApars->isNotEmpty()
                        ? collect($registeredRefillSummary['stock_requirements'] ?? [])
                            ->map(fn (array $requirement) => $requirement['jenis_refill']->nama_label ?? null)
                            ->filter()
                            ->implode(', ')
                        : $jenisRefill->nama_label;
                    $pesanan->keterangan = "Permintaan REFILL " . ($refillSummaryLabel !== '' ? $refillSummaryLabel : 'APAR')
                        . " | Status Unit: " . ($serviceUnitStatus === 'terdaftar' ? 'APAR Terdaftar' : 'APAR Belum Terdaftar')
                        . ($selectedUnitApars->isNotEmpty() ? " | Riwayat: {$servicePurchaseLabel}" : '')
                        . " | Ukuran: {$serviceUkuranApar}"
                        . " | Jumlah: {$serviceJumlahUnit} unit"
                        . " | Kebutuhan: {$totalKebutuhanKg} {$refillUnitLabel}"
                        . " | Metode: {$serviceMetode}"
                        . ($serviceOngkir > 0 ? " | Ongkir: Rp" . number_format($serviceOngkir, 0, ',', '.') : '')
                        . " | Catatan: " . $serviceKeluhanForText;
                    $pesanan->save();
                    $successMessage = 'Pesanan refill berhasil dibuat dengan total estimasi ' . number_format($totalPembayaranService, 0, ',', '.')
                        . '. Silakan lanjutkan pembayaran untuk mengaktifkan proses pengerjaan.';
                    }
                } else {
                    if ($usesManualMultiItemFlow) {
                        $manualServiceSummary = $this->buildManualServiceOrderSummary(
                            $manualServiceItems,
                            $servicePackagePricingService,
                            $serviceUkuranOptions,
                        );
                        $serviceJumlahUnit = (int) ($manualServiceSummary['total_units'] ?? 0);
                        $serviceUkuranApar = (string) ($manualServiceSummary['ukuran_summary'] ?? '');
                        $serviceJenisAparLabel = (string) ($manualServiceSummary['display_summary'] ?? '');
                        $estimasiBiaya = (float) ($manualServiceSummary['estimasi_biaya'] ?? 0);
                        $pesanan->service_jenis_apar = $serviceJenisAparLabel;
                        $pesanan->service_ukuran_apar = $serviceUkuranApar;
                        $pesanan->service_jumlah_unit = $serviceJumlahUnit;
                        $pesanan->service_keluhan = $this->buildManualServiceOrderNote(
                            $manualServiceSummary,
                            $serviceMetode,
                            $originalServiceKeluhan,
                        );

                        $pesanan->service_paket_id = $manualServiceSummary['single_paket_id'] ?? null;
                        $pesanan->service_jenis_refill_id = null;
                        $pesanan->service_total_kg = null;
                        $pesanan->service_estimasi_biaya = $estimasiBiaya;
                        $totalPembayaranService = $estimasiBiaya + $serviceOngkir;
                        $pesanan->total = $totalPembayaranService;
                        $pesanan->total_harga = $totalPembayaranService;
                        $serviceKeluhanForText = str_replace(["\r\n", "\r", "\n"], ' | ', $pesanan->service_keluhan ?: '-');
                        $serviceSummaryLabel = trim((string) ($manualServiceSummary['headline'] ?? ''));
                        $pesanan->keterangan = 'Permintaan SERVICE ' . ($serviceSummaryLabel !== '' ? $serviceSummaryLabel : 'APAR')
                            . " | Item: {$serviceJenisAparLabel}"
                            . " | Jumlah: {$serviceJumlahUnit} unit"
                            . " | Metode: {$serviceMetode}"
                            . ($serviceOngkir > 0 ? ' | Ongkir: Rp' . number_format($serviceOngkir, 0, ',', '.') : '')
                            . ' | Catatan: ' . $serviceKeluhanForText;
                        $pesanan->save();

                        $successMessage = 'Pesanan service berhasil dibuat dengan total estimasi ' . number_format($totalPembayaranService, 0, ',', '.')
                            . '. Silakan lanjutkan pembayaran untuk mengaktifkan proses pengerjaan.';
                    } else {
                        $requestedServicePaketId = (int) $request->input('service_paket_id');
                        $paket = app(ServiceMasterSyncService::class)
                            ->visibleServicePakets(['peralatans', 'jenisRefill'])
                            ->firstWhere('id', $requestedServicePaketId);

                        // Tetap izinkan paket lama yang masih ada di database agar alur service sebelumnya tidak ikut rusak.
                        if (! $paket && $requestedServicePaketId > 0) {
                            $paket = ServicePaket::query()
                                ->with(['peralatans', 'jenisRefill'])
                                ->find($requestedServicePaketId);
                        }

                        if (!$paket) {
                            throw ValidationException::withMessages([
                                'service_paket_id' => 'Paket service wajib dipilih.',
                            ]);
                        }

                        $serviceLineSpecs = $selectedUnitApars->isNotEmpty()
                            ? $this->buildRegisteredServicePackageLineSpecs($selectedUnitApars)
                            : $this->buildManualServicePackageLineSpec(
                                media: $serviceJenisAparLabel,
                                ukuran: $serviceUkuranApar,
                                jumlahUnit: $serviceJumlahUnit,
                            );
                        $packageSummary = $servicePackagePricingService->summarizePackageOrder($paket, $serviceLineSpecs);
                        $estimasiBiaya = (float) ($packageSummary['total_price'] ?? 0);
                        $estimasiPeralatan = $packageSummary['peralatan_items'] ?? [];
                        if ($estimasiBiaya <= 0) {
                            throw ValidationException::withMessages([
                                'service_paket_id' => 'Harga standar service untuk jenis service yang dipilih belum tersedia.',
                            ]);
                        }

                        $pesanan->service_keluhan = $this->buildServicePackageOrderNote(
                            paket: $paket,
                            lineItems: $packageSummary['line_items'] ?? [],
                            peralatanItems: $estimasiPeralatan,
                            total: $estimasiBiaya,
                            metode: $serviceMetode,
                            customerNote: $originalServiceKeluhan,
                            purchaseLabel: $selectedUnitApars->isNotEmpty() ? $servicePurchaseLabel : null,
                        );

                        $pesanan->service_paket_id = $paket->id;
                        $pesanan->service_jenis_refill_id = null;
                        $pesanan->service_total_kg = null;
                        $pesanan->service_estimasi_biaya = $estimasiBiaya;
                        $totalPembayaranService = $estimasiBiaya + $serviceOngkir;
                        $pesanan->total = $totalPembayaranService;
                        $pesanan->total_harga = $totalPembayaranService;
                        $pesanan->keterangan = "Permintaan SERVICE {$paket->nama}"
                            . " | Status Unit: " . ($serviceUnitStatus === 'terdaftar' ? 'APAR Terdaftar' : 'APAR Belum Terdaftar')
                            . ($selectedUnitApars->isNotEmpty() ? " | Riwayat: {$servicePurchaseLabel}" : '')
                            . " | Ukuran: {$serviceUkuranApar}"
                            . " | Jumlah: {$serviceJumlahUnit} unit"
                            . " | Metode: {$serviceMetode}"
                            . ($serviceOngkir > 0 ? " | Ongkir: Rp" . number_format($serviceOngkir, 0, ',', '.') : '')
                            . " | Catatan: " . str_replace(["\r\n", "\r", "\n"], ' | ', $pesanan->service_keluhan ?: '-');
                        $pesanan->save();

                        $successMessage = 'Pesanan service berhasil dibuat dengan total estimasi ' . number_format($totalPembayaranService, 0, ',', '.')
                            . '. Silakan lanjutkan pembayaran untuk mengaktifkan proses pengerjaan.';
                    }
                }

                $redirectToPayment = true;
            }

            DB::commit();

            // Broadcast ke admin real-time bila kanal tersedia.
            $this->safelyBroadcastPesananBaru($pesanan);

            if ($redirectToPayment) {
                return redirect()->route('order.payment', $pesanan)->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');
            }

            return redirect()->route('riwayat-apar')->with('success', ($successMessage ?: 'Pesanan berhasil dikirim.') . ' ID: ' . $pesanan->id);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (RuntimeException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }

    // orderAskWhatsapp removed

    public function rajaOngkirDestination(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:3|max:255',
        ]);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Lokasi pengiriman berhasil ditemukan.',
                'data' => $this->rajaOngkirService()->searchDestination((string) $validated['search']),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
            ], 422);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Ongkir belum dapat dihitung. Periksa alamat tujuan atau coba lagi.',
                'data' => [],
            ], 500);
        }
    }

    public function rajaOngkirCost(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'nullable|string|max:50',
            'destination_label' => 'nullable|string|max:255',
            'courier' => 'nullable|string|max:50',
            'weight' => 'nullable|integer|min:1',
            'order_type' => 'nullable|string|max:50',
            'handling_method' => 'nullable|string|max:50',
            'items' => 'nullable|array',
            'items.*.produk_id' => 'nullable|integer|exists:produks,id',
            'items.*.jumlah' => 'nullable|integer|min:1',
            'service_unit_apar_ids' => 'nullable|array',
            'service_unit_apar_ids.*' => 'integer|exists:unit_apars,id',
            'service_ukuran_apar' => 'nullable|string|max:120',
            'service_jumlah_unit' => 'nullable|integer|min:1|max:1000',
            'alamat_lat' => 'nullable|numeric|between:-90,90',
            'alamat_lng' => 'nullable|numeric|between:-180,180',
        ]);

        $orderType = mb_strtolower(trim((string) ($validated['order_type'] ?? '')));
        $handlingMethod = trim((string) ($validated['handling_method'] ?? ''));
        $isServicePickupQuote = in_array($orderType, ['service', 'refill'], true);
        $isZeroCostPickupMethod = $isServicePickupQuote
            ? in_array(mb_strtolower($handlingMethod), ['antar sendiri', 'ambil_sendiri', 'pickup'], true)
            : (
                $this->normalizeShippingMethod($handlingMethod) === 'pickup'
                || in_array(mb_strtolower($handlingMethod), ['antar sendiri', 'ambil_sendiri'], true)
            );

        if ($isZeroCostPickupMethod) {
            return response()->json([
                'success' => true,
                'message' => $isServicePickupQuote
                    ? 'Biaya penjemputan Rp 0 untuk metode Antar Sendiri.'
                    : 'Ongkir Rp 0 untuk metode tanpa pengiriman.',
                'quote_type' => 'pickup',
                'courier' => null,
                'courier_name' => null,
                'service' => null,
                'service_description' => null,
                'etd' => null,
                'cost' => 0,
                'formatted_cost' => 'Rp 0',
                'weight' => 0,
                'destination_id' => $validated['destination_id'] ?? null,
                'destination_label' => $validated['destination_label'] ?? null,
                'distance_km' => 0,
                'round_trip_distance_km' => 0,
                'rate_per_km' => $isServicePickupQuote ? $this->servicePickupPricingService()->ratePerKm() : null,
                'minimum_cost' => $isServicePickupQuote ? $this->servicePickupPricingService()->minimumCost() : null,
                'raw' => config('app.debug') ? ['mode' => 'pickup'] : null,
            ]);
        }

        try {
            if ($isServicePickupQuote) {
                $pelanggan = $this->authenticatedCheckoutCustomer();
                $customerLat = $this->sanitizeCoordinate($validated['alamat_lat'] ?? null)
                    ?? $this->sanitizeCoordinate($pelanggan?->alamat_lat);
                $customerLng = $this->sanitizeCoordinate($validated['alamat_lng'] ?? null)
                    ?? $this->sanitizeCoordinate($pelanggan?->alamat_lng);
                $quote = $this->servicePickupPricingService()->quote($customerLat, $customerLng);

                return response()->json([
                    'success' => true,
                    'message' => 'Biaya penjemputan berhasil dihitung.',
                    'quote_type' => 'service_pickup',
                    'courier' => null,
                    'courier_name' => null,
                    'service' => null,
                    'service_description' => null,
                    'etd' => null,
                    'cost' => $quote['cost'],
                    'formatted_cost' => 'Rp ' . number_format((float) $quote['cost'], 0, ',', '.'),
                    'weight' => 0,
                    'destination_id' => null,
                    'destination_label' => null,
                    'distance_km' => $quote['distance_km'],
                    'round_trip_distance_km' => $quote['round_trip_distance_km'],
                    'rate_per_km' => $quote['rate_per_km'],
                    'minimum_cost' => $quote['minimum_cost'],
                    'raw' => config('app.debug') ? $quote : null,
                ]);
            }

            $destinationId = trim((string) ($validated['destination_id'] ?? ''));
            if ($destinationId === '') {
                throw new RuntimeException('Lokasi pengiriman belum dapat digunakan untuk menghitung ongkir. Silakan perbarui alamat pengiriman Anda.');
            }

            $weight = max(
                1000,
                (int) ($validated['weight'] ?? $this->resolveRajaOngkirWeightFromRequest($request))
            );

            $quote = $this->rajaOngkirService()->getCheapestCost(
                destinationId: $destinationId,
                weight: $weight,
                courier: trim((string) ($validated['courier'] ?? '')) ?: null,
            );

            return response()->json([
                'success' => true,
                'message' => 'Ongkir berhasil dihitung.',
                'quote_type' => 'product_delivery',
                'courier' => $quote['courier_code'],
                'courier_name' => $quote['courier_name'],
                'service' => $quote['service'],
                'service_description' => $quote['service_description'],
                'etd' => $quote['etd'],
                'cost' => $quote['cost'],
                'formatted_cost' => $quote['formatted_cost'],
                'weight' => $quote['weight'],
                'destination_id' => $destinationId,
                'destination_label' => $validated['destination_label'] ?? null,
                'distance_km' => null,
                'round_trip_distance_km' => null,
                'rate_per_km' => null,
                'minimum_cost' => null,
                'raw' => config('app.debug') ? $quote : null,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Ongkir belum dapat dihitung. Periksa alamat tujuan atau coba lagi.',
            ], 500);
        }
    }

    public function orderShippingQuote(Request $request)
    {
        return $this->rajaOngkirCost($request);
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
        if ($pesanan->isProductOrder() && !$this->canAccessProductOrder($pesanan)) {
            if (!Auth::check()) {
                return redirect()->guest(route('login'));
            }

            abort(403, 'Anda tidak memiliki akses untuk melihat pesanan ini.');
        }

        if ($pesanan->isProductOrder() && $pesanan->hasPendingPurchasePriceRequest()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Pengajuan Harga Pembelian ini masih menunggu persetujuan admin.');
        }

        if ($pesanan->isProductOrder() && !$pesanan->canPay()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Pembayaran untuk transaksi ini belum tersedia atau sudah diproses.');
        }

        $pesanan->loadMissing(['details.produk', 'serviceJenisRefill', 'servicePaket']);
        $banks = $this->bankAccounts();

        return view('public.order.payment', compact('pesanan', 'banks'));
    }

    public function orderPaymentStore(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->isProductOrder() && !$this->canAccessProductOrder($pesanan)) {
            if (!Auth::check()) {
                return redirect()->guest(route('login'));
            }

            abort(403, 'Anda tidak memiliki akses untuk mengubah pesanan ini.');
        }

        if ($pesanan->isProductOrder() && $pesanan->hasPendingPurchasePriceRequest()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Pengajuan Harga Pembelian ini masih menunggu persetujuan admin.');
        }

        if ($pesanan->isProductOrder() && !$pesanan->canUploadPaymentProof()) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Bukti pembayaran belum bisa diunggah untuk transaksi ini.');
        }

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

        $proofPath = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        $pesanan->loadMissing(['details.produk', 'pelanggan', 'serviceJenisRefill', 'servicePaket.peralatans', 'service']);

        $isApprovedSpecialPrice = $pesanan->hasApprovedPurchasePriceRequest();
        $statusAfterPayment = $pesanan->tipe === 'service'
            ? ($pesanan->service_metode_penanganan === 'antar sendiri'
                ? Pesanan::STATUS_MENUNGGU_KEDATANGAN_UNIT
                : Pesanan::STATUS_MENUNGGU_PENGAMBILAN)
            : Pesanan::STATUS_DIPROSES;

        $payableTotal = $pesanan->payableTotal();

        try {
            DB::transaction(function () use ($pesanan, $validated, $lockedBank, $isApprovedSpecialPrice, $proofPath, $statusAfterPayment, $payableTotal) {
                $pesanan->update(array_merge([
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'bank' => $lockedBank,
                    'total' => $payableTotal ?: ($pesanan->total ?: $pesanan->total_harga),
                    'total_harga' => $payableTotal ?: ($pesanan->total_harga ?: $pesanan->total),
                    'tipe_harga' => $isApprovedSpecialPrice ? 'deal' : 'normal',
                    'status' => $statusAfterPayment,
                    'bukti_pembayaran' => $proofPath,
                    'pembayaran_terkonfirmasi_at' => now(),
                ], Pesanan::purchasePriceAttributes([
                    'used' => $isApprovedSpecialPrice,
                    'used_at' => $isApprovedSpecialPrice ? ($pesanan->kode_nego_terpakai_at ?: now()) : null,
                ])));

                if ($pesanan->isPackageServiceOrder()) {
                    $this->syncServiceLogForPackageOrder($pesanan);
                }

                app(PaidOrderStockService::class)->apply($pesanan->fresh([
                    'details.produk.jenisApar',
                    'pelanggan',
                    'service',
                    'serviceJenisRefill',
                    'servicePaket.peralatans',
                    'unitApars.produk',
                ]));
            });
        } catch (\RuntimeException $exception) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($proofPath);

            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $pesanan->refresh()->loadMissing(['details.produk', 'pelanggan', 'serviceJenisRefill', 'servicePaket.peralatans', 'service']);
        $pesanan->pelanggan?->update(['status' => 'tetap']);

        $tipeHargaLabel = $isApprovedSpecialPrice
            ? 'Harga Final'
            : (((int) ($pesanan->pricingSummary()['diskonPersen'] ?? 0)) > 0 ? 'Promo Pembelian Banyak' : 'Harga Normal');
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

        $waMessage = "Halo Admin, saya sudah mengonfirmasi pembayaran transaksi.\n\n"
            . "Transaksi: " . $pesanan->transactionDisplayName() . "\n"
            . "Waktu: " . $pesanan->displayTransactionDateTime() . " WIB\n"
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
        $waUrl = \App\Support\WhatsApp::companyLink($waMessage);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tersimpan.',
                'wa_url' => $waUrl,
                'redirect_url' => route('home'),
            ]);
        }

        return redirect()
            ->route('home')
            ->with('success', 'Pembayaran tersimpan. Bukti berhasil dikirim ke admin.');
    }

    // Removed checkNegoCode

    public function complainCreate()
    {
        $pelanggan = $this->authenticatedCustomer();
        $selectedOrder = $pelanggan && request()->filled('pesanan')
            ? $this->resolveFeedbackOrder(request(), $pelanggan)
            : null;

        return view('public.complain.create', [
            'pelanggan' => $pelanggan,
            'selectedOrder' => $selectedOrder,
            'existingComplain' => $selectedOrder?->complain,
        ]);
    }

    public function complainStore(Request $request)
    {
        $request->validate([
            'no_wa' => 'nullable|string',
            'pesanan_id' => 'nullable|exists:pesanans,id',
            'isi_complain' => 'required|string',
            'foto' => 'nullable|image|max:5120',
        ]);

        $pelanggan = $this->resolveFeedbackCustomer($request);

        if (!$pelanggan) {
            $message = 'Silakan login menggunakan akun pelanggan untuk mengirim komplain.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->route('login')->with('error', $message);
        }

        $selectedOrder = $this->resolveFeedbackOrder($request, $pelanggan);
        if ($request->filled('pesanan_id') && !$selectedOrder) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Pesanan yang dipilih tidak cocok dengan akun pelanggan ini.'], 400);
            }
            return back()->with('error', 'Pesanan yang dipilih tidak cocok dengan akun pelanggan ini.')->withInput();
        }

        if ($selectedOrder?->complain) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Komplain untuk transaksi ini sudah pernah dikirim. Admin akan menindaklanjuti melalui WhatsApp.'], 400);
            }
            return back()->with('error', 'Komplain untuk transaksi ini sudah pernah dikirim. Admin akan menindaklanjuti melalui WhatsApp.')->withInput();
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('complains', 'public');
        }

        Complain::create([
            'pelanggan_id' => $pelanggan->id,
            'pesanan_id' => $selectedOrder?->id,
            'service_id' => $selectedOrder?->service?->id,
            'isi_complain' => $request->isi_complain,
            'foto_path' => $fotoPath,
            'tanggal' => now(),
        ]);

        $redirectRoute = $this->authenticatedCustomer() ? 'riwayat-apar' : 'home';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Komplain Anda sudah kami terima. Tim admin akan follow up melalui WhatsApp.',
            ]);
        }

        return redirect()->route($redirectRoute)->with('success', 'Komplain Anda sudah kami terima. Tim admin akan follow up melalui WhatsApp.');
    }

    public function confirmOrderReceived(Request $request, Pesanan $pesanan)
    {
        $pelanggan = $this->authenticatedCustomer();

        if (!$pelanggan) {
            $message = 'Silakan login menggunakan akun pelanggan untuk mengonfirmasi pesanan.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect()->route('login')->with('error', $message);
        }

        if ((int) $pesanan->pelanggan_id !== (int) $pelanggan->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan yang dipilih tidak cocok dengan akun pelanggan ini.',
            ], 403);
        }

        $existingReview = $this->resolveLinkedTestimoniForOrder($pelanggan, $pesanan);
        if ($existingReview || $pesanan->hasSubmittedTestimonial()) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan untuk transaksi ini sudah pernah dikirim.',
            ], 422);
        }

        if (!$pesanan->canCustomerConfirmReceived()) {
            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi pesanan hanya tersedia saat status pesanan siap diterima pelanggan.',
            ], 422);
        }

        if ($pesanan->hasCustomerConfirmed()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan ini sudah pernah Anda konfirmasi.',
                'open_review' => true,
            ]);
        }

        DB::transaction(function () use ($pesanan, $request) {
            $pesanan->forceFill([
                'status' => Pesanan::STATUS_SELESAI_FINAL,
                'customer_confirmed_at' => now(),
                'customer_confirmed_by' => $request->user()?->id,
            ])->save();

            app(FinalTransactionStockService::class)->apply($pesanan->fresh());
        });

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dikonfirmasi dan transaksi sekarang berstatus Selesai Final.',
            'open_review' => true,
        ]);
    }

    public function testimoniCreate()
    {
        $pelanggan = $this->authenticatedCustomer();
        $selectedOrder = $pelanggan && request()->filled('pesanan')
            ? $this->resolveFeedbackOrder(request(), $pelanggan)
            : null;
        $existingReview = $pelanggan && $selectedOrder
            ? $this->resolveLinkedTestimoniForOrder($pelanggan, $selectedOrder)
            : null;

        return view('public.testimoni.create', [
            'pelanggan' => $pelanggan,
            'selectedOrder' => $selectedOrder,
            'existingReview' => $existingReview,
        ]);
    }

    public function testimoniStore(Request $request)
    {
        $request->validate([
            'no_wa' => 'nullable|string',
            'pesanan_id' => 'nullable|exists:pesanans,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
            'foto' => 'nullable|image|max:5120',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $pelanggan = $this->resolveFeedbackCustomer($request);

        if (!$pelanggan) {
            $message = 'Silakan login menggunakan akun pelanggan untuk mengirim testimoni.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->route('login')->with('error', $message);
        }

        $selectedOrder = $this->resolveFeedbackOrder($request, $pelanggan);
        if ($request->filled('pesanan_id') && !$selectedOrder) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Transaksi yang dipilih tidak cocok dengan akun pelanggan ini.'], 400);
            }
            return back()->with('error', 'Transaksi yang dipilih tidak cocok dengan akun pelanggan ini.')->withInput();
        }

        if ($selectedOrder && !$selectedOrder->isCompleted()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Testimoni baru bisa diberikan setelah transaksi selesai.'], 400);
            }
            return back()->with('error', 'Testimoni baru bisa diberikan setelah transaksi selesai.')->withInput();
        }

        if ($selectedOrder && !$selectedOrder->isAdminFinalized()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Ulasan hanya bisa diberikan setelah admin menyelesaikan transaksi final.'], 400);
            }
            return back()->with('error', 'Ulasan hanya bisa diberikan setelah admin menyelesaikan transaksi final.')->withInput();
        }

        if ($selectedOrder && !$selectedOrder->hasCustomerConfirmed()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Konfirmasi Pesanan Diterima terlebih dahulu sebelum mengirim ulasan.'], 400);
            }
            return back()->with('error', 'Konfirmasi Pesanan Diterima terlebih dahulu sebelum mengirim ulasan.')->withInput();
        }

        if ($selectedOrder && ($selectedOrder->hasSubmittedTestimonial() || $this->resolveLinkedTestimoniForOrder($pelanggan, $selectedOrder))) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Transaksi ini sudah pernah diberi penilaian.'], 400);
            }
            return back()->with('error', 'Transaksi ini sudah pernah diberi penilaian.')->withInput();
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('testimonis', 'public');
        }

        $testimoni = Testimoni::create([
            'pelanggan_id' => $pelanggan->id,
            'transaksi_type' => $selectedOrder ? Pesanan::class : null,
            'transaksi_id' => $selectedOrder?->id,
            'rating' => $request->rating,
            'review' => $request->review,
            'foto_path' => $fotoPath,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'tanggal' => now(),
            'status' => 'approved',
        ]);

        if ($selectedOrder) {
            $selectedOrder->forceFill([
                'testimonial_submitted_at' => now(),
            ])->save();

            ActivityLog::log(
                description: $this->feedbackLinkDescription($pelanggan, $selectedOrder),
                logName: 'feedback',
                subjectType: Testimoni::class,
                subjectId: $testimoni->id,
                event: 'linked_to_order',
                properties: [
                    'pelanggan_id' => $pelanggan->id,
                    'pesanan_id' => $selectedOrder->id,
                    'order_code' => $selectedOrder->orderCode(),
                ],
            );
        }

        $redirectRoute = $this->authenticatedCustomer() ? 'riwayat-apar' : 'home';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih. Testimoni Anda berhasil dikirim dan ditayangkan.',
            ]);
        }

        return redirect()->route($redirectRoute)->with('success', 'Terima kasih. Testimoni Anda berhasil dikirim dan ditayangkan.');
    }
}
