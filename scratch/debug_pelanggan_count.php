<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pelanggan;
use App\Models\Pesanan;

$summaryQuery = Pelanggan::query()->visibleInDirectory();

// Simulate the FIXED realtime controller summary
$summary = [
    'totalPelanggan' => (clone $summaryQuery)->count(),
    'pelangganAktif' => (clone $summaryQuery)->whereHas('validOrders')->count(),
    'totalTransaksiPelanggan' => Pesanan::query()
        ->whereNotIn('status', Pelanggan::excludedPurchaseStatuses())
        ->whereIn('pelanggan_id', (clone $summaryQuery)->select('pelanggans.id'))
        ->count(),
];

echo "=== FIXED Summary ===\n";
echo "Total Pelanggan: {$summary['totalPelanggan']}\n";
echo "Pelanggan Aktif: {$summary['pelangganAktif']}\n";
echo "Total Transaksi Pelanggan: {$summary['totalTransaksiPelanggan']}\n";

// Also check per-pelanggan withCount
$pelanggans = Pelanggan::query()
    ->visibleInDirectory()
    ->withCount(['validOrders as product_orders_count', 'validOrders as valid_orders_count'])
    ->get();

echo "\n=== Per Pelanggan ===\n";
foreach ($pelanggans as $p) {
    echo "{$p->nama}: product_orders_count={$p->product_orders_count}, valid_orders_count={$p->valid_orders_count}\n";
}
