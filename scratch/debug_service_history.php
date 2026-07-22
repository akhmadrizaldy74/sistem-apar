<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pesanan;
use App\Models\UnitApar;
use App\Support\RegisteredRefillUnitSupport;

$unit = UnitApar::where('no_seri', 'AKHMAD-22072026-01')->first();
if (!$unit) {
    echo "Unit AKHMAD-22072026-01 not found!\n";
    exit;
}

echo "Target Unit: ID {$unit->id} | Seri: {$unit->no_seri} | Pelanggan ID: {$unit->pelanggan_id}\n\n";

$orders = Pesanan::where('pelanggan_id', $unit->pelanggan_id)->get();
echo "Found " . $orders->count() . " total orders for this customer:\n";

foreach ($orders as $order) {
    echo "Order #{$order->id} | Tipe: {$order->tipe} | Status: {$order->status} | Kategori: {$order->service_jenis_layanan}\n";
    echo "   Keterangan: {$order->keterangan}\n";
    echo "   Keluhan: {$order->service_keluhan}\n";

    $ref = RegisteredRefillUnitSupport::orderReferencesUnit($order, $unit);
    echo "   -> References Unit #{$unit->id}?: " . ($ref ? 'YES' : 'NO') . "\n";

    $resolvedUnits = RegisteredRefillUnitSupport::resolveRegisteredUnitsForOrder($order);
    echo "   -> Resolved Units Count: " . $resolvedUnits->count() . "\n";
    foreach ($resolvedUnits as $ru) {
        echo "      Resolved Unit ID: {$ru->id} | Seri: {$ru->no_seri}\n";
    }
    echo "\n";
}
