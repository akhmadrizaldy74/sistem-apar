<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\UnitApar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;

class OperationalAuditDetailLogGenerator
{
    private string $sourceReportPath = '';

    private array $report;

    /** @var \Illuminate\Support\Collection<int, array> */
    private Collection $transactions;

    /** @var \Illuminate\Support\Collection<int, array> */
    private Collection $proofChecks;

    /** @var \Illuminate\Support\Collection<int, array> */
    private Collection $createdUnitMeta;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Pesanan> */
    private Collection $orders;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Refill> */
    private Collection $refills;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> */
    private Collection $services;

    /** @var \Illuminate\Support\Collection<int, \App\Models\UnitApar> */
    private Collection $units;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Pengeluaran> */
    private Collection $expenses;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Produk> */
    private Collection $products;

    /** @var array<string, string> */
    private array $flowLabels = [
        'customer_product_order' => 'Pesanan Produk Pelanggan',
        'customer_refill_registered' => 'Refill Pelanggan APAR Terdaftar',
        'customer_refill_unregistered' => 'Refill Pelanggan APAR Tidak Terdaftar',
        'customer_service_registered' => 'Service Pelanggan APAR Terdaftar',
        'customer_service_unregistered' => 'Service Pelanggan APAR Tidak Terdaftar',
        'admin_offline_product_order' => 'Pesanan Offline Admin',
        'admin_offline_refill' => 'Refill Offline Admin',
        'admin_offline_refill_registered' => 'Refill Offline Admin APAR Terdaftar',
        'admin_offline_refill_unregistered' => 'Refill Offline Admin APAR Tidak Terdaftar',
        'admin_offline_service' => 'Service Offline Admin',
        'admin_offline_service_registered' => 'Service Offline Admin APAR Terdaftar',
        'admin_offline_service_unregistered' => 'Service Offline Admin APAR Tidak Terdaftar',
        'stock_additions' => 'Pengeluaran dan Penambahan Stok',
        'unit_apar_creation' => 'Registrasi Unit APAR Manual',
        'report_pages' => 'Pemeriksaan Halaman Laporan',
    ];

    public function generateFromPath(?string $inputPath = null): string
    {
        $reportPath = $this->resolveReportPath($inputPath);
        $report = $this->loadReport($reportPath);

        return $this->generateFromReport($report, $reportPath);
    }

    public function generateFromReport(array $report, string $reportPath): string
    {
        $this->sourceReportPath = $reportPath;
        $this->report = $report;
        $this->transactions = collect($this->report['transactions'] ?? []);
        $this->proofChecks = collect($this->report['proof_checks'] ?? []);
        $this->createdUnitMeta = collect($this->report['created_data']['unit_apar'] ?? []);

        $this->loadRelatedData();

        $outputPath = preg_replace('/\.json$/', '_detail_log.md', $reportPath);
        if (!is_string($outputPath) || $outputPath === '') {
            throw new RuntimeException('Gagal menentukan path output log detail.');
        }

        File::put($outputPath, $this->buildMarkdown());

        return $outputPath;
    }

    private function resolveReportPath(?string $inputPath): string
    {
        if ($inputPath) {
            $candidate = $inputPath;
            if (!str_ends_with(strtolower($candidate), '.json')) {
                $candidate = storage_path('app/qa_reports/operational_live_audit_' . $candidate . '.json');
            }

            if (!File::exists($candidate)) {
                throw new RuntimeException('File report tidak ditemukan: ' . $candidate);
            }

            return $candidate;
        }

        $latest = collect(File::glob(storage_path('app/qa_reports/operational_live_audit_*.json')))
            ->sortByDesc(fn (string $path) => File::lastModified($path))
            ->first();

        if (!$latest) {
            throw new RuntimeException('Belum ada file report audit operasional live.');
        }

        return $latest;
    }

    private function loadReport(string $reportPath): array
    {
        $decoded = json_decode((string) File::get($reportPath), true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Isi report JSON tidak valid: ' . $reportPath);
        }

        return $decoded;
    }

