<?php

declare(strict_types=1);

use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\Testimoni;
use App\Models\UnitApar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

date_default_timezone_set((string) config('app.timezone', 'Asia/Jakarta'));

const TARGET_CUSTOMER_NAME = 'Akhmad Rizaldy';

main($argv);

function main(array $argv): void
{
    $options = parseOptions($argv);
    $runId = now('Asia/Jakarta')->format('Ymd_His');
    $customer = resolveCustomer($options);

    $context = collectCleanupContext((int) $customer->id);
    $report = [
        'meta' => [
            'run_id' => $runId,
            'generated_at' => now('Asia/Jakarta')->toDateTimeString(),
            'database' => (string) config('database.connections.mysql.database'),
            'mode' => $options['execute'] ? 'execute' : 'dry-run',
        ],
        'target_customer' => [
            'pelanggan_id' => (int) $customer->id,
            'user_id' => (int) ($customer->user_id ?? 0),
            'nama' => (string) $customer->nama,
            'no_wa' => (string) ($customer->no_wa ?? ''),
        ],
        'dry_run' => buildDryRunSummary($context),
        'notes' => [
            'Tidak ada reset database.',
            'Tidak ada penghapusan akun, produk, master stok, atau data pelanggan lain.',
            'File bukti pembayaran dan file upload terkait tidak dihapus untuk menjaga keamanan filesystem.',
        ],
    ];

    if (! $options['execute']) {
        [$jsonPath, $mdPath] = writeReport($runId, 'customer_cleanup', $report);

        echo 'Dry-run cleanup selesai.' . PHP_EOL;
        echo 'JSON: ' . $jsonPath . PHP_EOL;
        echo 'Markdown: ' . $mdPath . PHP_EOL;
        echo 'Ringkasan: ' . json_encode($report['dry_run']['counts'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        exit(0);
    }

    $execution = [
        'restored' => [
            'produk' => [],
            'refill' => [],
            'peralatan' => [],
        ],
        'deleted' => [],
        'baseline_units' => [],
    ];

    DB::transaction(function () use ($customer, $context, $runId, &$execution): void {
        $execution['restored']['produk'] = restoreProductStocks($context, $runId);
        $execution['restored']['refill'] = restoreRefillStocks($context);
        $execution['restored']['peralatan'] = restorePeralatanStocks($context);
        $execution['deleted'] = deleteOperationalData($context);
        $execution['baseline_units'] = createBaselineUnits($customer, $runId);
    });

    $postState = snapshotCustomerState((int) $customer->id);

    $report['execution'] = $execution;
    $report['post_state'] = $postState;

    [$jsonPath, $mdPath] = writeReport($runId, 'customer_cleanup', $report);

    echo 'Cleanup execute selesai.' . PHP_EOL;
    echo 'JSON: ' . $jsonPath . PHP_EOL;
    echo 'Markdown: ' . $mdPath . PHP_EOL;
    echo 'Post state: ' . json_encode($postState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

function parseOptions(array $argv): array
{
    $options = [
        'execute' => false,
        'customer_id' => null,
        'customer_name' => TARGET_CUSTOMER_NAME,
    ];

    foreach ($argv as $arg) {
        if ($arg === '--execute') {
            $options['execute'] = true;
            continue;
        }

        if (str_starts_with($arg, '--customer-id=')) {
            $options['customer_id'] = (int) substr($arg, strlen('--customer-id='));
            continue;
        }

        if (str_starts_with($arg, '--customer-name=')) {
            $options['customer_name'] = trim((string) substr($arg, strlen('--customer-name=')));
        }
    }

    return $options;
}

function resolveCustomer(array $options): Pelanggan
{
    $query = Pelanggan::query()->with('user');

    if ($options['customer_id']) {
        return $query->findOrFail((int) $options['customer_id']);
    }

    $customer = $query
        ->where('nama', (string) $options['customer_name'])
        ->first();

    if ($customer) {
        return $customer;
    }

    return $query
        ->where('nama', 'like', '%' . (string) $options['customer_name'] . '%')
        ->firstOrFail();
}

function collectCleanupContext(int $pelangganId): array
{
    $orders = Pesanan::query()
        ->with(['details.produk.jenisApar', 'service', 'serviceJenisRefill'])
        ->where('pelanggan_id', $pelangganId)
        ->orderBy('id')
        ->get();

    $orderIds = $orders->pluck('id')->map(fn ($id) => (int) $id)->values();
    $units = UnitApar::query()
        ->with('produk.jenisApar')
        ->where('pelanggan_id', $pelangganId)
        ->orderBy('id')
        ->get();
    $unitIds = $units->pluck('id')->map(fn ($id) => (int) $id)->values();

    $services = collect();
    if ($orderIds->isNotEmpty() || $unitIds->isNotEmpty()) {
        $services = Service::query()
            ->where(function ($query) use ($orderIds, $unitIds): void {
                if ($orderIds->isNotEmpty()) {
                    $query->whereIn('pesanan_id', $orderIds->all());
                }

                if ($unitIds->isNotEmpty()) {
                    $method = $orderIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('unit_apar_id', $unitIds->all());
                }
            })
            ->orderBy('id')
            ->get();
    }
    $serviceIds = $services->pluck('id')->map(fn ($id) => (int) $id)->values();

    $refills = collect();
    if ($serviceIds->isNotEmpty() || $unitIds->isNotEmpty()) {
        $refills = Refill::query()
            ->where(function ($query) use ($serviceIds, $unitIds): void {
                if ($serviceIds->isNotEmpty()) {
                    $query->whereIn('service_id', $serviceIds->all());
                }

                if ($unitIds->isNotEmpty()) {
                    $method = $serviceIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('unit_apar_id', $unitIds->all());
                }
            })
            ->orderBy('id')
            ->get();
    }
    $refillIds = $refills->pluck('id')->map(fn ($id) => (int) $id)->values();

    $details = $orderIds->isNotEmpty()
        ? PesananDetail::query()
            ->whereIn('pesanan_id', $orderIds->all())
            ->orderBy('id')
            ->get()
        : collect();
    $detailIds = $details->pluck('id')->map(fn ($id) => (int) $id)->values();

    $complains = DB::table('complains')
        ->where(function ($query) use ($pelangganId, $orderIds, $serviceIds): void {
            $query->where('pelanggan_id', $pelangganId);

            if ($orderIds->isNotEmpty()) {
                $query->orWhereIn('pesanan_id', $orderIds->all());
            }

            if ($serviceIds->isNotEmpty()) {
                $query->orWhereIn('service_id', $serviceIds->all());
            }
        })
        ->orderBy('id')
        ->get();
    $complainIds = collect($complains)->pluck('id')->map(fn ($id) => (int) $id)->values();

    $testimonis = Testimoni::query()
        ->where('pelanggan_id', $pelangganId)
        ->orderBy('id')
        ->get();
    $testimoniIds = $testimonis->pluck('id')->map(fn ($id) => (int) $id)->values();

    $activityLogIds = collect(resolveActivityLogIds(
        orderIds: $orderIds,
        detailIds: $detailIds,
        serviceIds: $serviceIds,
        refillIds: $refillIds,
        unitIds: $unitIds,
        complainIds: $complainIds,
        testimoniIds: $testimoniIds,
    ));

    return [
        'pelanggan_id' => $pelangganId,
        'orders' => $orders,
        'order_ids' => $orderIds,
        'details' => $details,
        'detail_ids' => $detailIds,
        'units' => $units,
        'unit_ids' => $unitIds,
        'services' => $services,
        'service_ids' => $serviceIds,
        'refills' => $refills,
        'refill_ids' => $refillIds,
        'complains' => collect($complains),
        'complain_ids' => $complainIds,
        'testimonis' => $testimonis,
        'testimoni_ids' => $testimoniIds,
        'activity_log_ids' => $activityLogIds,
        'payment_proofs' => $orders->pluck('bukti_pembayaran')->filter()->values(),
        'service_reports' => $services->pluck('laporan_foto')->filter()->values(),
    ];
}

function resolveActivityLogIds(
    Collection $orderIds,
    Collection $detailIds,
    Collection $serviceIds,
    Collection $refillIds,
    Collection $unitIds,
    Collection $complainIds,
    Collection $testimoniIds,
): array {
    $subjectMap = [
        App\Models\Pesanan::class => $orderIds->all(),
        App\Models\PesananDetail::class => $detailIds->all(),
        App\Models\Service::class => $serviceIds->all(),
        App\Models\Refill::class => $refillIds->all(),
        App\Models\UnitApar::class => $unitIds->all(),
        App\Models\Complain::class => $complainIds->all(),
        App\Models\Testimoni::class => $testimoniIds->all(),
    ];

    $query = DB::table('activity_logs');
    $hasCondition = false;

    foreach ($subjectMap as $subjectType => $ids) {
        if ($ids === []) {
            continue;
        }

        $method = $hasCondition ? 'orWhere' : 'where';
        $query->{$method}(function ($inner) use ($subjectType, $ids): void {
            $inner->where('subject_type', $subjectType)->whereIn('subject_id', $ids);
        });
        $hasCondition = true;
    }

    if (! $hasCondition) {
        return [];
    }

    return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
}

function buildDryRunSummary(array $context): array
{
    $orders = $context['orders'];
    $units = $context['units'];
    $services = $context['services'];
    $refills = $context['refills'];

    return [
        'counts' => [
            'pesanan' => (int) $orders->count(),
            'pesanan_detail' => (int) $context['details']->count(),
            'service' => (int) $services->count(),
            'refill' => (int) $refills->count(),
            'unit_apar' => (int) $units->count(),
            'complain' => (int) $context['complains']->count(),
            'testimoni' => (int) $context['testimonis']->count(),
            'activity_log' => (int) $context['activity_log_ids']->count(),
            'payment_proof_references' => (int) $context['payment_proofs']->count(),
            'service_report_references' => (int) $context['service_reports']->count(),
        ],
        'orders_by_status' => $orders
            ->groupBy(fn (Pesanan $order) => (string) $order->status)
            ->map(fn (Collection $group) => (int) $group->count())
            ->sortKeys()
            ->all(),
        'orders_by_type' => $orders
            ->groupBy(fn (Pesanan $order) => $order->tipe . ':' . ($order->service_jenis_layanan ?? '-'))
            ->map(fn (Collection $group) => (int) $group->count())
            ->sortKeys()
            ->all(),
        'units_preview' => $units
            ->take(10)
            ->map(fn (UnitApar $unit) => [
                'id' => (int) $unit->id,
                'no_seri' => (string) $unit->no_seri,
                'pesanan_id' => $unit->pesanan_id ? (int) $unit->pesanan_id : null,
                'ukuran' => (string) $unit->ukuran,
                'bahan' => (string) $unit->bahan,
            ])
            ->values()
            ->all(),
        'stock_restore' => [
            'produk' => computeProductRestoration($context),
            'refill' => computeRefillRestoration($context),
            'peralatan' => computePeralatanRestoration($context),
        ],
    ];
}

function computeProductRestoration(array $context): array
{
    $items = [];

    foreach ($context['orders'] as $order) {
        if (! $order->isProductOrder() || ! $order->stok_dikurangi) {
            continue;
        }

        foreach ($order->details as $detail) {
            $productId = (int) $detail->produk_id;
            if (!isset($items[$productId])) {
                $items[$productId] = [
                    'produk_id' => $productId,
                    'nama' => (string) ($detail->produk?->nama ?? ('Produk #' . $productId)),
                    'qty_restore' => 0,
                    'pesanan_ids' => [],
                ];
            }

            $items[$productId]['qty_restore'] += (int) $detail->jumlah;
            $items[$productId]['pesanan_ids'][] = (int) $order->id;
        }
    }

    return array_values(array_map(function (array $item): array {
        $item['pesanan_ids'] = array_values(array_unique($item['pesanan_ids']));
        return $item;
    }, $items));
}

function computeRefillRestoration(array $context): array
{
    $items = [];

    foreach ($context['orders'] as $order) {
        if (! $order->isRefillOrder() || ! $order->stok_dikurangi) {
            continue;
        }

        $refillId = (int) ($order->service_jenis_refill_id ?? 0);
        if ($refillId <= 0) {
            continue;
        }

        if (!isset($items[$refillId])) {
            $items[$refillId] = [
                'jenis_refill_id' => $refillId,
                'nama' => (string) ($order->serviceJenisRefill?->nama_label ?? ('Refill #' . $refillId)),
                'qty_restore' => 0.0,
                'pesanan_ids' => [],
            ];
        }

        $items[$refillId]['qty_restore'] += (float) ($order->service_total_kg ?? 0);
        $items[$refillId]['pesanan_ids'][] = (int) $order->id;
    }

    return array_values(array_map(function (array $item): array {
        $item['qty_restore'] = round((float) $item['qty_restore'], 2);
        $item['pesanan_ids'] = array_values(array_unique($item['pesanan_ids']));
        return $item;
    }, $items));
}

function computePeralatanRestoration(array $context): array
{
    $items = [];

    foreach ($context['services'] as $service) {
        $history = json_decode((string) ($service->stok_kurang_history_json ?? ''), true);
        if (!is_array($history) || $history === []) {
            continue;
        }

        foreach ($history as $row) {
            $peralatanId = (int) ($row['peralatan_id'] ?? 0);
            if ($peralatanId <= 0) {
                continue;
            }

            if (!isset($items[$peralatanId])) {
                $items[$peralatanId] = [
                    'peralatan_id' => $peralatanId,
                    'nama' => (string) ($row['nama'] ?? ('Peralatan #' . $peralatanId)),
                    'qty_restore' => 0,
                    'service_ids' => [],
                    'pesanan_ids' => [],
                ];
            }

            $items[$peralatanId]['qty_restore'] += (int) ($row['jumlah'] ?? 0);
            $items[$peralatanId]['service_ids'][] = (int) $service->id;
            $items[$peralatanId]['pesanan_ids'][] = (int) ($service->pesanan_id ?? 0);
        }
    }

    return array_values(array_map(function (array $item): array {
        $item['service_ids'] = array_values(array_unique(array_filter($item['service_ids'])));
        $item['pesanan_ids'] = array_values(array_unique(array_filter($item['pesanan_ids'])));
        return $item;
    }, $items));
}

function restoreProductStocks(array $context, string $runId): array
{
    $restores = computeProductRestoration($context);
    $today = Carbon::today('Asia/Jakarta');

    foreach ($restores as $item) {
        $product = Produk::query()->findOrFail((int) $item['produk_id']);
        $qty = (int) $item['qty_restore'];
        if ($qty <= 0) {
            continue;
        }

        DB::table('produks')->where('id', $product->id)->increment('stok', $qty);
        DB::table('stok_batches')->insert([
            'produk_id' => $product->id,
            'jumlah_masuk' => $qty,
            'sisa_qty' => $qty,
            'tgl_produksi' => $today->toDateString(),
            'tgl_expired' => resolveAparExpiredDate($product, $today)->toDateString(),
            'keterangan' => 'Restore cleanup pelanggan ' . TARGET_CUSTOMER_NAME . ' [' . $runId . ']',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $restores;
}

function restoreRefillStocks(array $context): array
{
    $restores = computeRefillRestoration($context);

    foreach ($restores as $item) {
        $qty = round((float) $item['qty_restore'], 2);
        if ($qty <= 0) {
            continue;
        }

        DB::table('jenis_refills')->where('id', (int) $item['jenis_refill_id'])->increment('stok', $qty);
    }

    return $restores;
}

function restorePeralatanStocks(array $context): array
{
    $restores = computePeralatanRestoration($context);

    foreach ($restores as $item) {
        $qty = (int) $item['qty_restore'];
        if ($qty <= 0) {
            continue;
        }

        DB::table('peralatans')->where('id', (int) $item['peralatan_id'])->increment('stok', $qty);
    }

    return $restores;
}

function deleteOperationalData(array $context): array
{
    $deleted = [];

    $deleted['complains'] = deleteByIds('complains', $context['complain_ids']);
    $deleted['refills'] = deleteByIds('refills', $context['refill_ids']);
    $deleted['services'] = deleteByIds('services', $context['service_ids']);
    $deleted['unit_apars'] = deleteByIds('unit_apars', $context['unit_ids']);
    $deleted['pesanan_details'] = deleteByIds('pesanan_details', $context['detail_ids']);
    $deleted['pesanans'] = deleteByIds('pesanans', $context['order_ids']);
    $deleted['testimonis'] = deleteByIds('testimonis', $context['testimoni_ids']);
    $deleted['activity_logs'] = deleteByIds('activity_logs', $context['activity_log_ids']);

    return $deleted;
}

function deleteByIds(string $table, Collection $ids): int
{
    if ($ids->isEmpty()) {
        return 0;
    }

    return (int) DB::table($table)->whereIn('id', $ids->all())->delete();
}

function createBaselineUnits(Pelanggan $customer, string $runId): array
{
    $today = Carbon::today('Asia/Jakarta');
    $prefix = 'AKHMAD-' . $today->format('dmY');

    $powderProduct = Produk::query()
        ->with('jenisApar')
        ->where('kapasitas', 'like', '%1 kg%')
        ->whereHas('jenisApar', fn ($query) => $query->where('nama', 'like', '%Powder%'))
        ->orderByDesc('stok')
        ->orderBy('id')
        ->firstOrFail();

    $foamProduct = Produk::query()
        ->with('jenisApar')
        ->where('kapasitas', 'like', '%6 kg%')
        ->whereHas('jenisApar', fn ($query) => $query->where('nama', 'like', '%Foam%'))
        ->orderByDesc('stok')
        ->orderBy('id')
        ->firstOrFail();

    $units = [
        [
            'produk' => $powderProduct,
            'no_seri' => $prefix . '-01',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'lokasi_unit' => 'Baseline retest pelanggan',
            'catatan_unit' => 'Baseline retest ' . $runId . ' - APAR terdaftar awal',
        ],
        [
            'produk' => $foamProduct,
            'no_seri' => $prefix . '-02',
            'ukuran' => '6 kg',
            'bahan' => 'Foam',
            'lokasi_unit' => 'Baseline retest pelanggan',
            'catatan_unit' => 'Baseline retest ' . $runId . ' - APAR terdaftar awal',
        ],
    ];

    $created = [];

    foreach ($units as $unit) {
        DB::table('unit_apars')->insert([
            'pelanggan_id' => (int) $customer->id,
            'pesanan_id' => null,
            'produk_id' => (int) $unit['produk']->id,
            'no_seri' => $unit['no_seri'],
            'lokasi_unit' => $unit['lokasi_unit'],
            'tgl_beli' => $today->toDateString(),
            'tgl_produksi' => $today->toDateString(),
            'ukuran' => $unit['ukuran'],
            'bahan' => $unit['bahan'],
            'kondisi_awal' => 'layak',
            'catatan_unit' => $unit['catatan_unit'],
            'tgl_expired' => UnitApar::calculateExpiry($today->toDateString(), $unit['ukuran'], $unit['bahan'])->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $created[] = [
            'no_seri' => $unit['no_seri'],
            'produk_id' => (int) $unit['produk']->id,
            'produk' => (string) $unit['produk']->nama,
            'jenis_apar' => (string) $unit['bahan'],
            'ukuran' => (string) $unit['ukuran'],
            'status' => 'Aktif',
        ];
    }

    return $created;
}

function snapshotCustomerState(int $pelangganId): array
{
    return [
        'pesanan' => (int) DB::table('pesanans')->where('pelanggan_id', $pelangganId)->count(),
        'service' => (int) DB::table('services')->whereIn('pesanan_id', DB::table('pesanans')->where('pelanggan_id', $pelangganId)->select('id'))->count(),
        'refill' => (int) DB::table('refills')->whereIn('service_id', DB::table('services')->select('id'))->whereIn('unit_apar_id', DB::table('unit_apars')->where('pelanggan_id', $pelangganId)->select('id'))->count(),
        'unit_apar' => (int) DB::table('unit_apars')->where('pelanggan_id', $pelangganId)->count(),
        'unit_numbers' => DB::table('unit_apars')
            ->where('pelanggan_id', $pelangganId)
            ->orderBy('id')
            ->pluck('no_seri')
            ->all(),
        'dangling_status_counts' => DB::table('pesanans')
            ->where('pelanggan_id', $pelangganId)
            ->whereNotIn('status', ['selesai', 'selesai final', 'ditolak'])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all(),
    ];
}

function writeReport(string $runId, string $prefix, array $report): array
{
    $directory = storage_path('app/qa_reports');
    if (!File::isDirectory($directory)) {
        File::makeDirectory($directory, 0777, true);
    }

    $jsonPath = $directory . DIRECTORY_SEPARATOR . $prefix . '_' . $runId . '.json';
    $mdPath = $directory . DIRECTORY_SEPARATOR . $prefix . '_' . $runId . '.md';

    File::put($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    File::put($mdPath, buildMarkdown($report));

    return [$jsonPath, $mdPath];
}

function buildMarkdown(array $report): string
{
    $lines = [];
    $lines[] = '# Laporan Cleanup Operasional Pelanggan';
    $lines[] = '';
    $lines[] = '- Run ID: `' . $report['meta']['run_id'] . '`';
    $lines[] = '- Waktu: ' . $report['meta']['generated_at'];
    $lines[] = '- Database: `' . $report['meta']['database'] . '`';
    $lines[] = '- Mode: `' . $report['meta']['mode'] . '`';
    $lines[] = '';
    $lines[] = '## Target';
    $lines[] = '';
    $lines[] = '- Pelanggan: `' . $report['target_customer']['nama'] . '`';
    $lines[] = '- Pelanggan ID: `' . $report['target_customer']['pelanggan_id'] . '`';
    $lines[] = '- User ID: `' . $report['target_customer']['user_id'] . '`';
    $lines[] = '';
    $lines[] = '## Dry Run';
    $lines[] = '';
    foreach ($report['dry_run']['counts'] as $key => $value) {
        $lines[] = '- ' . $key . ': ' . $value;
    }
    $lines[] = '';
    $lines[] = '### Status Pesanan';
    $lines[] = '';
    foreach ($report['dry_run']['orders_by_status'] as $status => $total) {
        $lines[] = '- ' . $status . ': ' . $total;
    }
    $lines[] = '';
    $lines[] = '### Restore Stok';
    $lines[] = '';
    foreach (['produk', 'refill', 'peralatan'] as $group) {
        $lines[] = '- ' . ucfirst($group) . ': `' . json_encode($report['dry_run']['stock_restore'][$group], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
    }

    if (isset($report['execution'])) {
        $lines[] = '';
        $lines[] = '## Execution';
        $lines[] = '';
        $lines[] = '- Deleted: `' . json_encode($report['execution']['deleted'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '- Baseline units: `' . json_encode($report['execution']['baseline_units'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '';
        $lines[] = '## Post State';
        $lines[] = '';
        foreach ($report['post_state'] as $key => $value) {
            $lines[] = '- ' . $key . ': `' . json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        }
    }

    $lines[] = '';
    $lines[] = '## Catatan';
    $lines[] = '';
    foreach ($report['notes'] as $note) {
        $lines[] = '- ' . $note;
    }

    return implode(PHP_EOL, $lines) . PHP_EOL;
}

function resolveAparExpiredDate(Produk $product, Carbon $tanggalMasuk): Carbon
{
    $baseDate = $tanggalMasuk->copy();
    $ukuranAngka = (float) filter_var((string) $product->kapasitas, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    return $ukuranAngka === 1.0
        ? $baseDate->addMonths(6)
        : $baseDate->addYear();
}
