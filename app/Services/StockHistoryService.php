<?php

namespace App\Services;

use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\TugasRefill;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StockHistoryService
{
    public function recent(int $limit = 30): Collection
    {
        $entries = collect();

        $this->appendPurchaseEntries($entries);
        $this->appendProductSaleEntries($entries);
        $this->appendRefillUsageEntries($entries);
        $this->appendServicePeralatanEntries($entries);
        $this->appendInternalRefillEntries($entries);

        return $entries
            ->sortByDesc(fn (object $entry) => $entry->tanggal?->getTimestamp() ?? 0)
            ->values()
            ->take($limit);
    }

    private function appendPurchaseEntries(Collection $entries): void
    {
        Pengeluaran::with(['produk', 'jenisRefill', 'peralatan'])
            ->whereNotNull('jenis_pengeluaran')
            ->latest('tanggal')
            ->latest()
            ->get()
            ->each(function (Pengeluaran $pengeluaran) use ($entries) {
                $entries->push($this->makeEntry(
                    tanggal: $pengeluaran->tanggal,
                    itemTypeLabel: match ($pengeluaran->jenis_pengeluaran) {
                        Pengeluaran::JENIS_PEMBELIAN_APAR => 'Produk APAR',
                        Pengeluaran::JENIS_PEMBELIAN_REFILL => 'Media Refil',
                        Pengeluaran::JENIS_PEMBELIAN_PERALATAN => 'Peralatan',
                        default => 'Stok',
                    },
                    itemName: $pengeluaran->display_item_name,
                    sourceLabel: 'Pembelian dari Pengeluaran',
                    movementType: StockMovement::MOVE_IN,
                    qty: (float) ($pengeluaran->qty ?? 0),
                    satuan: (string) ($pengeluaran->satuan ?: 'Unit'),
                    keterangan: 'Pengeluaran Stok - ' . ($pengeluaran->keterangan ?: $pengeluaran->display_item_name),
                ));
            });
    }

    private function appendProductSaleEntries(Collection $entries): void
    {
        Pesanan::with(['pelanggan', 'details.produk'])
            ->where('tipe', 'produk')
            ->where('stok_dikurangi', true)
            ->where('status', Pesanan::STATUS_SELESAI_FINAL)
            ->latest('tanggal')
            ->latest()
            ->get()
            ->each(function (Pesanan $pesanan) use ($entries) {
                foreach ($pesanan->details as $detail) {
                    $entries->push($this->makeEntry(
                        tanggal: $pesanan->pembayaran_terkonfirmasi_at ?: $pesanan->updated_at ?: $pesanan->tanggal,
                        itemTypeLabel: 'Produk APAR',
                        itemName: (string) ($detail->produk?->nama ?: $detail->merek ?: 'Produk APAR'),
                        sourceLabel: 'Penjualan Produk',
                        movementType: StockMovement::MOVE_OUT,
                        qty: (float) ($detail->jumlah ?? 0),
                        satuan: 'Unit',
                        keterangan: 'Pesanan Produk - ' . $this->customerName($pesanan),
                    ));
                }
            });
    }

    private function appendRefillUsageEntries(Collection $entries): void
    {
        Pesanan::with(['pelanggan', 'serviceJenisRefill'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereNotNull('service_jenis_refill_id')
            ->where('service_total_kg', '>', 0)
            ->where('status', Pesanan::STATUS_SELESAI_FINAL)
            ->where('stok_dikurangi', true)
            ->latest('tanggal')
            ->latest()
            ->get()
            ->each(function (Pesanan $pesanan) use ($entries) {
                $entries->push($this->makeEntry(
                    tanggal: $pesanan->pembayaran_terkonfirmasi_at ?: $pesanan->updated_at ?: $pesanan->tanggal,
                    itemTypeLabel: 'Media Refil',
                    itemName: (string) ($pesanan->serviceJenisRefill?->nama_label ?: 'Refill APAR'),
                    sourceLabel: 'Refill Pelanggan',
                    movementType: StockMovement::MOVE_OUT,
                    qty: (float) ($pesanan->service_total_kg ?? 0),
                    satuan: (string) ($pesanan->serviceJenisRefill?->satuan_label ?: 'Kg'),
                    keterangan: 'Refill APAR - ' . $this->customerName($pesanan),
                ));
            });
    }

    private function appendServicePeralatanEntries(Collection $entries): void
    {
        Service::query()
            ->where('status_konfirmasi', 'confirmed')
            ->whereHas('pesanan', fn ($query) => $query->where('status', Pesanan::STATUS_SELESAI_FINAL))
            ->where(function ($query) {
                $query->whereNotNull('actual_peralatan_json')
                    ->orWhereNotNull('estimasi_peralatan_json');
            })
            ->with(['pesanan.pelanggan', 'unitApar.pelanggan'])
            ->latest('tgl_selesai_admin')
            ->latest()
            ->get()
            ->each(function (Service $service) use ($entries) {
                foreach ($service->effective_peralatan as $item) {
                    $qty = (float) ($item['jumlah'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $entries->push($this->makeEntry(
                        tanggal: $service->tgl_selesai_admin ?: $service->updated_at ?: $service->tgl_service,
                        itemTypeLabel: 'Peralatan',
                        itemName: (string) ($item['nama'] ?? 'Peralatan'),
                        sourceLabel: 'Service Pelanggan',
                        movementType: StockMovement::MOVE_OUT,
                        qty: $qty,
                        satuan: 'Unit',
                        keterangan: 'Service APAR - ' . $this->customerName($service),
                    ));
                }
            });
    }

    private function appendInternalRefillEntries(Collection $entries): void
    {
        TugasRefill::with('produk')
            ->where('status', 'selesai')
            ->latest('tanggal_refill')
            ->latest()
            ->get()
            ->each(function (TugasRefill $tugasRefill) use ($entries) {
                $entries->push($this->makeEntry(
                    tanggal: $tugasRefill->tanggal_refill ?: $tugasRefill->updated_at,
                    itemTypeLabel: 'Produk APAR',
                    itemName: (string) ($tugasRefill->produk?->nama ?: 'Produk APAR'),
                    sourceLabel: 'Hasil Refill Batch',
                    movementType: StockMovement::MOVE_IN,
                    qty: (float) ($tugasRefill->jumlah_refill ?? 0),
                    satuan: 'Unit',
                    keterangan: 'Batch hasil refill teknisi',
                ));
            });
    }

    private function makeEntry(
        Carbon|string|null $tanggal,
        string $itemTypeLabel,
        string $itemName,
        string $sourceLabel,
        string $movementType,
        float $qty,
        string $satuan,
        ?string $keterangan = null,
    ): object {
        return (object) [
            'tanggal' => $tanggal instanceof Carbon
                ? $tanggal
                : ($tanggal ? Carbon::parse($tanggal) : null),
            'item_type_label' => $itemTypeLabel,
            'item_nama' => $itemName,
            'source_label' => $sourceLabel,
            'movement_type' => $movementType,
            'qty' => $qty,
            'satuan' => $satuan,
            'keterangan' => $keterangan,
        ];
    }

    private function customerName(Pesanan|Service $source): string
    {
        if ($source instanceof Pesanan) {
            return (string) ($source->pelanggan?->nama ?: 'Pelanggan tidak diketahui');
        }

        return (string) ($source->pesanan?->pelanggan?->nama
            ?: $source->unitApar?->pelanggan?->nama
            ?: 'Pelanggan tidak diketahui');
    }
}
