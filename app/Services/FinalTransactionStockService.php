<?php

namespace App\Services;

use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\UnitApar;
use Illuminate\Support\Facades\DB;

class FinalTransactionStockService
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function apply(Pesanan $pesanan): void
    {
        $pesanan->refresh()->loadMissing([
            'pelanggan',
            'details.produk.jenisApar',
            'service',
            'servicePaket.peralatans',
            'serviceJenisRefill',
        ]);

        if ((string) $pesanan->status !== Pesanan::STATUS_SELESAI_FINAL) {
            return;
        }

        DB::transaction(function () use ($pesanan) {
            if ($pesanan->isProductOrder()) {
                $pesanan->reduceStock();
                return;
            }

            if ($pesanan->isRefillOrder()) {
                $this->applyRefillStock($pesanan);
                return;
            }

            if ($pesanan->isPackageServiceOrder()) {
                $this->applyServicePeralatanStock($pesanan);
            }
        });
    }

    private function applyRefillStock(Pesanan $pesanan): void
    {
        if ($pesanan->stok_dikurangi) {
            return;
        }

        $this->ensureRefillLog($pesanan);

        $jenisRefill = $pesanan->serviceJenisRefill;
        $qty = (float) ($pesanan->service_total_kg ?? 0);

        if (!$jenisRefill || $qty <= 0) {
            return;
        }

        $this->inventoryService->decreaseRefillStock(
            jenisRefill: $jenisRefill,
            qty: $qty,
            sourceType: StockMovement::SOURCE_REFILL_PELANGGAN,
            reference: $pesanan,
            keterangan: 'Refill APAR - ' . $this->customerName($pesanan),
            tanggal: now(),
        );

        $pesanan->forceFill(['stok_dikurangi' => true])->save();
    }

    private function ensureRefillLog(Pesanan $pesanan): void
    {
        if (!$pesanan->service_jenis_refill_id) {
            return;
        }

        $unitAparId = $pesanan->service?->unit_apar_id ?: $pesanan->unitApars()->value('id');

        if (!$unitAparId) {
            $unitApar = UnitApar::where('pelanggan_id', $pesanan->pelanggan_id)
                ->where(function ($query) use ($pesanan) {
                    $query->where('ukuran', $pesanan->service_ukuran_apar)
                        ->orWhereRaw("CONCAT(ukuran, '') LIKE ?", ['%' . ($pesanan->service_ukuran_apar ?? '') . '%']);
                })
                ->orderByDesc('tgl_beli')
                ->first();

            if (!$unitApar) {
                $produk = Produk::where('kapasitas', $pesanan->service_ukuran_apar)->first();
                if ($produk) {
                    $unitApar = UnitApar::create([
                        'pelanggan_id' => $pesanan->pelanggan_id,
                        'pesanan_id' => $pesanan->id,
                        'produk_id' => $produk->id,
                        'no_seri' => 'AUTO-' . $pesanan->id . '-' . time(),
                        'tgl_beli' => $pesanan->tanggal ?: now(),
                        'tgl_produksi' => $pesanan->tanggal ?: now(),
                        'ukuran' => $pesanan->service_ukuran_apar,
                        'bahan' => $pesanan->service_jenis_apar ?: 'APAR',
                        'tgl_expired' => now()->addYear(),
                    ]);
                }
            }

            $unitAparId = $unitApar?->id;
        }

        if (!$unitAparId) {
            return;
        }

        $service = Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            [
                'unit_apar_id' => $unitAparId,
                'jenis_service' => 'Refill APAR',
                'tgl_service' => $pesanan->tanggal ?: now(),
                'biaya' => (float) ($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? $pesanan->total ?? 0),
                'status_konfirmasi' => 'confirmed',
                'tgl_selesai_admin' => now(),
            ],
        );

        Refill::updateOrCreate(
            ['service_id' => $service->id],
            [
                'unit_apar_id' => $unitAparId,
                'jenis_refill_id' => $pesanan->service_jenis_refill_id,
                'tgl_refill' => $pesanan->tanggal ?: now(),
                'biaya' => (float) ($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? $pesanan->total ?? 0),
            ],
        );

        UnitApar::whereKey($unitAparId)->update(['tgl_expired' => now()->addYear()]);
    }

    private function applyServicePeralatanStock(Pesanan $pesanan): void
    {
        $service = Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            $pesanan->serviceLogPayload([
                'status_konfirmasi' => $pesanan->service?->status_konfirmasi ?: 'pending',
                'actual_peralatan_json' => $pesanan->service?->actual_peralatan_json,
                'catatan_teknisi' => $pesanan->service?->catatan_teknisi,
                'laporan_foto' => $pesanan->service?->laporan_foto,
                'tgl_selesai_admin' => $pesanan->service?->tgl_selesai_admin,
                'stok_kurang_history_json' => $pesanan->service?->stok_kurang_history_json,
            ]),
        );

        if (!empty($service->stok_kurang_history)) {
            $service->update([
                'status_konfirmasi' => 'confirmed',
                'tgl_selesai_admin' => $service->tgl_selesai_admin ?: now(),
            ]);
            return;
        }

        $items = collect($service->effective_peralatan ?: $pesanan->estimatedServicePeralatan())
            ->map(fn (array $item) => [
                'peralatan_id' => (int) ($item['peralatan_id'] ?? $item['id'] ?? 0),
                'nama' => (string) ($item['nama'] ?? ''),
                'jumlah' => (int) ($item['jumlah'] ?? 0),
            ])
            ->filter(fn (array $item) => $item['peralatan_id'] > 0 && $item['jumlah'] > 0)
            ->values();

        $history = [];

        foreach ($items as $item) {
            $peralatan = Peralatan::find($item['peralatan_id']);
            if (!$peralatan) {
                continue;
            }

            $stokSebelum = (float) $peralatan->stok;
            $jumlah = (float) $item['jumlah'];

            $this->inventoryService->decreasePeralatanStock(
                peralatan: $peralatan,
                qty: $jumlah,
                sourceType: StockMovement::SOURCE_SERVICE_PELANGGAN,
                reference: $pesanan,
                keterangan: 'Service APAR - ' . $this->customerName($pesanan),
                tanggal: now(),
            );

            $history[] = [
                'peralatan_id' => $peralatan->id,
                'nama' => $peralatan->nama,
                'jumlah' => (int) $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => (float) $peralatan->fresh()->stok,
            ];
        }

        $service->update([
            'actual_peralatan_json' => $service->actual_peralatan_json ?: json_encode($items->all()),
            'status_konfirmasi' => 'confirmed',
            'tgl_selesai_admin' => now(),
            'stok_kurang_history_json' => json_encode($history),
        ]);
    }

    private function customerName(Pesanan $pesanan): string
    {
        return (string) ($pesanan->pelanggan?->nama ?: 'Pelanggan tidak diketahui');
    }
}
