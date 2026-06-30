<?php

namespace App\Support;

use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminPesananData
{
    public static function relations(): array
    {
        return [
            'pelanggan',
            'details.produk.jenisApar',
            'unitApars.produk',
            'service.unitApar.pelanggan',
            'service.unitApar.produk.jenisApar',
            'servicePaket',
            'serviceJenisRefill',
            'teknisi',
        ];
    }

    public static function query(): Builder
    {
        return Pesanan::query()
            ->visibleInAdminPesananMenu()
            ->with(static::relations())
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at');
    }

    public static function normalizeRevenuePeriod(?string $period): string
    {
        return $period === 'all' ? 'all' : 'month';
    }

    /**
     * @param Collection<int, Pesanan> $pesanans
     * @return array{aktif: Collection<int, Pesanan>, riwayat: Collection<int, Pesanan>}
     */
    public static function split(Collection $pesanans): array
    {
        $riwayat = $pesanans
            ->filter(fn (Pesanan $pesanan) => static::isHistoryStatus($pesanan))
            ->values();

        $aktif = $pesanans
            ->reject(fn (Pesanan $pesanan) => static::isHistoryStatus($pesanan))
            ->values();

        return [
            'aktif' => $aktif,
            'riwayat' => $riwayat,
        ];
    }

    public static function summary(?string $period = null): array
    {
        $resolvedPeriod = static::normalizeRevenuePeriod($period);
        $revenueQuery = Pesanan::query()
            ->revenueRecognized();

        if ($resolvedPeriod === 'month') {
            $monthStart = now()->startOfMonth()->toDateString();
            $monthEnd = now()->endOfMonth()->toDateString();
            $revenueDateExpression = Pesanan::revenueRecognitionDateExpression();

            $revenueQuery->whereRaw(
                "DATE({$revenueDateExpression}) BETWEEN ? AND ?",
                [$monthStart, $monthEnd]
            );
        }

        return [
            'totalTransaksi' => Pesanan::query()->count(),
            'totalPembelianUnit' => Pesanan::query()->where('tipe', 'produk')->count(),
            'totalRefill' => Pesanan::query()
                ->where('tipe', 'service')
                ->where('service_jenis_layanan', 'refill')
                ->count(),
            'totalService' => Pesanan::query()
                ->where('tipe', 'service')
                ->where(function (Builder $builder) {
                    $builder->where('service_jenis_layanan', 'service')
                        ->orWhereNull('service_jenis_layanan')
                        ->orWhere('service_jenis_layanan', '');
                })
                ->count(),
            'penghasilanPesanan' => (float) ($revenueQuery->selectRaw('COALESCE(SUM(COALESCE(total_harga, total, 0)), 0) as aggregate')->value('aggregate') ?? 0),
            'revenuePeriod' => $resolvedPeriod,
        ];
    }

    /**
     * @param Collection<int, Pesanan> $pesanans
     * @return array<int, array<string, mixed>>
     */
    public static function detailData(Collection $pesanans): array
    {
        return $pesanans
            ->map(fn (Pesanan $pesanan) => static::detailPayload($pesanan))
            ->values()
            ->all();
    }

    public static function isHistoryStatus(Pesanan $pesanan): bool
    {
        return in_array(static::normalizedStatus($pesanan->status), [
            'selesai',
            'selesai final',
            'ditolak',
            'dibatalkan',
            'batal',
            'cancelled',
            'canceled',
        ], true);
    }

    private static function detailPayload(Pesanan $pesanan): array
    {
        $pricingSummary = $pesanan->pricingSummary();
        $purchasePriceLabel = $pesanan->purchasePriceStatusLabel();
        $requestedPurchasePrice = $pesanan->requestedPurchasePrice();
        $approvedPurchasePrice = $pesanan->approvedPurchaseFinalPrice();
        $purchasePriceAdminNote = trim((string) ($pesanan->catatan_admin_harga ?? $pesanan->catatan_admin ?? ''));
        $proofUrl = !empty($pesanan->bukti_pembayaran)
            ? '/storage/' . ltrim(str_replace('storage/', '', (string) $pesanan->bukti_pembayaran), '/')
            : null;
        $unitDisplay = $pesanan->isProductOrder()
            ? ServiceUnitDisplay::empty()
            : $pesanan->serviceUnitDisplay();

        return [
            'id' => $pesanan->id,
            'label' => $pesanan->adminOrderTypeLabel(),
            'pelanggan' => $pesanan->pelanggan?->nama ?? '-',
            'no_wa' => $pesanan->pelanggan?->no_wa ?? '-',
            'alamat' => $pesanan->pelanggan?->alamat ?? '-',
            'alamat_lat' => $pesanan->alamat_lat ?? $pesanan->pelanggan?->alamat_lat,
            'alamat_lng' => $pesanan->alamat_lng ?? $pesanan->pelanggan?->alamat_lng,
            'tanggal' => $pesanan->displayTransactionDateTime(),
            'status' => $pesanan->status,
            'status_label' => $pesanan->adminStatusLabel(),
            'hide_payment_badge' => $pesanan->shouldHidePaymentStatusBadge(),
            'payment_status_label' => $pesanan->isPaymentConfirmed() ? 'Lunas' : 'Belum Bayar',
            'metode' => $pesanan->trackingMethodLabel(),
            'bank' => strtoupper((string) ($pesanan->bank ?? '-')),
            'total_unit' => $pesanan->adminOrderUnitCount(),
            'total' => number_format((float) ($pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.'),
            'proof_url' => $proofUrl,
            'teknisi' => $pesanan->teknisi?->name,
            'is_paid' => $pesanan->isPaymentConfirmed(),
            'items_heading' => $pesanan->isProductOrder() ? 'Rincian Produk' : 'Rincian Pesanan',
            'items' => static::detailItems($pesanan),
            'summary_heading' => 'Ringkasan Pembayaran',
            'summary_lines' => static::summaryLines($pesanan, $pricingSummary),
            'meta' => static::metaFields($pesanan),
            'catatan' => static::detailNote($pesanan),
            'unit_display' => $unitDisplay,
            'peralatan' => static::peralatanItems($pesanan),
            'purchase_price' => [
                'has_request' => $pesanan->hasPurchasePriceRequest(),
                'is_pending' => $pesanan->hasPendingPurchasePriceRequest(),
                'is_approved' => $pesanan->hasApprovedPurchasePriceRequest(),
                'is_rejected' => $pesanan->hasRejectedPurchasePriceRequest(),
                'label' => $purchasePriceLabel,
                'badge_classes' => $pesanan->purchasePriceStatusClasses(),
                'requested_price' => !is_null($requestedPurchasePrice)
                    ? number_format((float) $requestedPurchasePrice, 0, ',', '.')
                    : null,
                'final_price' => !is_null($approvedPurchasePrice)
                    ? number_format((float) $approvedPurchasePrice, 0, ',', '.')
                    : null,
                'normal_total' => number_format((float) ($pricingSummary['normalTotalPembayaran'] ?? $pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.'),
                'current_total' => number_format((float) ($pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.'),
                'customer_note' => $pesanan->purchasePriceCustomerNote(),
                'admin_note' => $purchasePriceAdminNote !== '' ? $purchasePriceAdminNote : null,
                'acc_url' => route('admin.pesanan.pengajuan-harga.acc', $pesanan),
                'reject_url' => route('admin.pesanan.pengajuan-harga.tolak', $pesanan),
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function detailItems(Pesanan $pesanan): array
    {
        if ($pesanan->isProductOrder()) {
            return $pesanan->details
                ->map(function ($detail) {
                    return [
                        'nama' => $detail->produk?->nama ?? 'Produk Terhapus',
                        'meta' => collect([
                            $detail->produk?->jenisApar?->nama,
                            $detail->kapasitas,
                            $detail->merek,
                        ])->filter()->implode(' • '),
                        'qty_label' => (int) $detail->jumlah . ' unit',
                        'subtotal' => 'Rp ' . number_format((float) $detail->subtotal, 0, ',', '.'),
                    ];
                })
                ->values()
                ->all();
        }

        if ($pesanan->isRefillOrder()) {
            $refillItems = collect($pesanan->servicePricingBreakdown())
                ->map(function (array $item) {
                    return [
                        'nama' => (string) ($item['label'] ?? 'Refill APAR'),
                        'meta' => collect([
                            $item['ukuran'] ?? null,
                            isset($item['unit_price']) ? 'Rp ' . number_format((float) $item['unit_price'], 0, ',', '.') . '/unit' : null,
                        ])->filter()->implode(' â€¢ '),
                        'qty_label' => max(1, (int) ($item['qty'] ?? 1)) . ' unit',
                        'subtotal' => 'Rp ' . number_format((float) ($item['total'] ?? 0), 0, ',', '.'),
                    ];
                })
                ->values();

            if ($refillItems->isNotEmpty()) {
                return $refillItems->all();
            }

            return [[
                'nama' => $pesanan->serviceJenisRefill?->nama_label ?? 'Refill APAR',
                'meta' => collect([
                    $pesanan->adminOrderUnitCount() . ' unit',
                    $pesanan->service_total_kg ? static::formatNumber((float) $pesanan->service_total_kg) . ' kg' : null,
                ])->filter()->implode(' • '),
                'qty_label' => 'Total',
                'subtotal' => 'Rp ' . number_format((float) ($pesanan->pricingSummary()['totalPembayaran'] ?? 0), 0, ',', '.'),
            ]];
        }

        $items = collect($pesanan->servicePricingBreakdown())
            ->map(function (array $item) {
                return [
                    'nama' => (string) ($item['display_label'] ?? $item['label'] ?? 'Service APAR'),
                    'meta' => collect([
                        $item['ukuran'] ?? null,
                        isset($item['unit_price']) ? 'Rp ' . number_format((float) $item['unit_price'], 0, ',', '.') . '/unit' : null,
                    ])->filter()->implode(' • '),
                    'qty_label' => max(1, (int) ($item['qty'] ?? 1)) . ' unit',
                    'subtotal' => 'Rp ' . number_format((float) ($item['total'] ?? 0), 0, ',', '.'),
                ];
            })
            ->values();

        if ($items->isNotEmpty()) {
            return $items->all();
        }

        return [[
            'nama' => $pesanan->servicePaket?->nama ?? 'Service APAR',
            'meta' => $pesanan->adminOrderDetailMeta(),
            'qty_label' => 'Total',
            'subtotal' => 'Rp ' . number_format((float) ($pesanan->pricingSummary()['totalPembayaran'] ?? 0), 0, ',', '.'),
        ]];
    }

    /**
     * @return array<int, array{label: string, value: string, tone: string}>
     */
    private static function summaryLines(Pesanan $pesanan, array $pricingSummary): array
    {
        $approvedAdjustment = max(0, (float) $pesanan->purchasePriceInitialTotal() - (float) ($pricingSummary['totalPembayaran'] ?? 0));

        $lines = [[
            'label' => 'Subtotal Produk / Layanan',
            'value' => 'Rp ' . number_format((float) ($pricingSummary['subtotalProduk'] ?? 0), 0, ',', '.'),
            'tone' => 'default',
        ], [
            'label' => 'Diskon',
            'value' => ((float) ($pricingSummary['nominalDiskon'] ?? 0) > 0 ? '-Rp ' : 'Rp ') . number_format((float) ($pricingSummary['nominalDiskon'] ?? 0), 0, ',', '.'),
            'tone' => (float) ($pricingSummary['nominalDiskon'] ?? 0) > 0 ? 'positive' : 'default',
        ], [
            'label' => 'Biaya Pengiriman',
            'value' => 'Rp ' . number_format((float) ($pricingSummary['ongkir'] ?? 0), 0, ',', '.'),
            'tone' => 'default',
        ]];

        if ($approvedAdjustment > 0) {
            $lines[] = [
                'label' => 'Penyesuaian Harga Disetujui',
                'value' => '-Rp ' . number_format($approvedAdjustment, 0, ',', '.'),
                'tone' => 'positive',
            ];
        }

        $lines[] = [
            'label' => 'Total Pembayaran',
            'value' => 'Rp ' . number_format((float) ($pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.'),
            'tone' => 'total',
        ];

        return $lines;
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private static function metaFields(Pesanan $pesanan): array
    {
        $fields = [
            [
                'label' => 'Jenis Pesanan',
                'value' => $pesanan->adminOrderTypeLabel(),
            ],
            [
                'label' => 'Status Pesanan',
                'value' => $pesanan->adminStatusLabel(),
            ],
            [
                'label' => 'Metode',
                'value' => $pesanan->trackingMethodLabel(),
            ],
            [
                'label' => 'Status Pengiriman',
                'value' => $pesanan->requiresCustomerDeliveryConfirmation()
                    ? ($pesanan->isAwaitingCustomerConfirmation() ? 'Siap Dikirim' : ($pesanan->hasCustomerConfirmed() ? 'Diterima Pelanggan' : 'Menunggu Pengiriman'))
                    : 'Tanpa Pengiriman Lanjutan',
            ],
            [
                'label' => 'Bank Tujuan',
                'value' => strtoupper((string) ($pesanan->bank ?? '-')),
            ],
            [
                'label' => 'Total Unit',
                'value' => $pesanan->adminOrderUnitCount() . ' unit',
            ],
            [
                'label' => 'Teknisi',
                'value' => $pesanan->teknisi?->name ?? 'Belum ditugaskan',
            ],
        ];

        if ($pesanan->isRefillOrder()) {
            $fields[] = [
                'label' => 'Jenis Refill',
                'value' => $pesanan->serviceJenisRefill?->nama_label ?? 'Refill APAR',
            ];

            if ((float) ($pesanan->service_total_kg ?? 0) > 0) {
                $fields[] = [
                    'label' => 'Total Kebutuhan',
                    'value' => static::formatNumber((float) $pesanan->service_total_kg) . ' kg',
                ];
            }
        }

        if ($pesanan->isServiceOrder()) {
            $fields[] = [
                'label' => 'Jenis Service',
                'value' => $pesanan->servicePaket?->nama ?? 'Service APAR',
            ];
        }

        return $fields;
    }

    private static function detailNote(Pesanan $pesanan): ?string
    {
        $note = $pesanan->isProductOrder()
            ? trim((string) ($pesanan->catatan_admin ?? $pesanan->keterangan ?? ''))
            : trim((string) ($pesanan->catatan_admin ?: $pesanan->serviceCustomerNote() ?: $pesanan->keterangan ?: ''));

        return $note !== '' ? $note : null;
    }

    /**
     * @return array<int, array{nama: string, jumlah: string}>
     */
    private static function peralatanItems(Pesanan $pesanan): array
    {
        if (!$pesanan->isServiceOrder()) {
            return [];
        }

        return collect($pesanan->servicePeralatanItems())
            ->map(function (array $item) {
                return [
                    'nama' => (string) ($item['nama'] ?? '-'),
                    'jumlah' => static::formatNumber((float) ($item['jumlah'] ?? 0)),
                ];
            })
            ->filter(fn (array $item) => $item['nama'] !== '-')
            ->values()
            ->all();
    }

    private static function normalizedStatus(?string $status): string
    {
        return trim(strtolower(str_replace('_', ' ', (string) $status)));
    }

    private static function formatNumber(float $value): string
    {
        if (fmod($value, 1.0) === 0.0) {
            return number_format($value, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    }
}
