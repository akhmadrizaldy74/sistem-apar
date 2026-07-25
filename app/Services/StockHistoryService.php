<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\PurchaseOrderDetail;
use App\Models\Service;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StockHistoryService
{
    public function recent(int $limit = 30, ?string $tanggalDari = null, ?string $tanggalSampai = null): Collection
    {
        $entries = collect();

        $this->appendPurchaseEntries($entries);
        $this->appendProductSaleEntries($entries);
        $this->appendRefillUsageEntries($entries);
        $this->appendServicePeralatanEntries($entries);

        return $entries
            ->filter(fn (object $entry) => $this->withinDateRange($entry->tanggal, $tanggalDari, $tanggalSampai))
            ->sortByDesc(fn (object $entry) => $entry->tanggal?->getTimestamp() ?? 0)
            ->values()
            ->take($limit);
    }

    private function appendPurchaseEntries(Collection $entries): void
    {
        PurchaseOrderDetail::with(['purchaseOrder.supplier'])
            ->latest()
            ->get()
            ->each(function (PurchaseOrderDetail $detail) use ($entries) {
                $po = $detail->purchaseOrder;
                if (! $po) {
                    return;
                }

                $entries->push($this->makeEntry(
                    tanggal: $po->tanggal_po ?: $po->created_at,
                    itemTypeLabel: match ($detail->kategori) {
                        'produk' => 'Pembelian Stok APAR',
                        'refill' => 'Pembelian Stok Refill',
                        'peralatan' => 'Pembelian Peralatan Service',
                        default => 'Pembelian Stok',
                    },
                    itemName: $detail->nama_item,
                    sourceLabel: 'Purchase Order ('.$po->nomor_po.')',
                    sourceDetail: 'Supplier: '.($po->supplier?->nama_supplier ?: '-'),
                    flowLabel: 'Stok masuk',
                    movementType: StockMovement::MOVE_IN,
                    qty: (float) ($detail->jumlah ?? 0),
                    satuan: 'Item',
                    deskripsi: 'Pembelian barang via PO '.$po->nomor_po,
                    detailUrl: route('admin.purchase-orders.show', $po->id)
                ));
            });
    }

    private function appendProductSaleEntries(Collection $entries): void
    {
        Pesanan::with(['pelanggan', 'details.produk'])
            ->where('tipe', 'produk')
            ->whereNotIn('status', [Pesanan::STATUS_DITOLAK])
            ->latest('tanggal')
            ->latest()
            ->get()
            ->each(function (Pesanan $pesanan) use ($entries) {
                foreach ($pesanan->details as $detail) {
                    $entries->push($this->makeEntry(
                        tanggal: $pesanan->displayTransactionDateTime() ? Carbon::parse($pesanan->tanggal) : $pesanan->created_at,
                        itemTypeLabel: 'Penjualan APAR',
                        itemName: (string) ($detail->produk?->nama ?: $detail->merek ?: 'Unit APAR'),
                        sourceLabel: 'Transaksi Pesanan',
                        sourceDetail: 'Pelanggan: '.($pesanan->pelanggan?->nama ?: 'Umum'),
                        flowLabel: 'Stok keluar',
                        movementType: StockMovement::MOVE_OUT,
                        qty: (float) ($detail->jumlah ?? 0),
                        satuan: 'Unit',
                        deskripsi: 'Penjualan produk APAR kepada pelanggan.',
                        detailUrl: route('admin.pesanan.show', $pesanan)
                    ));
                }
            });
    }

    private function appendRefillUsageEntries(Collection $entries): void
    {
        Pesanan::with(['pelanggan', 'serviceJenisRefill'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereNotIn('status', [Pesanan::STATUS_DITOLAK])
            ->latest('tanggal')
            ->get()
            ->each(function (Pesanan $pesanan) use ($entries) {
                $refillNama = $pesanan->serviceJenisRefill?->nama ?: 'Bahan Refill';
                $unitCount = max(1, (int) ($pesanan->service_jumlah_unit ?? 1));

                $entries->push($this->makeEntry(
                    tanggal: $pesanan->created_at,
                    itemTypeLabel: 'Isi Ulang Refill APAR',
                    itemName: $refillNama,
                    sourceLabel: 'Layanan Refill',
                    sourceDetail: 'Pelanggan: '.($pesanan->pelanggan?->nama ?: 'Umum'),
                    flowLabel: 'Pengurangan stok refill',
                    movementType: StockMovement::MOVE_OUT,
                    qty: (float) $unitCount,
                    satuan: 'Kg',
                    deskripsi: 'Penggunaan stok refill untuk pesanan pelanggan.',
                    detailUrl: route('admin.pesanan.show', $pesanan)
                ));
            });
    }

    private function appendServicePeralatanEntries(Collection $entries): void
    {
        Service::with(['pesanan.pelanggan', 'servicePaket.peralatans'])
            ->latest('tgl_service')
            ->get()
            ->each(function (Service $service) use ($entries) {
                $peralatans = $service->servicePaket?->peralatans ?? collect();

                foreach ($peralatans as $peralatan) {
                    $qtyPerUnit = (int) ($peralatan->pivot?->jumlah ?? 1);

                    $entries->push($this->makeEntry(
                        tanggal: $service->tgl_service ? Carbon::parse($service->tgl_service) : $service->created_at,
                        itemTypeLabel: 'Pemakaian Peralatan Service',
                        itemName: $peralatan->nama,
                        sourceLabel: 'Pekerjaan Service',
                        sourceDetail: 'Pelanggan: '.($service->pesanan?->pelanggan?->nama ?: 'Umum'),
                        flowLabel: 'Stok keluar',
                        movementType: StockMovement::MOVE_OUT,
                        qty: (float) $qtyPerUnit,
                        satuan: 'Pcs',
                        deskripsi: 'Penggunaan peralatan service untuk unit pelanggan.',
                        detailUrl: $service->pesanan ? route('admin.pesanan.show', $service->pesanan) : null
                    ));
                }
            });
    }

    private function makeEntry(
        ?Carbon $tanggal,
        string $itemTypeLabel,
        string $itemName,
        string $sourceLabel,
        string $sourceDetail,
        string $flowLabel,
        string $movementType,
        float $qty,
        string $satuan,
        string $deskripsi,
        ?string $detailUrl = null
    ): object {
        $tanggalObj = $tanggal instanceof Carbon ? $tanggal : ($tanggal ? Carbon::parse($tanggal) : now());

        return (object) [
            'tanggal' => $tanggalObj,
            'tanggalLabel' => $tanggalObj->translatedFormat('d M Y H:i'),
            'itemTypeLabel' => $itemTypeLabel,
            'item_type_label' => $itemTypeLabel,
            'itemName' => $itemName,
            'item_name' => $itemName,
            'item_nama' => $itemName,
            'sourceLabel' => $sourceLabel,
            'source_label' => $sourceLabel,
            'sourceDetail' => $sourceDetail,
            'source_detail' => $sourceDetail,
            'flowLabel' => $flowLabel,
            'flow_label' => $flowLabel,
            'movementType' => $movementType,
            'movement_type' => $movementType,
            'qty' => $qty,
            'satuan' => $satuan,
            'deskripsi' => $deskripsi,
            'keterangan' => $deskripsi,
            'detailUrl' => $detailUrl,
            'detail_url' => $detailUrl,
        ];
    }

    private function withinDateRange(?Carbon $tanggal, ?string $tanggalDari, ?string $tanggalSampai): bool
    {
        if (! $tanggal) {
            return true;
        }

        if ($tanggalDari && $tanggal->toDateString() < $tanggalDari) {
            return false;
        }

        if ($tanggalSampai && $tanggal->toDateString() > $tanggalSampai) {
            return false;
        }

        return true;
    }
}
