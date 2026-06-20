<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockMovement;
use App\Services\InventoryService;
use App\Services\OrderPricingService;
use App\Services\ServicePackagePricingService;
use App\Support\ServiceUnitDisplay;
use Illuminate\Support\Facades\Schema;

class Pesanan extends Model
{
    use Auditable;

    public const PRICE_REQUEST_PENDING = 'pending';
    public const PRICE_REQUEST_APPROVED = 'approved';
    public const PRICE_REQUEST_REJECTED = 'rejected';
    public const STATUS_MENUNGGU_PERSETUJUAN_HARGA = 'menunggu persetujuan';

    public const STATUS_PENDING = 'pending';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_MENUNGGU_PENGAMBILAN = 'menunggu pengambilan';
    public const STATUS_MENUNGGU_KEDATANGAN_UNIT = 'menunggu kedatangan unit';
    public const STATUS_DITUGASKAN_KE_TEKNISI = 'ditugaskan ke teknisi';
    public const STATUS_DIKERJAKAN_TEKNISI = 'dikerjakan teknisi';
    public const STATUS_SELESAI_OLEH_TEKNISI = 'selesai oleh teknisi';
    public const STATUS_DIKONFIRMASI_ADMIN = 'dikonfirmasi admin';
    public const STATUS_SIAP_DIKIRIM = 'siap dikirim';
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
        'customer_confirmed_at',
        'customer_confirmed_by',
        'testimonial_submitted_at',
        'metode_pengiriman',
        'ongkir',
        'harga_normal',
        'harga_setelah_diskon',
        'total_awal',
        'shipping_courier',
        'shipping_service',
        'shipping_etd',
        'shipping_destination_id',
        'shipping_destination_label',
        'shipping_weight',
        'shipping_distance_km',
        'alamat_maps',
        'alamat_detail',
        'alamat_lat',
        'alamat_lng',
        'bukti_pembayaran',
        'status',
        'harga_pengajuan',
        'harga_final',
        'harga_final_admin',
        'status_pengajuan_harga',
        'status_persetujuan_harga',
        'catatan_pengajuan_harga',
        'catatan_admin_harga',
        'disetujui_oleh',
        'disetujui_pada',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'harga_khusus_digunakan',
        'harga_usulan',
        'harga_penawaran_pelanggan',
        'kode_nego',
        'kode_nego_terpakai_at',
        'is_nego',
        'is_pengajuan_harga',
        'keterangan',
        'catatan_admin',
        'tanggal',
        'teknisi_id',
        'teknisi_selesai_at',
        'teknisi_catatan',
        'stok_dikurangi',
        'hidden_from_pesanan_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_harga' => 'float',
        'service_jumlah_unit' => 'integer',
        'service_total_kg' => 'float',
        'service_estimasi_biaya' => 'float',
        'ongkir' => 'float',
        'harga_normal' => 'float',
        'harga_setelah_diskon' => 'float',
        'total_awal' => 'float',
        'shipping_weight' => 'integer',
        'shipping_distance_km' => 'float',
        'alamat_lat' => 'float',
        'alamat_lng' => 'float',
        'is_pesanan_lama' => 'boolean',
        'kode_nego_terpakai_at' => 'datetime',
        'link_pembayaran_terkirim_at' => 'datetime',
        'pembayaran_terkonfirmasi_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
        'customer_confirmed_by' => 'integer',
        'testimonial_submitted_at' => 'datetime',
        'teknisi_selesai_at' => 'datetime',
        'stok_dikurangi' => 'boolean',
        'hidden_from_pesanan_at' => 'datetime',
        'harga_pengajuan' => 'float',
        'harga_final' => 'float',
        'harga_final_admin' => 'float',
        'disetujui_pada' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'harga_khusus_digunakan' => 'boolean',
        'harga_usulan' => 'float',
        'harga_penawaran_pelanggan' => 'float',
        'is_pengajuan_harga' => 'boolean',
    ];

    protected static array $tableColumnCache = [];

    public static function supportsDatabaseColumn(string $column): bool
    {
        return (new static())->hasDatabaseColumn($column);
    }

    public static function purchasePriceStatusStorageColumn(): ?string
    {
        foreach (['status_persetujuan_harga', 'status_pengajuan_harga', 'kode_nego'] as $column) {
            if (static::supportsDatabaseColumn($column)) {
                return $column;
            }
        }

        return null;
    }

    public static function purchasePriceAttributes(array $state = []): array
    {
        $model = new static();
        $payload = [];

        if (array_key_exists('requested_price', $state)) {
            $value = static::normalizeNullableFloat($state['requested_price']);
            if ($model->hasDatabaseColumn('harga_pengajuan')) {
                $payload['harga_pengajuan'] = $value;
            }

            if ($model->hasDatabaseColumn('harga_penawaran_pelanggan')) {
                $payload['harga_penawaran_pelanggan'] = $value;
            }
        }

        if (array_key_exists('final_price', $state)) {
            $value = static::normalizeNullableFloat($state['final_price']);
            if ($model->hasDatabaseColumn('harga_final')) {
                $payload['harga_final'] = $value;
            }

            if ($model->hasDatabaseColumn('harga_final_admin')) {
                $payload['harga_final_admin'] = $value;
            }

            if ($model->hasDatabaseColumn('harga_usulan')) {
                $payload['harga_usulan'] = $value;
            }
        }

        if (array_key_exists('status', $state)) {
            $status = static::normalizePurchasePriceStatus($state['status']);

            if ($model->hasDatabaseColumn('status_pengajuan_harga')) {
                $payload['status_pengajuan_harga'] = $status;
            }

            if ($model->hasDatabaseColumn('status_persetujuan_harga')) {
                $payload['status_persetujuan_harga'] = $status;
            }

            if ($model->hasDatabaseColumn('is_nego')) {
                $payload['is_nego'] = !is_null($status);
            }

            if ($model->hasDatabaseColumn('is_pengajuan_harga')) {
                $payload['is_pengajuan_harga'] = !is_null($status);
            }

            if ($model->hasDatabaseColumn('kode_nego')) {
                $payload['kode_nego'] = $status === static::PRICE_REQUEST_REJECTED ? 'rejected' : null;
            }
        }

        if (array_key_exists('customer_note', $state)) {
            $note = static::normalizeNullableString($state['customer_note']);
            if ($model->hasDatabaseColumn('catatan_pengajuan_harga')) {
                $payload['catatan_pengajuan_harga'] = $note;
            } elseif ($model->hasDatabaseColumn('service_admin_catatan')) {
                $payload['service_admin_catatan'] = $note;
            }
        }

        if (array_key_exists('admin_note', $state)) {
            $note = static::normalizeNullableString($state['admin_note']);
            if ($model->hasDatabaseColumn('catatan_admin_harga')) {
                $payload['catatan_admin_harga'] = $note;
            }
        }

        if (array_key_exists('approved_by', $state) && $model->hasDatabaseColumn('disetujui_oleh')) {
            $payload['disetujui_oleh'] = $state['approved_by'] ? (int) $state['approved_by'] : null;
        }

        if (array_key_exists('approved_at', $state) && $model->hasDatabaseColumn('disetujui_pada')) {
            $payload['disetujui_pada'] = $state['approved_at'];
        }

        if (array_key_exists('approved_by', $state) && $model->hasDatabaseColumn('approved_by')) {
            $payload['approved_by'] = $state['approved_by'] ? (int) $state['approved_by'] : null;
        }

        if (array_key_exists('approved_at', $state) && $model->hasDatabaseColumn('approved_at')) {
            $payload['approved_at'] = $state['approved_at'];
        }

        if (array_key_exists('rejected_by', $state) && $model->hasDatabaseColumn('rejected_by')) {
            $payload['rejected_by'] = $state['rejected_by'] ? (int) $state['rejected_by'] : null;
        }

        if (array_key_exists('rejected_at', $state) && $model->hasDatabaseColumn('rejected_at')) {
            $payload['rejected_at'] = $state['rejected_at'];
        }

        if (array_key_exists('normal_subtotal', $state) && $model->hasDatabaseColumn('harga_normal')) {
            $payload['harga_normal'] = static::normalizeNullableFloat($state['normal_subtotal']);
        }

        if (array_key_exists('discounted_total', $state) && $model->hasDatabaseColumn('harga_setelah_diskon')) {
            $payload['harga_setelah_diskon'] = static::normalizeNullableFloat($state['discounted_total']);
        }

        if (array_key_exists('initial_total', $state) && $model->hasDatabaseColumn('total_awal')) {
            $payload['total_awal'] = static::normalizeNullableFloat($state['initial_total']);
        }

        if (array_key_exists('used', $state)) {
            $used = (bool) $state['used'];

            if ($model->hasDatabaseColumn('harga_khusus_digunakan')) {
                $payload['harga_khusus_digunakan'] = $used;
            }

            if ($model->hasDatabaseColumn('kode_nego_terpakai_at')) {
                $payload['kode_nego_terpakai_at'] = $used
                    ? ($state['used_at'] ?? now())
                    : null;
            }
        } elseif (array_key_exists('used_at', $state) && $model->hasDatabaseColumn('kode_nego_terpakai_at')) {
            $payload['kode_nego_terpakai_at'] = $state['used_at'];
        }

        return $payload;
    }

    protected function hasDatabaseColumn(string $column): bool
    {
        $table = $this->getTable();

        if (!array_key_exists($table, static::$tableColumnCache)) {
            try {
                static::$tableColumnCache[$table] = Schema::getColumnListing($table);
            } catch (\Throwable) {
                static::$tableColumnCache[$table] = [];
            }
        }

        return in_array($column, static::$tableColumnCache[$table], true);
    }

    protected static function normalizeNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    protected static function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    public function scopeVisibleInAdminPesananMenu(Builder $query): Builder
    {
        if (!static::supportsDatabaseColumn('hidden_from_pesanan_at')) {
            return $query;
        }

        return $query->whereNull('hidden_from_pesanan_at');
    }

    public function isHiddenFromAdminPesananMenu(): bool
    {
        if (!$this->hasDatabaseColumn('hidden_from_pesanan_at')) {
            return false;
        }

        return !is_null($this->hidden_from_pesanan_at);
    }

    public function hideFromAdminPesananMenu(): bool
    {
        if (!$this->hasDatabaseColumn('hidden_from_pesanan_at')) {
            return false;
        }

        if ($this->isHiddenFromAdminPesananMenu()) {
            return true;
        }

        $this->forceFill([
            'hidden_from_pesanan_at' => now(),
        ]);

        return $this->save();
    }

    protected static function normalizePurchasePriceStatus(mixed $status): ?string
    {
        $normalized = trim(mb_strtolower((string) ($status ?? '')));

        return match ($normalized) {
            '', 'none', 'normal' => null,
            'pending', 'menunggu', 'menunggu persetujuan', 'request', 'requested' => static::PRICE_REQUEST_PENDING,
            'approved', 'approve', 'accepted', 'disetujui' => static::PRICE_REQUEST_APPROVED,
            'rejected', 'reject', 'ditolak' => static::PRICE_REQUEST_REJECTED,
            default => $normalized !== '' ? $normalized : null,
        };
    }

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
        if ($this->stok_dikurangi || $this->tipe !== 'produk' || (string) $this->status !== self::STATUS_SELESAI_FINAL) {
            return [];
        }

        $this->loadMissing(['details.produk', 'pelanggan']);
        $namaPelanggan = (string) ($this->pelanggan?->nama ?: 'Pelanggan tidak diketahui');
        $batchAllocations = [];

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

                    $qtyDariBatch = min((int) $batch->sisa_qty, (int) $jumlahDibutuhkan);
                    if ($qtyDariBatch <= 0) {
                        continue;
                    }

                    $batchAllocations[$detail->produk_id][] = [
                        'batch_id' => (int) $batch->id,
                        'qty' => $qtyDariBatch,
                        'tgl_produksi' => $batch->tgl_produksi?->toDateString()
                            ?: optional($this->tanggal)->toDateString()
                            ?: now()->toDateString(),
                        'tgl_expired' => $batch->tgl_expired?->toDateString()
                            ?: UnitApar::calculateExpiry(
                                optional($this->tanggal)->toDateString() ?: now()->toDateString(),
                                $produk->kapasitas ?? '-',
                                $produk->jenisApar?->nama ?? '-',
                            )->toDateString(),
                    ];

                    if ($batch->sisa_qty >= $qtyDariBatch) {
                        $batch->decrement('sisa_qty', $qtyDariBatch);
                    } else {
                        $batch->update(['sisa_qty' => 0]);
                    }

                    $jumlahDibutuhkan -= $qtyDariBatch;
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
                    keterangan: 'Pesanan Produk - ' . $namaPelanggan,
                    tanggal: now(),
                );
            }
        }

        $this->update(['stok_dikurangi' => true]);

        return $batchAllocations;
    }

    public function orderCode(): string
    {
        return 'TNTI' . $this->tanggal->format('dmY') . 'AJ' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    public function transactionDisplayName(): string
    {
        if ($this->isRefillOrder()) {
            return 'Refill APAR';
        }

        if ($this->isServiceOrder()) {
            return 'Service APAR';
        }

        return 'Pesanan Produk';
    }

    public function adminOrderTypeLabel(): string
    {
        if ($this->isRefillOrder()) {
            return 'Refill APAR';
        }

        if ($this->isServiceOrder()) {
            return 'Service APAR';
        }

        return 'Pembelian Unit';
    }

    public function adminDestroyTypeSlug(): string
    {
        if ($this->isRefillOrder()) {
            return 'refill-apar';
        }

        if ($this->isServiceOrder()) {
            return 'service-apar';
        }

        return 'pembelian-unit';
    }

    public function matchesAdminDestroyType(string $jenis): bool
    {
        return trim(mb_strtolower($jenis)) === $this->adminDestroyTypeSlug();
    }

    public function adminOrderTypeBadgeClasses(): string
    {
        if ($this->isRefillOrder()) {
            return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        }

        if ($this->isServiceOrder()) {
            return 'bg-violet-50 text-violet-700 border border-violet-200';
        }

        return 'bg-red-50 text-red-700 border border-red-200';
    }

    public function adminOrderUnitCount(): int
    {
        if ($this->isProductOrder()) {
            return (int) $this->details->sum('jumlah');
        }

        $serviceUnitCount = (int) ($this->service_jumlah_unit ?? 0);
        if ($serviceUnitCount > 0) {
            return $serviceUnitCount;
        }

        $display = $this->serviceUnitDisplay();
        return max(1, (int) ($display['quantity'] ?? 1));
    }

    public function adminOrderDetailTitle(): string
    {
        if ($this->isProductOrder()) {
            $firstProduk = $this->details->first();
            $title = $firstProduk?->produk?->nama ?? 'Pembelian Unit';
            $remainingProducts = max(0, $this->details->count() - 1);

            if ($remainingProducts > 0) {
                $title .= ' + ' . $remainingProducts . ' lainnya';
            }

            return $title;
        }

        if ($this->isRefillOrder()) {
            return $this->serviceJenisRefill?->nama_label ?: 'Refill APAR';
        }

        return $this->servicePaket?->nama ?: 'Service APAR';
    }

    public function adminOrderDetailMeta(): string
    {
        if ($this->isProductOrder()) {
            return $this->details->count() . ' item • ' . $this->adminOrderUnitCount() . ' unit';
        }

        if ($this->isRefillOrder()) {
            $parts = [$this->adminOrderUnitCount() . ' unit'];

            if ((float) ($this->service_total_kg ?? 0) > 0) {
                $parts[] = $this->formatCompactAdminNumber((float) $this->service_total_kg) . ' kg';
            }

            return implode(' • ', $parts);
        }

        return $this->adminOrderUnitCount() . ' unit';
    }

    public function adminStatusLabel(): string
    {
        $normalizedStatus = trim(strtolower(str_replace('_', ' ', (string) $this->status)));

        if ($this->isProductOrder() && $this->hasPendingPurchasePriceRequest()) {
            return 'Menunggu Persetujuan Harga';
        }

        if ($this->isProductOrder() && $this->hasApprovedPurchasePriceRequest() && empty($this->bukti_pembayaran)) {
            return 'Harga Disetujui / Siap Dibayar';
        }

        if ($this->isProductOrder() && $this->hasRejectedPurchasePriceRequest() && empty($this->bukti_pembayaran)) {
            return 'Pengajuan Ditolak / Harga Normal Aktif';
        }

        return match ($normalizedStatus) {
            self::STATUS_SELESAI_FINAL,
            self::STATUS_SELESAI => 'Selesai Final',
            self::STATUS_DITOLAK,
            'batal',
            'dibatalkan',
            'cancelled',
            'canceled' => 'Ditolak',
            self::STATUS_SIAP_DIKIRIM => 'Siap Dikirim',
            self::STATUS_DIKONFIRMASI_ADMIN => 'Dikonfirmasi Admin',
            self::STATUS_SELESAI_OLEH_TEKNISI => 'Selesai oleh Teknisi',
            self::STATUS_DIKERJAKAN_TEKNISI => 'Dikerjakan Teknisi',
            self::STATUS_DITUGASKAN_KE_TEKNISI => 'Ditugaskan ke Teknisi',
            self::STATUS_DIPROSES,
            self::STATUS_DISETUJUI => 'Diproses',
            self::STATUS_PENDING,
            self::STATUS_PERMINTAAN_MASUK,
            self::STATUS_DIREVIEW_ADMIN,
            self::STATUS_MENUNGGU_PENJADWALAN,
            self::STATUS_MENUNGGU_PENGAMBILAN,
            self::STATUS_MENUNGGU_KEDATANGAN_UNIT,
            self::STATUS_MENUNGGU_PERSETUJUAN_BIAYA,
            'menunggu persetujuan',
            'menunggu',
            'menunggu diproses admin' => 'Menunggu',
            default => ucwords($normalizedStatus ?: '-'),
        };
    }

    public function adminStatusBadgeClasses(): string
    {
        return match ($this->adminStatusLabel()) {
            'Selesai Final' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Menunggu', 'Menunggu Persetujuan Harga' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'Harga Disetujui / Siap Dibayar' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Pengajuan Ditolak / Harga Normal Aktif' => 'bg-red-50 text-red-700 border border-red-200',
            'Diproses' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'Ditugaskan ke Teknisi' => 'bg-purple-50 text-purple-700 border border-purple-200',
            'Dikerjakan Teknisi' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
            'Selesai oleh Teknisi', 'Dikonfirmasi Admin', 'Siap Dikirim' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
            'Ditolak' => 'bg-red-50 text-red-700 border border-red-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function displayTransactionAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->created_at) {
            return $this->created_at->copy()->timezone(config('app.timezone'));
        }

        return $this->tanggal?->copy()
            ->timezone(config('app.timezone'))
            ->startOfDay();
    }

    public function technicianTaskAt(): ?\Illuminate\Support\Carbon
    {
        $assignedAt = $this->getAttribute('assigned_at');
        if ($assignedAt instanceof \Illuminate\Support\Carbon) {
            return $assignedAt->copy()->timezone(config('app.timezone'));
        }

        if (!empty($assignedAt)) {
            return \Illuminate\Support\Carbon::parse($assignedAt)->timezone(config('app.timezone'));
        }

        if ($this->created_at) {
            return $this->created_at->copy()->timezone(config('app.timezone'));
        }

        if ($this->updated_at) {
            return $this->updated_at->copy()->timezone(config('app.timezone'));
        }

        return $this->tanggal?->copy()
            ->timezone(config('app.timezone'))
            ->startOfDay();
    }

    public function technicianTaskDateTime(string $format = 'd M Y, H:i'): string
    {
        return $this->technicianTaskAt()?->format($format) ?? '-';
    }

    public function displayTransactionDateTime(string $format = 'd M Y, H:i'): string
    {
        return $this->displayTransactionAt()?->format($format) ?? '-';
    }

    public function invoiceDisplayNumber(): string
    {
        return 'INV-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function invoiceTitle(): string
    {
        if ($this->isRefillOrder()) {
            return 'Invoice Refill APAR';
        }

        if ($this->isServiceOrder()) {
            return 'Invoice Service APAR';
        }

        return 'Invoice Pesanan Produk';
    }

    public function isPaymentConfirmed(): bool
    {
        if (!is_null($this->pembayaran_terkonfirmasi_at)) {
            return true;
        }

        if ($this->metode_pembayaran === 'cash'
            && in_array((string) $this->status, [
                self::STATUS_DIPROSES,
                self::STATUS_DITUGASKAN_KE_TEKNISI,
                self::STATUS_DIKERJAKAN_TEKNISI,
                self::STATUS_SELESAI_OLEH_TEKNISI,
                self::STATUS_DIKONFIRMASI_ADMIN,
                self::STATUS_SIAP_DIKIRIM,
                self::STATUS_SELESAI_FINAL,
                self::STATUS_SELESAI,
            ], true)
        ) {
            return true;
        }

        return !empty($this->bukti_pembayaran) && $this->status !== self::STATUS_PENDING;
    }

    public function payableTotal(): float
    {
        return (float) ($this->pricingSummary()['totalPembayaran'] ?? 0);
    }

    public function pricingSummary(): array
    {
        return app(OrderPricingService::class)->summarizePesanan($this);
    }

    public function hasPurchasePriceRequest(): bool
    {
        if (!$this->isProductOrder()) {
            return false;
        }

        return !is_null($this->purchasePriceRequestStatus())
            || !is_null($this->requestedPurchasePrice())
            || !is_null($this->approvedPurchaseFinalPrice())
            || (bool) ($this->harga_khusus_digunakan ?? false);
    }

    public function purchasePriceRequestStatus(): ?string
    {
        $statusMarker = static::normalizePurchasePriceStatus(
            $this->status_persetujuan_harga ?? $this->status_pengajuan_harga ?? null
        );
        if (in_array($statusMarker, [
            static::PRICE_REQUEST_PENDING,
            static::PRICE_REQUEST_APPROVED,
            static::PRICE_REQUEST_REJECTED,
        ], true)) {
            return $statusMarker;
        }

        if ((float) ($this->harga_final_admin ?? 0) > 0 || (float) ($this->harga_final ?? 0) > 0 || (float) ($this->harga_usulan ?? 0) > 0 || (bool) ($this->harga_khusus_digunakan ?? false)) {
            return self::PRICE_REQUEST_APPROVED;
        }

        $legacyStatusMarker = static::normalizePurchasePriceStatus($this->kode_nego ?? null);
        if ($legacyStatusMarker === self::PRICE_REQUEST_REJECTED) {
            return self::PRICE_REQUEST_REJECTED;
        }

        if ($legacyStatusMarker === self::PRICE_REQUEST_PENDING) {
            return self::PRICE_REQUEST_PENDING;
        }

        if ((string) $this->status === static::STATUS_MENUNGGU_PERSETUJUAN_HARGA) {
            return self::PRICE_REQUEST_PENDING;
        }

        if (!is_null($this->requestedPurchasePrice()) || (bool) ($this->is_nego ?? false) || (bool) ($this->is_pengajuan_harga ?? false)) {
            return self::PRICE_REQUEST_PENDING;
        }

        return null;
    }

    public function hasPendingPurchasePriceRequest(): bool
    {
        return $this->purchasePriceRequestStatus() === self::PRICE_REQUEST_PENDING;
    }

    public function hasApprovedPurchasePriceRequest(): bool
    {
        return $this->purchasePriceRequestStatus() === self::PRICE_REQUEST_APPROVED;
    }

    public function hasRejectedPurchasePriceRequest(): bool
    {
        return $this->purchasePriceRequestStatus() === self::PRICE_REQUEST_REJECTED;
    }

    public function purchasePriceStatusLabel(): ?string
    {
        return match ($this->purchasePriceRequestStatus()) {
            self::PRICE_REQUEST_PENDING => 'Menunggu Persetujuan Harga',
            self::PRICE_REQUEST_APPROVED => 'Harga Disetujui',
            self::PRICE_REQUEST_REJECTED => 'Pengajuan Ditolak',
            default => null,
        };
    }

    public function purchasePriceStatusClasses(): string
    {
        return match ($this->purchasePriceRequestStatus()) {
            self::PRICE_REQUEST_PENDING => 'bg-amber-100 text-amber-800 border border-amber-200',
            self::PRICE_REQUEST_APPROVED => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            self::PRICE_REQUEST_REJECTED => 'bg-red-100 text-red-800 border border-red-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function requestedPurchasePrice(): ?float
    {
        foreach (['harga_pengajuan', 'harga_penawaran_pelanggan'] as $attribute) {
            $value = (float) ($this->{$attribute} ?? 0);
            if ($value > 0) {
                return $value;
            }
        }

        return null;
    }

    public function approvedPurchaseFinalPrice(): ?float
    {
        foreach (['harga_final_admin', 'harga_final', 'harga_usulan'] as $attribute) {
            $value = (float) ($this->{$attribute} ?? 0);
            if ($value > 0) {
                return $value;
            }
        }

        if ($this->hasApprovedPurchasePriceRequest()) {
            return $this->requestedPurchasePrice();
        }

        return null;
    }

    public function purchasePriceAdminNote(): ?string
    {
        foreach (['catatan_admin_harga', 'catatan_admin'] as $attribute) {
            $note = trim((string) ($this->{$attribute} ?? ''));
            if ($note !== '') {
                return $note;
            }
        }

        return null;
    }

    public function purchasePriceNormalSubtotal(): float
    {
        $stored = (float) ($this->harga_normal ?? 0);
        if ($stored > 0) {
            return $stored;
        }

        return (float) ($this->pricingSummary()['subtotalProduk'] ?? 0);
    }

    public function purchasePriceDiscountedTotal(): float
    {
        $stored = (float) ($this->harga_setelah_diskon ?? 0);
        if ($stored > 0) {
            return $stored;
        }

        return (float) ($this->pricingSummary()['totalSetelahPromo'] ?? 0);
    }

    public function purchasePriceInitialTotal(): float
    {
        $stored = (float) ($this->total_awal ?? 0);
        if ($stored > 0) {
            return $stored;
        }

        return (float) ($this->pricingSummary()['normalTotalPembayaran'] ?? 0);
    }

    public function hasResolvedPurchasePriceDecision(): bool
    {
        return $this->hasApprovedPurchasePriceRequest() || $this->hasRejectedPurchasePriceRequest();
    }

    public function purchasePriceCustomerNote(): ?string
    {
        if (!$this->isProductOrder()) {
            return null;
        }

        foreach (['catatan_pengajuan_harga', 'service_admin_catatan'] as $attribute) {
            $note = trim((string) ($this->{$attribute} ?? ''));
            if ($note !== '') {
                return $note;
            }
        }

        return null;
    }

    public function isCompleted(): bool
    {
        return in_array((string) $this->status, [
            self::STATUS_SELESAI,
            self::STATUS_SELESAI_FINAL,
        ], true);
    }

    public function isFinalCompleted(): bool
    {
        return $this->isCompleted();
    }

    public function isAdminFinalized(): bool
    {
        return (string) $this->status === self::STATUS_SELESAI_FINAL;
    }

    public function isAwaitingCustomerConfirmation(): bool
    {
        return (string) $this->status === self::STATUS_SIAP_DIKIRIM;
    }

    public function isLockedForAdminDeletion(): bool
    {
        return in_array((string) $this->status, [
            self::STATUS_SELESAI,
            self::STATUS_SELESAI_FINAL,
        ], true);
    }

    public function canGiveReview(): bool
    {
        return $this->canSubmitCustomerReview();
    }

    public function canViewInvoice(): bool
    {
        return $this->hasPurchasePriceRequest()
            || $this->isPaymentConfirmed()
            || !empty($this->bukti_pembayaran)
            || (float) ($this->total_harga ?: $this->total ?: $this->service_estimasi_biaya ?: 0) > 0
            || $this->isCompleted()
            || $this->isLegacyAdminSource();
    }

    public function hasCustomerConfirmed(): bool
    {
        return !is_null($this->customer_confirmed_at);
    }

    public function hasSubmittedTestimonial(): bool
    {
        return !is_null($this->testimonial_submitted_at);
    }

    public function hasLinkedTestimoni(): bool
    {
        return !is_null($this->getAttribute('linkedTestimoni'))
            || !empty($this->getAttribute('linked_testimoni_id'));
    }

    public function canCustomerConfirmReceived(): bool
    {
        return ($this->isAwaitingCustomerConfirmation() || $this->isAdminFinalized())
            && !$this->hasCustomerConfirmed()
            && !$this->hasSubmittedTestimonial()
            && !$this->hasLinkedTestimoni();
    }

    public function canSubmitCustomerReview(): bool
    {
        return $this->isAdminFinalized()
            && $this->hasCustomerConfirmed()
            && !$this->hasSubmittedTestimonial()
            && !$this->hasLinkedTestimoni();
    }

    public function requiresCustomerDeliveryConfirmation(): bool
    {
        if ($this->isProductOrder()) {
            return (string) $this->metode_pengiriman === 'diantar_internal';
        }

        if ($this->tipe === 'service') {
            return (string) $this->service_metode_penanganan !== 'antar sendiri';
        }

        return false;
    }

    public function canMarkReadyToShip(): bool
    {
        return $this->requiresCustomerDeliveryConfirmation()
            && in_array((string) $this->status, [
                self::STATUS_SELESAI_OLEH_TEKNISI,
                self::STATUS_DIKONFIRMASI_ADMIN,
            ], true);
    }

    public function canFinalizeDirectlyByAdmin(): bool
    {
        return !$this->requiresCustomerDeliveryConfirmation()
            && in_array((string) $this->status, [
                self::STATUS_SELESAI_OLEH_TEKNISI,
                self::STATUS_DIKONFIRMASI_ADMIN,
            ], true);
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

    public static function legacySourceValues(): array
    {
        return [
            'datang_langsung',
            'offline',
            'input_admin',
            'telepon',
            'whatsapp',
            'data_lama',
        ];
    }

    public static function isLegacySourceValue(?string $source): bool
    {
        return in_array((string) $source, static::legacySourceValues(), true);
    }

    public function isLegacyAdminSource(): bool
    {
        return static::isLegacySourceValue($this->sumber_pesanan);
    }

    public function adminSourceLabel(): string
    {
        return $this->isLegacyAdminSource()
            ? 'Riwayat Lama'
            : 'Transaksi Pelanggan';
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

        return app(ServicePackagePricingService::class)->resolveEstimatedPeralatan(
            $this->servicePaket,
            max(1, (int) ($this->service_jumlah_unit ?? 1)),
        );
    }

    public function servicePricingBreakdown(): array
    {
        if (!$this->isServiceOrder()) {
            return [];
        }

        $parsed = collect(preg_split('/\r\n|\r|\n/', (string) $this->service_keluhan))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(function (string $line) {
                if (!preg_match('/^\d+\.\s*(.+?)\s+-\s+Rp\s*([\d\.]+)/u', $line, $matches)) {
                    return null;
                }

                $label = trim((string) $matches[1]);
                $total = (float) str_replace('.', '', (string) $matches[2]);
                $qty = 1;

                if (preg_match('/\sx\s*(\d+)\s*unit$/ui', $label, $qtyMatch)) {
                    $qty = max(1, (int) ($qtyMatch[1] ?? 1));
                    $label = trim((string) preg_replace('/\sx\s*\d+\s*unit$/ui', '', $label));
                }

                return [
                    'label' => $label,
                    'qty' => $qty,
                    'unit_price' => $qty > 0 ? round($total / $qty, 0) : $total,
                    'total' => $total,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (!empty($parsed)) {
            return $parsed;
        }

        $qty = max(1, (int) ($this->service_jumlah_unit ?? 1));
        $total = (float) ($this->service_estimasi_biaya ?: $this->total_harga ?: $this->total ?: 0);
        $labelParts = array_filter([
            $this->service_jenis_apar ? 'APAR ' . $this->service_jenis_apar : 'APAR',
            $this->service_ukuran_apar ?: null,
        ]);

        return [[
            'label' => implode(' ', $labelParts),
            'qty' => $qty,
            'unit_price' => $qty > 0 ? round($total / $qty, 0) : $total,
            'total' => $total,
        ]];
    }

    public function serviceCustomerNote(): string
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $this->service_keluhan))
            ->map(fn ($line) => trim((string) $line))
            ->filter();

        $customerNoteLine = $lines->first(fn (string $line) => str_starts_with(mb_strtolower($line), 'catatan pelanggan:'));

        if ($customerNoteLine) {
            return trim((string) preg_replace('/^Catatan Pelanggan:\s*/iu', '', $customerNoteLine));
        }

        return trim((string) $this->service_keluhan);
    }

    public function serviceUnitDisplay(): array
    {
        return ServiceUnitDisplay::forPesanan($this);
    }

    public function technicianStatusLabel(): string
    {
        return match ((string) $this->status) {
            self::STATUS_DITUGASKAN_KE_TEKNISI => 'Ditugaskan ke Teknisi',
            self::STATUS_DIKERJAKAN_TEKNISI => 'Dikerjakan Teknisi',
            self::STATUS_SELESAI_OLEH_TEKNISI,
            self::STATUS_DIKONFIRMASI_ADMIN,
            self::STATUS_SIAP_DIKIRIM,
            self::STATUS_SELESAI,
            self::STATUS_SELESAI_FINAL => 'Selesai',
            default => $this->publicStatusLabel(),
        };
    }

    public function servicePeralatanItems(): array
    {
        return $this->service?->effective_peralatan ?: $this->estimatedServicePeralatan();
    }

    public function serviceLogPayload(array $overrides = []): array
    {
        $this->loadMissing('servicePaket.peralatans');

        return array_merge([
            'service_paket_id' => $this->service_paket_id,
            'jenis_service' => $this->servicePaket?->nama ?? 'Service APAR',
            'rincian_layanan' => $this->servicePaket?->rincian_layanan,
            'tgl_service' => $this->resolvedOperationalDate(),
            'biaya' => (float) ($this->service_estimasi_biaya ?: $this->total_harga ?: $this->total ?: 0),
            'estimasi_peralatan_json' => json_encode($this->estimatedServicePeralatan()),
            'keterangan' => $this->service_keluhan ?: $this->keterangan,
        ], $overrides);
    }

    public function resolvedOperationalDate(): string
    {
        if ($this->teknisi_selesai_at) {
            return $this->teknisi_selesai_at->copy()->timezone(config('app.timezone'))->toDateString();
        }

        if ($this->service?->tgl_service) {
            return $this->service->tgl_service->copy()->timezone(config('app.timezone'))->toDateString();
        }

        if ($this->service?->tgl_selesai_admin) {
            return $this->service->tgl_selesai_admin->copy()->timezone(config('app.timezone'))->toDateString();
        }

        return optional($this->tanggal)->toDateString() ?? now()->toDateString();
    }

    public function publicStatusLabel(): string
    {
        $status = (string) $this->status;

        if ($status === self::STATUS_DITOLAK) {
            return 'Ditolak';
        }

        if ($this->isProductOrder() && $this->hasPendingPurchasePriceRequest()) {
            return 'Menunggu Persetujuan Harga';
        }

        if ($this->isProductOrder() && $this->hasApprovedPurchasePriceRequest()) {
            if (empty($this->bukti_pembayaran) && $this->payableTotal() > 0) {
                return 'Harga Disetujui - Siap Dibayar';
            }

            if (!empty($this->bukti_pembayaran) && !$this->isPaymentConfirmed()) {
                return 'Menunggu Verifikasi Pembayaran';
            }
        }

        if ($this->isProductOrder() && $this->hasRejectedPurchasePriceRequest()) {
            if (empty($this->bukti_pembayaran) && $this->payableTotal() > 0) {
                return 'Pengajuan Ditolak - Harga Normal Aktif';
            }

            if (!empty($this->bukti_pembayaran) && !$this->isPaymentConfirmed()) {
                return 'Menunggu Verifikasi Pembayaran';
            }
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
                self::STATUS_DIKONFIRMASI_ADMIN => 'Dikonfirmasi Admin',
                self::STATUS_SIAP_DIKIRIM => 'Siap Dikirim',
                self::STATUS_SELESAI,
                self::STATUS_SELESAI_FINAL => 'Selesai Final',
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
                self::STATUS_DIPROSES => 'Diproses',
                self::STATUS_SELESAI_OLEH_TEKNISI => 'Selesai oleh Teknisi',
                self::STATUS_DIKONFIRMASI_ADMIN => 'Dikonfirmasi Admin',
                self::STATUS_SIAP_DIKIRIM => 'Siap Dikirim',
                self::STATUS_SELESAI,
                self::STATUS_SELESAI_FINAL => 'Selesai Final',
                default => ucwords($status),
            };
        }

        return match ($status) {
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_SELESAI_OLEH_TEKNISI => 'Selesai oleh Teknisi',
            self::STATUS_DIKONFIRMASI_ADMIN => 'Dikonfirmasi Admin',
            self::STATUS_SIAP_DIKIRIM => 'Siap Dikirim',
            self::STATUS_SELESAI,
            self::STATUS_SELESAI_FINAL => 'Selesai Final',
            default => ucwords($status),
        };
    }

    public function publicStatusClasses(): string
    {
        $label = $this->publicStatusLabel();

        return match ($label) {
            'Menunggu Persetujuan Harga' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'Harga Disetujui - Siap Dibayar' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'Pengajuan Ditolak - Harga Normal Aktif' => 'bg-red-100 text-red-800 border border-red-200',
            'Menunggu Pembayaran', 'Menunggu Verifikasi Pembayaran' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'Menunggu Diproses', 'Ditugaskan ke Teknisi' => 'bg-blue-100 text-blue-800 border border-blue-200',
            'Sedang Dikerjakan' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
            'Selesai oleh Teknisi', 'Dikonfirmasi Admin', 'Siap Dikirim' => 'bg-cyan-100 text-cyan-800 border border-cyan-200',
            'Selesai Final', 'Selesai' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'Ditolak' => 'bg-red-100 text-red-800 border border-red-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function shouldHidePaymentStatusBadge(): bool
    {
        return $this->adminStatusLabel() === 'Selesai Final';
    }

    private function formatCompactAdminNumber(float $value): string
    {
        if (fmod($value, 1.0) === 0.0) {
            return number_format($value, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    }

    public function getTimelineStep(): int
    {
        $status = (string) $this->status;

        if ($status === self::STATUS_DITOLAK) {
            return 0;
        }

        if ($this->hasPendingPurchasePriceRequest()) {
            return 1;
        }

        if ($this->canPay()) {
            return 1;
        }

        if (in_array($status, [self::STATUS_SELESAI, self::STATUS_SELESAI_FINAL], true)) {
            return $this->tipe === 'service' ? 7 : 6;
        }

        if ($status === self::STATUS_SIAP_DIKIRIM) {
            return $this->tipe === 'service' ? 6 : 4;
        }

        if ($status === self::STATUS_DIKERJAKAN_TEKNISI) {
            return 4;
        }

        if (in_array($status, [self::STATUS_SELESAI_OLEH_TEKNISI, self::STATUS_DIKONFIRMASI_ADMIN], true)) {
            return $this->tipe === 'service' ? 5 : 3;
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
            $unitDisplay = $this->serviceUnitDisplay();
            $jenisApar = trim((string) ($this->service_jenis_apar ?? ''));
            if ($jenisApar === '' && $this->isRefillOrder()) {
                $jenisApar = trim((string) ($this->serviceJenisRefill?->nama_label ?? ''));
            }

            return [
                'jumlah' => (int) ($unitDisplay['quantity'] ?? $this->service_jumlah_unit ?? 0),
                'ukuran' => $this->service_ukuran_apar ?? '-',
                'jenis' => $jenisApar !== '' ? $jenisApar : ($unitDisplay['detail_label'] ?? '-'),
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
        if ($this->hasPendingPurchasePriceRequest()) {
            return false;
        }

        return in_array((string) $this->status, self::PAYMENT_PENDING_STATUSES, true)
            && empty($this->bukti_pembayaran)
            && $this->payableTotal() > 0;
    }

    public function canUploadPaymentProof(): bool
    {
        return $this->canPay();
    }

    public function needsPickup(): bool
    {
        return $this->tipe === 'service'
            && $this->service_metode_penanganan === 'antar sendiri'
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