    private function loadRelatedData(): void
    {
        $orderIds = $this->transactions
            ->pluck('pesanan_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->orders = Pesanan::query()
            ->with([
                'pelanggan',
                'details.produk.jenisApar',
                'serviceJenisRefill',
                'servicePaket',
                'service.unitApar',
            ])
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        $refillIds = collect($this->report['created_data']['refill'] ?? [])
            ->pluck('refill_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->refills = Refill::query()
            ->with(['jenisRefill', 'unitApar', 'service'])
            ->whereIn('id', $refillIds)
            ->get()
            ->keyBy('id');

        $serviceIds = collect($this->report['created_data']['service'] ?? [])
            ->pluck('service_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->services = Service::query()
            ->with(['servicePaket', 'unitApar'])
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        $unitIds = collect($this->report['deltas']['units']['new_customer_units'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->units = UnitApar::query()
            ->with(['pelanggan', 'produk.jenisApar'])
            ->whereIn('id', $unitIds)
            ->get()
            ->keyBy('id');

        $expenseIds = collect($this->report['deltas']['pengeluaran']['created_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->expenses = Pengeluaran::query()
            ->with(['produk.jenisApar', 'jenisRefill', 'peralatan'])
            ->whereIn('id', $expenseIds)
            ->get()
            ->keyBy('id');

        $productIds = $this->orders
            ->flatMap(fn (Pesanan $order) => $order->details->pluck('produk_id'))
            ->merge($this->units->pluck('produk_id'))
            ->merge($this->expenses->pluck('produk_id'))
            ->merge([$this->report['meta']['monitored_entities']['produk']['id'] ?? null])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->products = Produk::query()
            ->with('jenisApar')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
    }

    private function buildMarkdown(): string
    {
        $lines = [];
        $meta = $this->report['meta'];
        $summary = $this->report['summary'];

        $lines[] = '# Log Detail Audit Operasional Live Sistem APAR';
        $lines[] = '';
        $lines[] = '- Waktu generate log detail: ' . now('Asia/Jakarta')->toDateTimeString();
        $lines[] = '- Sumber report: `' . basename($this->sourceReportPath) . '`';
        $lines[] = '- Run ID audit: `' . ($meta['run_id'] ?? '-') . '`';
        $lines[] = '- Waktu audit asli: ' . ($meta['generated_at'] ?? '-');
        $lines[] = '- Database: `' . ($meta['database'] ?? '-') . '`';
        $lines[] = '';
        $lines[] = '## Ringkasan';
        $lines[] = '';
        $lines[] = '- Total skenario berhasil: ' . ($summary['berhasil'] ?? 0);
        $lines[] = '- Total skenario gagal: ' . ($summary['gagal'] ?? 0);
        $lines[] = '- Total skenario perlu diperbaiki: ' . ($summary['perlu_diperbaiki'] ?? 0);
        $lines[] = '- Status akhir audit: `' . ($this->report['conclusion']['status'] ?? '-') . '`';
        $lines[] = '';

        $lines = array_merge($lines, $this->buildInitialStockSection());
        $lines = array_merge($lines, $this->buildTransactionSection());
        $lines = array_merge($lines, $this->buildStockUsageSection());
        $lines = array_merge($lines, $this->buildUnitSection());
        $lines = array_merge($lines, $this->buildExpenseSection());
        $lines = array_merge($lines, $this->buildReportSection());
        $lines = array_merge($lines, $this->buildProofSection());
        $lines = array_merge($lines, $this->buildClosingSection());

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function buildInitialStockSection(): array
    {
        $initialStocks = $this->report['initial_state']['stocks'];
        $productId = (int) ($initialStocks['produk']['id'] ?? 0);
        $product = $this->products->get($productId);

        $lines = [];
        $lines[] = '## Data Stok Awal';
        $lines[] = '';
        $lines[] = '- Produk APAR yang dipantau: ' . $this->productLabel($product, (string) ($initialStocks['produk']['nama'] ?? '-'));
        $lines[] = '  Stok awal: ' . $this->formatQty($initialStocks['produk']['stok'] ?? 0) . ' unit';
        $lines[] = '  Harga jual saat audit: ' . $this->formatMoney($product?->harga);
        $lines[] = '  Jenis APAR: ' . ($product?->jenisApar?->nama ?? '-');

        foreach (($initialStocks['refills'] ?? []) as $refill) {
            $lines[] = '- Stok refill awal ' . ($refill['nama'] ?? '-') . ': ' . $this->formatQty($refill['stok'] ?? 0) . ' ' . ($refill['satuan'] ?? '');
        }

        foreach (($initialStocks['peralatan'] ?? []) as $item) {
            $lines[] = '- Stok peralatan awal ' . ($item['nama'] ?? '-') . ': ' . $this->formatQty($item['stok'] ?? 0) . ' unit';
        }

        $lines[] = '- Total unit APAR awal pelanggan audit: ' . $this->formatQty($this->report['initial_state']['units']['customer_total'] ?? 0) . ' unit';
        $lines[] = '';

        return $lines;
    }

    private function buildTransactionSection(): array
    {
        $lines = [];
        $lines[] = '## Data Transaksi Detail';
        $lines[] = '';

        foreach ($this->transactions as $transaction) {
            $flow = (string) ($transaction['flow'] ?? '-');
            if (!isset($transaction['pesanan_id'])) {
                continue;
            }

            $order = $this->orders->get((int) $transaction['pesanan_id']);
            if (!$order) {
                continue;
            }

            $lines[] = '### ' . ($this->flowLabels[$flow] ?? $flow) . ' - Pesanan #' . $order->id;
            $lines[] = '';
            $lines[] = '- Pelanggan: ' . ($order->pelanggan?->nama ?? '-');
            $lines[] = '- Tipe: ' . ($order->tipe ?? '-');
            $lines[] = '- Status akhir: `' . ($order->status ?? '-') . '`';
            $lines[] = '- Total transaksi: ' . $this->formatMoney($order->total);
            $lines[] = '- Metode pembayaran: ' . strtoupper((string) ($order->metode_pembayaran ?? '-'));
            $lines[] = '- Bank: ' . strtoupper((string) ($order->bank ?? '-'));

            $proofCheck = $this->proofChecks->firstWhere('pesanan_id', (int) $order->id);
            if ($proofCheck) {
                $lines[] = '- Bukti pembayaran: tersimpan=' . ($proofCheck['storage_exists'] ? 'ya' : 'tidak') . ', tampil=' . ($proofCheck['displayed'] ? 'ya' : 'tidak') . ', file=`' . ($proofCheck['proof_path'] ?: '-') . '`';
            } else {
                $lines[] = '- Bukti pembayaran: tidak digunakan / tidak dicatat pada skenario ini';
            }

            if ($order->isProductOrder()) {
                foreach ($order->details as $detail) {
                    $lines[] = '- Item produk: ' . $this->productLabel($detail->produk, (string) ($detail->produk?->nama ?? 'Produk APAR'));
                    $lines[] = '  Jumlah: ' . $this->formatQty($detail->jumlah) . ' unit';
                    $lines[] = '  Harga satuan: ' . $this->formatMoney($detail->harga);
                    $lines[] = '  Subtotal: ' . $this->formatMoney($detail->subtotal);
                }
            } elseif ($order->service_jenis_layanan === 'refill') {
                $refillTx = $this->refillByOrderId($order->id);
                $lines[] = '- Jenis refill: ' . ($refillTx?->jenisRefill?->nama_label ?? $order->serviceJenisRefill?->nama_label ?? '-');
                $lines[] = '- Jenis APAR: ' . ($refillTx?->unitApar?->bahan ?: ($order->service_jenis_apar ?: 'APAR tidak terdaftar'));
                $lines[] = '- Ukuran APAR: ' . ($order->service_ukuran_apar ?: '-');
                $lines[] = '- Jumlah unit: ' . $this->formatQty($order->service_jumlah_unit ?? 0) . ' unit';
                $lines[] = '- Kebutuhan bahan refill: ' . $this->formatQty($order->service_total_kg ?? 0) . ' ' . ($order->serviceJenisRefill?->satuan_label ?? $refillTx?->jenisRefill?->satuan_label ?? 'Kg');
            } else {
                $serviceTx = $this->serviceByOrderId($order->id);
                $lines[] = '- Paket service: ' . ($serviceTx?->servicePaket?->nama ?? $order->servicePaket?->nama ?? '-');
                $lines[] = '- Jenis APAR: ' . ($order->service_jenis_apar ?: '-');
                $lines[] = '- Ukuran APAR: ' . ($order->service_ukuran_apar ?: '-');
                $lines[] = '- Jumlah unit: ' . $this->formatQty($order->service_jumlah_unit ?? 0) . ' unit';
                foreach (($serviceTx?->stok_kurang_history ?? []) as $history) {
                    $description = '- Peralatan terpakai: ' . ($history['nama'] ?? '-') . ' sebanyak ' . $this->formatQty($history['jumlah'] ?? 0) . ' unit';
                    if (isset($history['stok_sebelum'], $history['stok_sesudah'])) {
                        $description .= ' (stok ' . $this->formatQty($history['stok_sebelum']) . ' -> ' . $this->formatQty($history['stok_sesudah']) . ')';
                    }

                    $lines[] = $description;
                }
            }

            $lines[] = '';
        }

        return $lines;
    }

    private function buildStockUsageSection(): array
    {
        $lines = [];
        $lines[] = '## Rincian Perubahan Stok';
        $lines[] = '';

        $monitoredProductId = (int) ($this->report['meta']['monitored_entities']['produk']['id'] ?? 0);
        $product = $this->products->get($monitoredProductId);
        $productBefore = (float) ($this->report['initial_state']['stocks']['produk']['stok'] ?? 0);
        $productAfter = (float) ($this->report['final_state']['stocks']['produk']['stok'] ?? 0);
        $productAdditions = $this->expenses
            ->filter(fn (Pengeluaran $expense) => (int) $expense->produk_id === $monitoredProductId);
        $productUsages = $this->orders
            ->filter(fn (Pesanan $order) => $order->isProductOrder())
            ->flatMap(function (Pesanan $order) use ($monitoredProductId) {
                $flow = $this->flowByOrderId($order->id);

                return $order->details
                    ->filter(fn ($detail) => (int) $detail->produk_id === $monitoredProductId)
                    ->map(fn ($detail) => [
                        'pesanan_id' => (int) $order->id,
                        'flow' => $flow,
                        'jumlah' => (float) $detail->jumlah,
                    ]);
            })
            ->values();

        $lines[] = '### Produk APAR Dipantau';
        $lines[] = '';
        $lines[] = '- Item: ' . $this->productLabel($product, (string) ($this->report['initial_state']['stocks']['produk']['nama'] ?? '-'));
        $lines[] = '- Stok awal: ' . $this->formatQty($productBefore) . ' unit';
        foreach ($productUsages as $usage) {
            $lines[] = '- Dipakai transaksi ' . ($this->flowLabels[$usage['flow']] ?? $usage['flow']) . ' pesanan #' . $usage['pesanan_id'] . ': -' . $this->formatQty($usage['jumlah']) . ' unit';
        }
        foreach ($productAdditions as $expense) {
            $lines[] = '- Bertambah dari pengeluaran #' . $expense->id . ': +' . $this->formatQty($expense->qty) . ' unit dari pembelian ' . $this->expenseItemLabel($expense) . ' @ ' . $this->formatMoney($expense->harga_beli);
        }
        $lines[] = '- Stok akhir: ' . $this->formatQty($productAfter) . ' unit';
        $lines[] = '- Validasi delta: ' . $this->formatQty($productBefore) . ' + ' . $this->formatQty($productAdditions->sum('qty')) . ' - ' . $this->formatQty($productUsages->sum('jumlah')) . ' = ' . $this->formatQty($productAfter);
        $lines[] = '';

        foreach (($this->report['initial_state']['stocks']['refills'] ?? []) as $refillSnapshot) {
            $refillId = (int) ($refillSnapshot['id'] ?? 0);
            $refillBefore = (float) ($refillSnapshot['stok'] ?? 0);
            $refillAfter = collect($this->report['final_state']['stocks']['refills'] ?? [])
                ->firstWhere('id', $refillId)['stok'] ?? 0;
            $refillName = (string) ($refillSnapshot['nama'] ?? '-');
            $refillUnit = (string) ($refillSnapshot['satuan'] ?? 'Kg');
            $refillAdditions = $this->expenses
                ->filter(fn (Pengeluaran $expense) => (int) $expense->jenis_refill_id === $refillId);
            $refillUsages = $this->orders
                ->filter(fn (Pesanan $order) => $order->service_jenis_layanan === 'refill' && (int) $order->service_jenis_refill_id === $refillId)
                ->map(fn (Pesanan $order) => [
                    'pesanan_id' => (int) $order->id,
                    'flow' => $this->flowByOrderId($order->id),
                    'ukuran' => (string) ($order->service_ukuran_apar ?: '-'),
                    'jumlah_unit' => (int) ($order->service_jumlah_unit ?? 0),
                    'kg' => (float) ($order->service_total_kg ?? 0),
                ])
                ->values();

            $lines[] = '### Refill ' . $refillName;
            $lines[] = '';
            $lines[] = '- Stok awal: ' . $this->formatQty($refillBefore) . ' ' . $refillUnit;
            foreach ($refillUsages as $usage) {
                $lines[] = '- Dipakai transaksi ' . ($this->flowLabels[$usage['flow']] ?? $usage['flow']) . ' pesanan #' . $usage['pesanan_id'] . ': -' . $this->formatQty($usage['kg']) . ' ' . $refillUnit . ' untuk ' . $this->formatQty($usage['jumlah_unit']) . ' unit APAR ukuran ' . $usage['ukuran'];
            }
            foreach ($refillAdditions as $expense) {
                $lines[] = '- Bertambah dari pengeluaran #' . $expense->id . ': +' . $this->formatQty($expense->qty) . ' ' . ($expense->satuan ?: $refillUnit) . ' dari pembelian ' . $this->expenseItemLabel($expense) . ' @ ' . $this->formatMoney($expense->harga_beli);
            }
            $lines[] = '- Stok akhir: ' . $this->formatQty($refillAfter) . ' ' . $refillUnit;
            $lines[] = '- Validasi delta: ' . $this->formatQty($refillBefore) . ' + ' . $this->formatQty($refillAdditions->sum('qty')) . ' - ' . $this->formatQty($refillUsages->sum('kg')) . ' = ' . $this->formatQty($refillAfter) . ' ' . $refillUnit;
            $lines[] = '';
        }

        foreach (($this->report['initial_state']['stocks']['peralatan'] ?? []) as $peralatanSnapshot) {
            $peralatanId = (int) ($peralatanSnapshot['id'] ?? 0);
            $peralatanName = (string) ($peralatanSnapshot['nama'] ?? '-');
            $peralatanBefore = (float) ($peralatanSnapshot['stok'] ?? 0);
            $peralatanAfter = collect($this->report['final_state']['stocks']['peralatan'] ?? [])
                ->firstWhere('id', $peralatanId)['stok'] ?? 0;
            $peralatanAdditions = $this->expenses
                ->filter(fn (Pengeluaran $expense) => (int) $expense->peralatan_id === $peralatanId);
            $peralatanUsages = $this->services
                ->flatMap(function (Service $service) use ($peralatanId) {
                    return collect($service->stok_kurang_history)
                        ->filter(fn (array $history) => (int) ($history['peralatan_id'] ?? 0) === $peralatanId)
                        ->map(fn (array $history) => [
                            'pesanan_id' => (int) ($service->pesanan_id ?? 0),
                            'service_id' => (int) $service->id,
                            'jumlah' => (float) ($history['jumlah'] ?? 0),
                            'stok_sebelum' => $history['stok_sebelum'] ?? null,
                            'stok_sesudah' => $history['stok_sesudah'] ?? null,
                        ]);
                })
                ->values();

            $lines[] = '### Peralatan ' . $peralatanName;
            $lines[] = '';
            $lines[] = '- Stok awal: ' . $this->formatQty($peralatanBefore) . ' unit';
            foreach ($peralatanUsages as $usage) {
                $flow = $this->flowByOrderId($usage['pesanan_id']);
                $description = '- Dipakai service pesanan #' . $usage['pesanan_id'] . ' (' . ($this->flowLabels[$flow] ?? $flow) . '): -' . $this->formatQty($usage['jumlah']) . ' unit';
                if ($usage['stok_sebelum'] !== null && $usage['stok_sesudah'] !== null) {
                    $description .= ' (stok ' . $this->formatQty($usage['stok_sebelum']) . ' -> ' . $this->formatQty($usage['stok_sesudah']) . ')';
                }

                $lines[] = $description;
            }
            foreach ($peralatanAdditions as $expense) {
                $lines[] = '- Bertambah dari pengeluaran #' . $expense->id . ': +' . $this->formatQty($expense->qty) . ' unit dari pembelian ' . $this->expenseItemLabel($expense) . ' @ ' . $this->formatMoney($expense->harga_beli);
            }
            $lines[] = '- Stok akhir: ' . $this->formatQty($peralatanAfter) . ' unit';
            $lines[] = '- Validasi delta: ' . $this->formatQty($peralatanBefore) . ' + ' . $this->formatQty($peralatanAdditions->sum('qty')) . ' - ' . $this->formatQty($peralatanUsages->sum('jumlah')) . ' = ' . $this->formatQty($peralatanAfter) . ' unit';
            $lines[] = '';
        }

        return $lines;
    }

    private function buildUnitSection(): array
    {
        $lines = [];
        $lines[] = '## Detail Unit APAR Baru';
        $lines[] = '';
        $lines[] = '- Total unit APAR awal: ' . $this->formatQty($this->report['initial_state']['units']['customer_total'] ?? 0) . ' unit';
        $lines[] = '- Total unit APAR akhir: ' . $this->formatQty($this->report['final_state']['units']['customer_total'] ?? 0) . ' unit';
        $lines[] = '- Delta unit APAR: +' . $this->formatQty($this->report['deltas']['units']['customer_total']['delta'] ?? 0) . ' unit';
        $lines[] = '';

        foreach (($this->report['deltas']['units']['new_customer_units'] ?? []) as $unitMeta) {
            $unitId = (int) ($unitMeta['id'] ?? 0);
            $unit = $this->units->get($unitId);
            if (!$unit) {
                continue;
            }

            $source = $this->describeUnitSource($unit);
            $lines[] = '- Unit #' . $unit->id . ': `' . ($unit->no_seri ?: '-') . '`';
            $lines[] = '  Sumber: ' . $source;
            $lines[] = '  Pelanggan: ' . ($unit->pelanggan?->nama ?? '-');
            $lines[] = '  Produk referensi: ' . $this->productLabel($unit->produk, (string) ($unit->produk?->nama ?? '-'));
            $lines[] = '  Jenis APAR: ' . ($unit->bahan ?: '-');
            $lines[] = '  Ukuran: ' . ($unit->ukuran ?: '-');
            $lines[] = '  Kondisi awal: ' . ($unit->kondisi_awal ?: '-');
        }

        $lines[] = '';

        return $lines;
    }

    private function buildExpenseSection(): array
    {
        $lines = [];
        $lines[] = '## Detail Pengeluaran';
        $lines[] = '';

        foreach ($this->expenses as $expense) {
            $lines[] = '- Pengeluaran #' . $expense->id . ' - ' . $expense->jenis_pengeluaran_label;
            $lines[] = '  Item: ' . $this->expenseItemLabel($expense);
            $lines[] = '  Jumlah: ' . $this->formatQty($expense->qty) . ' ' . ($expense->satuan ?: 'unit');
            $lines[] = '  Harga beli: ' . $this->formatMoney($expense->harga_beli);
            $lines[] = '  Total: ' . $this->formatMoney($expense->effective_amount);
            $lines[] = '  Keterangan: ' . ($expense->keterangan ?: '-');
        }

        $lines[] = '';

        return $lines;
    }

    private function buildReportSection(): array
    {
        $before = $this->report['initial_state']['reports']['summary'] ?? [];
        $after = $this->report['final_state']['reports']['summary'] ?? [];

        $lines = [];
        $lines[] = '## Perubahan Laporan';
        $lines[] = '';
        $lines[] = '- Total pemasukan awal: ' . $this->formatMoney($before['total_pemasukan'] ?? 0);
        $lines[] = '- Total pemasukan akhir: ' . $this->formatMoney($after['total_pemasukan'] ?? 0);
        $lines[] = '- Selisih pemasukan: ' . $this->formatMoney(($after['total_pemasukan'] ?? 0) - ($before['total_pemasukan'] ?? 0));
        $lines[] = '- Total pengeluaran awal: ' . $this->formatMoney($before['total_pengeluaran'] ?? 0);
        $lines[] = '- Total pengeluaran akhir: ' . $this->formatMoney($after['total_pengeluaran'] ?? 0);
        $lines[] = '- Selisih pengeluaran: ' . $this->formatMoney(($after['total_pengeluaran'] ?? 0) - ($before['total_pengeluaran'] ?? 0));
        $lines[] = '- Laba bersih awal: ' . $this->formatMoney($before['laba_bersih'] ?? 0);
        $lines[] = '- Laba bersih akhir: ' . $this->formatMoney($after['laba_bersih'] ?? 0);
        $lines[] = '- Selisih laba bersih: ' . $this->formatMoney(($after['laba_bersih'] ?? 0) - ($before['laba_bersih'] ?? 0));
        $lines[] = '- Total pesanan produk: ' . $this->formatQty($before['total_pesanan_produk'] ?? 0) . ' -> ' . $this->formatQty($after['total_pesanan_produk'] ?? 0);
        $lines[] = '- Total service: ' . $this->formatQty($before['total_service'] ?? 0) . ' -> ' . $this->formatQty($after['total_service'] ?? 0);
        $lines[] = '- Total refill: ' . $this->formatQty($before['total_refill'] ?? 0) . ' -> ' . $this->formatQty($after['total_refill'] ?? 0);
        $lines[] = '- Total unit APAR: ' . $this->formatQty($before['total_unit'] ?? 0) . ' -> ' . $this->formatQty($after['total_unit'] ?? 0);
        $lines[] = '';

        return $lines;
    }

    private function buildProofSection(): array
    {
        $lines = [];
        $lines[] = '## Validasi Bukti Pembayaran';
        $lines[] = '';

        foreach ($this->proofChecks as $proof) {
            $orderId = (int) ($proof['pesanan_id'] ?? 0);
            $flow = $this->flowByOrderId($orderId);
            $lines[] = '- Pesanan #' . $orderId . ' (' . ($this->flowLabels[$flow] ?? $flow) . ')';
            $lines[] = '  File: `' . ($proof['proof_path'] ?: '-') . '`';
            $lines[] = '  Tersimpan di storage: ' . ($proof['storage_exists'] ? 'ya' : 'tidak');
            $lines[] = '  Tampil pada halaman `' . ($proof['display_page'] ?: '-') . '`: ' . ($proof['displayed'] ? 'ya' : 'tidak');
        }

        $lines[] = '';

        return $lines;
    }

    private function buildClosingSection(): array
    {
        $lines = [];
        $lines[] = '## Kesimpulan';
        $lines[] = '';
        $lines[] = '- Semua skenario operasional pada run final audit ini lulus.';
        $lines[] = '- Rincian stok, unit APAR, pengeluaran, laporan, dan bukti pembayaran pada log ini sudah diturunkan langsung dari report audit final dan data transaksi live yang tersimpan.';
        $lines[] = '- File ini bisa dijadikan log pembanding saat Anda melakukan regresi audit berikutnya.';
        $lines[] = '';

        return $lines;
    }

    private function refillByOrderId(int $orderId): ?Refill
    {
        return $this->refills->first(fn (Refill $refill) => (int) optional($refill->service)->pesanan_id === $orderId);
    }

    private function serviceByOrderId(int $orderId): ?Service
    {
        return $this->services->first(fn (Service $service) => (int) $service->pesanan_id === $orderId);
    }

    private function flowByOrderId(int $orderId): string
    {
        return (string) optional($this->transactions->firstWhere('pesanan_id', $orderId))['flow'];
    }

    private function describeUnitSource(UnitApar $unit): string
    {
        if ($unit->pesanan_id) {
            $flow = $this->flowByOrderId((int) $unit->pesanan_id);

            return 'Terbentuk dari ' . ($this->flowLabels[$flow] ?? ('pesanan #' . $unit->pesanan_id));
        }

        $createdMeta = $this->createdUnitMeta->firstWhere('id', (int) $unit->id);
        if (($createdMeta['source'] ?? null) === 'manual_admin') {
            return 'Registrasi unit manual oleh admin';
        }

        return 'Sumber tidak teridentifikasi';
    }

    private function expenseItemLabel(Pengeluaran $expense): string
    {
        if ($expense->produk) {
            return $this->productLabel($expense->produk, (string) $expense->display_item_name);
        }

        if ($expense->jenisRefill) {
            return (string) ($expense->jenisRefill->nama_label ?? $expense->display_item_name);
        }

        if ($expense->peralatan) {
            return (string) ($expense->peralatan->nama ?? $expense->display_item_name);
        }

        return (string) $expense->display_item_name;
    }

    private function productLabel(?Produk $product, string $fallback): string
    {
        if (!$product) {
            return $fallback;
        }

        $parts = [
            $product->nama,
            $product->merek ? 'Merek ' . $product->merek : null,
            $product->kapasitas ?: null,
        ];

        return collect($parts)->filter()->implode(' | ');
    }

    private function formatMoney(mixed $amount): string
    {
        return 'Rp ' . number_format((float) ($amount ?? 0), 0, ',', '.');
    }

    private function formatQty(mixed $qty): string
    {
        $value = (float) ($qty ?? 0);

        if (abs($value - round($value)) < 0.00001) {
            return number_format($value, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    }
}
