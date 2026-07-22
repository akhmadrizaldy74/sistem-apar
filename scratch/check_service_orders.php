<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pesanan;
use App\Models\UnitApar;

$orders = Pesanan::where('tipe', 'service')->get();
echo "Found " . $orders->count() . " service orders.\n";

foreach ($orders as $order) {
    echo "Order #{$order->id} | Tipe: {$order->tipe} | Kategori: {$order->service_jenis_layanan} | Status: {$order->status} | Ket: {$order->keterangan}\n";
    echo "  Keluhan: {$order->service_keluhan}\n";
}

$unit = UnitApar::where('no_seri', 'AKHMAD-22072026-01')->first();
if ($unit) {
    echo "Unit ID: {$unit->id} | Seri: {$unit->no_seri}\n";
}
