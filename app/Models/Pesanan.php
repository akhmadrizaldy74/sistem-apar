<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockMovement;
use App\Services\InventoryService;

class Pesanan extends Model
{
    use Auditable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_MENUNGGU_PENGAMBILAN = 'menunggu pengambilan';
    public const STATUS_MENUNGGU_KEDATANGAN_UNIT = 'menunggu kedatangan unit';
    public const STATUS_DITUGASKAN_KE_TEKNISI = 'ditugaskan ke teknisi';
    public const STATUS_DIKERJAKAN_TEKNISI = 'dikerjakan teknisi';
    public const STATUS_SELESAI_OLEH_TEKNISI = 'selesai oleh teknisi';
    public const STATUS_DIKONFIRMASI_ADMIN = 'dikonfirmasi admin';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_SELESAI_FINAL = 'selesai final';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_PERMINTAAN_MASUK = 'permintaan masuk';
    public const STATUS_DIREVIEW_ADMIN = 'direview admin';
    public const STATUS_MENUNGGU_PENJADWALAN = 'menunggu penjadwalan';
    public const STATUS_MENUNGGU_PERSETUJUAN_BIAYA = 'menunggu persetujuan biaya';
    public const STATUS_DISETUJUI = 'disetujui';

    public const PAYMENT_PENDING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PERMINTAAN_MASUK,
        self::STATUS_MENUNGGU_PERSETUJUAN_BIAYA,
        self::STATUS_DISETUJUI,
    ];

    public const TIMELINE_STEPS_PRODUCT = [
        1 => ['key' => 'pending', 'label' => 'Menunggu Pembayaran', 'icon' => 'fa-clock'],
        2 => ['key' => 'diproses', 'label' => 'Sedang Diproses', 'icon' => 'fa-gear'],
        3 => ['key' => 'dikirim', 'label' => 'Barang Disiapkan', 'icon' => 'fa-boxes-packing'],
        4 => ['key' => 'dikirim', 'label' => 'Dalam Pengiriman', 'icon' => 'fa-truck-fast'],
        5 => ['key' => 'siap_ambil', 'label' => 'Siap Diambil', 'icon' => 'fa-hand-holding-box'],
        6 => ['key' => 'selesai', 'label' => 'Selesai', 'icon' => 'fa-circle-check'],
    ];

    public const TIMELINE_STEPS_SERVICE = [
        1 => ['key' => 'pending', 'label' => 'Menunggu Pembayaran', 'icon' => 'fa-clock'],
        2 => ['key' => 'diproses', 'label' => 'Pembayaran Diverifikasi', 'icon' => 'fa-file-check'],
        3 => ['key' => 'diproses', 'label' => 'Sedang Diproses', 'icon' => 'fa-gear'],
        4 => ['key' => 'dikerjakan', 'label' => 'Dikerjakan Teknisi', 'icon' => 'fa-user-gear'],
        5 => ['key' => 'selesai', 'label' => 'Service/Refill Selesai', 'icon' => 'fa-check-to-slot'],
        6 => ['key' => 'siap', 'label' => 'Siap Diambil/Dikirim', 'icon' => 'fa-box'],
        7 => ['key' => 'selesai', 'label' => 'Selesai', 'icon' => 'fa-circle-check'],
    ];

    protected $fillable = [
        'pelanggan_id',
        'no_pesanan',
        'user_id',
        'nama_penerima',
        'nomor_wa_penerima',
        'alamat_pengiriman',
        'tipe',
        'sumber_pesanan',
        'is_pesanan_lama',
        'total',
        'total_harga',
        'service_jenis_layanan',
        'service_paket_id',
        'service_jenis_refill_id',
        'service_jenis_apar',
        'service_ukuran_apar',
        'service_jumlah_unit',
        'service_total_kg',
        'service_keluhan',
        'service_foto',
        'service_metode_penanganan',
        'service_estimasi_biaya',
        'service_admin_catatan',
        'tipe_harga',
        'metode_pembayaran',
        'bank',
        'link_pembayaran_terkirim_at',
        'pembayaran_terkonfirmasi_at',
        'metode_pengiriman',
        'ongkir',
        'shipping_distance_km',
        'alamat_maps',
        'alamat_detail',
        'alamat_lat',
        'alamat_lng',
        'bukti_pembayaran',
        'status',
        'harga_usulan',
        'harga_penawaran_pelanggan',
        'kode_nego',
        'kode_nego_terpakai_at',
        'is_nego',
        'keterangan',
        'catatan_admin',
        'tanggal',
        'teknisi_id',
        'teknisi_selesai_at',
        'teknisi_catatan',
        'stok_dikurangi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_harga' => 'float',
        'service_jumlah_unit' => 'integer',
        'service_total_kg' => 'float',
        'service_estimasi_biaya' => 'float',
        'ongkir' => 'float',
        'shipping_distance_km' => 'float',
        'alamat_lat' => 'float',
        'alamat_lng' => 'float',
        'is_pesanan_lama' => 'boolean',
        'kode_nego_terpakai_at' => 'datetime',
        'link_pembayaran_terkirim_at' => 'datetime',
        'pembayaran_terkonfirmasi_at' => 'datetime',
        'teknisi_selesai_at' => 'datetime',
        'stok_dikurangi' => 'boolean',
        'harga_penawaran_pelanggan' => 'float',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(\App\Models\User::class, 'teknisi_id');
    }

    public function complain()
    {
        return $this->hasOne(Complain::class);
    }

    public function details()
    {
        return $this->hasMany(PesananDetail::class);
    }

    public function service()
    {
        return $this->hasOne(Service::class);
    }

    public function servicePaket()
    {
        return $this->belongsTo(ServicePaket::class, 'service_paket_id');
    }

    public function serviceJenisRefill()
    {
        return $this->belongsTo(JenisRefill::class, 'service_jenis_refill_id');
    }

    public function unitApars()
    {
        return $this->hasMany(UnitApar::class);
    }

    public function reduceStock()
    {
        if ($this->stok_dikurangi || $this->tipe !== 'produk') {
            return;
        }

        foreach ($this->details as $detail) {
            if ($detail->produk) {
                $produk = $detail->produk;
                $jumlahDibutuhkan = $detail->jumlah;
                $stokSebelum = (float) $produk->stok_tersedia;

                // FIFO: Ambil batch yang tidak expired & memiliki sisa stok
                $batches = \App\Models\StokBatch::where('produk_id', $produk->id)
                    ->where('sisa_qty', '>', 0)
                    ->where('tgl_expired', '>=', now()->toDateString())
                    ->orderBy('tgl_expired', 'asc')
                    ->get();

                $totalTersedia = $batches->sum('sisa_qty');

                if ($totalTersedia < $jumlahDibutuhkan) {
                    throw new \RuntimeException("Stok non-expired produk '{$produk->nama}' tidak mencukupi untuk memenuhi pesanan!");
                }

                foreach ($batches as $batch) {
                    if ($jumlahDibutuhkan <= 0) break;

                    if ($batch->sisa_qty >= $jumlahDibutuhkan) {
                        $batch->decrement('sisa_qty', $jumlahDibutuhkan);
                        $jumlahDibutuhkan = 0;
                    } else {
                        $jumlahDibutuhkan -= $batch->sisa_qty;
                        $batch->update(['sisa_qty' => 0]);
                    }
                }

                $produk->decrement('stok', $detail->jumlah);

                app(InventoryService::class)->logProductMovement(
                    produk: $produk->fresh(),
                    qty: (float) $detail->jumlah,
                    movementType: StockMovement::MOVE_OUT,
                    sourceType: StockMovement::SOURCE_PENJUALAN_PRODUK,
                    stokSebelum: $stokSebelum,
                    stokSesudah: (float) $produk->fresh('stokBatches')->stok_tersedia,
                    reference: $this,
                    keterangan: 'Penjualan produk untuk pesanan #' . $this->id,
                    tanggal: $this->tanggal ?? now(),
                );
            }
        }

        $this->update(['stok_dikurangi' => true]);
    }

    public function orderCode(): string
    {
        return 'TNTI' . $this->tanggal->format('dmY') . 'AJ' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    public function isPaymentConfirmed(): bool
    {
        if (!is_null($this->pembayaran_terkonfirmasi_at)) {
            return true;
        }

        return !empty($this->bukti_pembayaran) && $this->status !== self::STATUS_PENDING;
    }

    public function payableTotal(): float
    {
        $total = (float) ($this->total_harga ?: $this->total ?: 0);

        if ($total <= 0 && $this->tipe === 'service') {
            $total = (float) ($this->service_estimasi_biaya ?: 0);
        }

        return max(0, $total);
    }

    public function isCompleted(): bool
    {
        return in_array((string) $this->status, [self::STATUS_SELESAI, self::STATUS_SELESAI_FINAL], true);
    }

    public function trackingTypeLabel(): string
    {
        if ($this->tipe === 'service') {
            return $this->service_jenis_layanan === 'refill' ? 'Refill APAR' : 'Service APAR';
        }

        return 'Pembelian APAR';
    }

    public function trackingItemLabel(): string
    {
        if ($this->tipe === 'service') {
            if ($this->service_jenis_layanan === 'refill') {
                return $this->serviceJenisRefill?->nama_label ?: 'Refill APAR';
            }

            return $this->servicePaket?->nama ?: 'Paket Service APAR';
        }

        $detailNames = $this->details
            ->pluck('produk.nama')
            ->filter()
            ->take(2)
            ->implode(', ');

        return $detailNames !== '' ? $detailNames : 'Pembelian Produk APAR';
    }

    public function trackingMethodLabel(): string
    {
        if ($this->tipe === 'service') {
            return $this->service_metode_penanganan === 'antar sendiri'
                ? 'Antar Sendiri'
                : 'Dijemput';
        }

        return $this->metode_pengiriman === 'diantar_internal'
            ? 'Ekspedisi / Diantar'
            : 'Ambil Sendiri';
    }

    public function isProductOrder(): bool
    {
        return $this->tipe === 'produk';
    }

    public function isRefillOrder(): bool
    {
        return $this->tipe === 'service' && $this->service_jenis_layanan === 'refill';
    }

    public function isServiceOrder(): bool
    {
        return $this->tipe === 'service' && ($this->service_jenis_layanan === 'service' || empty($this->service_jenis_layanan));
    }

    public function isPackageServiceOrder(): bool
    {
        return $this->tipe === 'service'
            && $this->service_jenis_layanan === 'service'
            && !is_null($this->service_paket_id);
    }

    public function estimatedServicePeralatan(): array
    {
        if (!$this->isPackageServiceOrder()) {
            return [];
        }

        $this->loadMissing('servicePaket.peralatans');

        $qtyMultiplier = max(1, (int) ($this->service_jumlah_unit ?? 1));

        return $this->servicePaket?->peralatans
            ?->map(function ($peralatan) use ($qtyMultiplier) {
                $jumlahPerUnit = (int) ($peralatan->pivot->jumlah_estimasi ?? 0);

                return [
                    'peralatan_id' => (int) $peralatan->id,
                    'nama' => (string) $peralatan->nama,
                    'jumlah' => $jumlahPerUnit * $qtyMultiplier,
                    'jumlah_per_unit' => $jumlahPerUnit,
                ];
            })
            ->filter(fn (array $item) => (int) ($item['jumlah'] ?? 0) > 0)
            ->values()
            ->all() ?? [];
    }

    public function serviceLogPayload(array $overrides = []): array
    {
        $this->loadMissing('servicePaket.peralatans');

        return array_merge([
            'service_paket_id' => $this->service_paket_id,
            'jenis_service' => $this->servicePaket?->nama ?? 'Service APAR',
            'rincian_layanan' => $this->servicePaket?->rincian_layanan,
            'tgl_service' => optional($this->tanggal)->toDateString() ?? now()->toDateString(),
            'biaya' => (float) ($this->service_estimasi_biaya ?: $this->total_harga ?: $this->total ?: 0),
            'estimasi_peralatan_json' => json_encode($this->estimatedServicePeralatan()),
            'keterangan' => $this->service_keluhan ?: $this->keterangan,
        ], $overrides);
    }

    public function publicStatusLabel(): string
    {
        $status = (string) $this->status;

        if ($status === self::STATUS_DITOLAK) {
            return 'Ditolak';
        }

        if ($this->tipe === 'service') {
            if (in_array($status, self::PAYMENT_PENDING_STATUSES, true)) {
                if (empty($this->bukti_pembayaran) && $this->payableTotal() > 0) {
                    return 'Menunggu Pembayaran';
                }
                if (!empty($this->bukti_pembayaran) && !$this->isPaymentConfirmed()) {
                    return 'Menunggu Verifikasi Pembayaran';
                }
                return 'Menunggu Diproses';
            }

            return match ($status) {
                self::STATUS_PENDING => 'Menunggu Pembayaran',
                self::STATUS_DIPROSES => 'Menunggu Diproses',
                self::STATUS_MENUNGGU_PENGAMBILAN => 'Menunggu Diproses',
                self::STATUS_MENUNGGU_KEDATANGAN_UNIT => 'Menunggu Diproses',
                self::STATUS_DITUGASKAN_KE_TEKNISI => 'Ditugaskan ke Teknisi',
                self::STATUS_DIKERJAKAN_TEKNISI => 'Sedang Dikerjakan',
                self::STATUS_SELESAI_OLEH_TEKNISI => 'Selesai oleh Teknisi',
                self::STATUS_DIKONFIRMASI_ADMIN,
                self::STATUS_SELESAI,
                self::STATUS_SELESAI_FINAL => 'Selesai',
                default => 'Menunggu Diproses',
            };
        }

        if (in_array($status, self::PAYMENT_PENDING_STATUSES, true)) {
            if (empty($this->bukti_pembayaran) && $this->payableTotal() > 0) {
                return 'Menunggu Pembayaran';
            }

            if (!empty($this->bukti_pembayaran) && ! $this->isPaymentConfirmed()) {
                return 'Menunggu Verifikasi Pembayaran';
            }
        }

        if ($this->metode_pengiriman === 'diantar_internal') {
            return match ($status) {
                self::STATUS_DIPROSES => 'Sedang Pengiriman',
                self::STATUS_SELESAI,
                self::STATUS_SELESAI_FINAL => 'Selesai',
                default => ucwords($status),
            };
        }

        return match ($status) {
            self::STATUS_DIPROSES => 'Siap Diambil',
            self::STATUS_SELESAI,
            self::STATUS_SELESAI_FINAL => 'Selesai',
            default => ucwords($status),
        };
    }

    public function publicStatusClasses(): string
    {
        $label = $this->publicStatusLabel();

        return match ($label) {
            'Menunggu Pembayaran', 'Menunggu Verifikasi Pembayaran' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'Menunggu Diproses', 'Ditugaskan ke Teknisi' => 'bg-blue-100 text-blue-800 border border-blue-200',
            'Sedang Dikerjakan' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
            'Selesai oleh Teknisi' => 'bg-purple-100 text-purple-800 border border-purple-200',
            'Siap Diambil', 'Selesai' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'Ditolak' => 'bg-red-100 text-red-800 border border-red-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function getTimelineStep(): int
    {
        $status = (string) $this->status;

        if ($status === self::STATUS_DITOLAK) {
            return 0;
        }

        if ($this->canPay()) {
            return 1;
        }

        if (in_array($status, [self::STATUS_SELESAI, self::STATUS_SELESAI_FINAL])) {
            return $this->tipe === 'service' ? 7 : 6;
        }

        if (in_array($status, [self::STATUS_DIKONFIRMASI_ADMIN, self::STATUS_SELESAI_OLEH_TEKNISI])) {
            if ($this->tipe === 'service') {
                return $this->service_metode_penanganan === 'antar sendiri' ? 6 : 6;
            }
            return $this->metode_pengiriman === 'diantar_internal' ? 4 : 5;
        }

        if ($status === self::STATUS_DIKERJAKAN_TEKNISI) {
            return 4;
        }

        if (in_array($status, [self::STATUS_DITUGASKAN_KE_TEKNISI, self::STATUS_MENUNGGU_PENGAMBILAN, self::STATUS_MENUNGGU_KEDATANGAN_UNIT])) {
            return 3;
        }

        if (in_array($status, [self::STATUS_PENDING, self::STATUS_DIPROSES])) {
            return $this->status === self::STATUS_PENDING ? 2 : 3;
        }

        return 2;
    }

    public function getTimelineData(): array
    {
        $currentStep = $this->getTimelineStep();
        $steps = [];
        $timelineSteps = $this->tipe === 'service' ? self::TIMELINE_STEPS_SERVICE : self::TIMELINE_STEPS_PRODUCT;

        foreach ($timelineSteps as $stepNum => $step) {
            $steps[] = [
                'number' => $stepNum,
                'label' => $step['label'],
                'icon' => $step['icon'],
                'state' => $stepNum < $currentStep ? 'completed' : ($stepNum === $currentStep ? 'current' : 'pending'),
            ];
        }

        return $steps;
    }

    public function getConditionStatus(): string
    {
        if ($this->tipe !== 'service') {
            return 'product';
        }

        return match ($this->service_jenis_layanan) {
            'refill' => 'refill',
            'service' => 'service',
            default => 'service',
        };
    }

    public function getUnitInfo(): ?array
    {
        if ($this->tipe === 'service') {
            return [
                'jumlah' => (int) ($this->service_jumlah_unit ?? 0),
                'ukuran' => $this->service_ukuran_apar ?? '-',
                'jenis' => $this->service_jenis_apar ?? '-',
            ];
        }

        if ($this->details->isNotEmpty()) {
            return [
                'jumlah' => $this->details->sum('jumlah'),
                'ukuran' => $this->details->first()?->kapasitas ?? '-',
                'jenis' => $this->details->first()?->produk?->jenisApar?->nama ?? '-',
            ];
        }

        return null;
    }

    public function isActiveOrder(): bool
    {
        return !in_array($this->status, [
            self::STATUS_SELESAI,
            self::STATUS_SELESAI_FINAL,
            self::STATUS_DITOLAK,
        ]);
    }

    public function canPay(): bool
    {
        return in_array((string) $this->status, self::PAYMENT_PENDING_STATUSES, true)
            && empty($this->bukti_pembayaran)
            && $this->payableTotal() > 0;
    }

    public function needsPickup(): bool
    {
        return $this->tipe === 'service'
            && $this->service_metode_penanganan !== 'antar sendiri'
            && in_array($this->status, [
                self::STATUS_DIKONFIRMASI_ADMIN,
                self::STATUS_SELESAI_OLEH_TEKNISI,
            ]);
    }

    public function getExpiryDate(): ?string
    {
        $service = $this->service;
        if ($service && $service->tgl_expired) {
            return $service->tgl_expired->format('d M Y');
        }

        if ($this->unitApars->isNotEmpty()) {
            return $this->unitApars->first()?->tgl_expired?->format('d M Y');
        }

        return null;
    }

    public function getPaymentMethodLabel(): string
    {
        if ($this->metode_pembayaran === 'cash') {
            return 'Tunai / Cash';
        }

        if ($this->bank && isset($this->bankAccounts()[$this->bank])) {
            return 'Transfer ' . $this->bankAccounts()[$this->bank]['nama_bank'];
        }

        return $this->metode_pembayaran ? ucfirst($this->metode_pembayaran) : '-';
    }

    private function bankAccounts(): array
    {
        return [
            'bca' => ['nama_bank' => 'Bank BCA'],
            'bri' => ['nama_bank' => 'Bank BRI'],
            'mandiri' => ['nama_bank' => 'Bank Mandiri'],
        ];
    }
}
