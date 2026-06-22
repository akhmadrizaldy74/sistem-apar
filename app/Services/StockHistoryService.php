<?php

namespace App\Services;

use App\Models\Pengeluaran;
use App\Models\Pesanan;
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
        Pengeluaran::with(['produk', 'jenisRefill', 'peralatan'])
            ->whereNotNull('jenis_pengeluaran')
            ->latest('tanggal')
            ->latest()
            ->get()
            ->each(function (Pengeluaran $pengeluaran) use ($entries) {
                $itemName = (string) ($pengeluaran->display_item_name ?: $pengeluaran->nama_item ?: 'Item stok');
                $deskripsi = match ($pengeluaran->jenis_pengeluaran) {
                    Pengeluaran::JENIS_PEMBELIAN_APAR => 'Pembelian stok APAR dicatat lewat menu Pengeluaran.',
                    Pengeluaran::JENIS_PEMBELIAN_REFILL => 'Pembelian stok refill dicatat lewat menu Pengeluaran.',
                    Pengeluaran::JENIS_PEMBELIAN_PERALATAN => 'Pembelian peralatan service dicatat lewat menu Pengeluaran.',
                    default => 'Pengeluaran operasional stok dicatat admin.',
                };

                $entries->push($this->makeEntry(
                    tanggal: $pengeluaran->tanggal,
                    itemTypeLabel: match ($pengeluaran->jenis_pengeluaran) {
                        Pengeluaran::JENIS_PEMBELIAN_APAR => 'Pembelian Stok APAR',
                        Pengeluaran::JENIS_PEMBELIAN_REFILL => 'Pembelian Stok Refill',
                        Pengeluaran::JENIS_PEMBELIAN_PERALATAN => 'Pembelian Peralatan Service',
                        default => 'Pengeluaran Stok',
                    },
                    itemName: $itemName,
                    sourceLabel: 'Menu Pengeluaran',
                    sourceDetail: 'Admin mencatat stok masuk',
                    flowLabel: 'Stok masuk',
                    movementType: StockMovement::MOVE_IN,
                    qty: (float) ($pengeluaran->qty ?? 0),
                    satuan: (string) ($pengeluaran->satuan ?: 'Unit'),
                    keterangan: $this->appendNote($deskripsi, $pengeluaran->keterangan, $itemName),
                ));
            });
    }

    private function appendProductSaleEntries(Collection $entries): void
    {
        Pesanan::with(['pelanggan', 'details.produk'])
            ->where('tipe', 'produk')
            ->where('stok_dikurangi', true)
            ->latest('tanggal')
            ->latest()
            ->get()
            ->each(function (Pesanan $pesanan) use ($entries) {
                foreach ($pesanan->details as $detail) {
                    $itemName = (string) ($detail->produk?->nama ?: $detail->merek ?: 'Produk APAR');

                    $entries->push($this->makeEntry(
                        tanggal: $pesanan->pembayaran_terkonfirmasi_at ?: $pesanan->updated_at ?: $pesanan->tanggal,
                        itemTypeLabel: 'Penjualan Produk APAR',
                        itemName: $itemName,
                        sourceLabel: 'Pesanan Pelanggan',
                        sourceDetail: 'Stok keluar setelah pembayaran valid',
                        flowLabel: 'Stok keluar',
                        movementType: StockMovement::MOVE_OUT,
                        qty: (float) ($detail->jumlah ?? 0),
                        satuan: 'Unit',
                        keterangan: 'Stok keluar karena pembelian pelanggan ' . $this->customerName($pesanan) . '.',
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
            ->where('stok_dikurangi', true)
            ->latest('tanggal')
            ->latest()
            ->get()
            ->each(function (Pesanan $pesanan) use ($entries) {
                $entries->push($this->makeEntry(
                    tanggal: $pesanan->pembayaran_terkonfirmasi_at ?: $pesanan->updated_at ?: $pesanan->tanggal,
                    itemTypeLabel: 'Pemakaian Stok Refill',
                    itemName: (string) ($pesanan->serviceJenisRefill?->nama_label ?: 'Refill APAR'),
                    sourceLabel: 'Pesanan Refill',
                    sourceDetail: 'Refill unit pelanggan selesai diproses',
                    flowLabel: 'Stok keluar',
                    movementType: StockMovement::MOVE_OUT,
                    qty: (float) ($pesanan->service_total_kg ?? 0),
                    satuan: (string) ($pesanan->serviceJenisRefill?->satuan_label ?: 'Kg'),
                    keterangan: 'Stok refill berkurang untuk refill unit pelanggan ' . $this->customerName($pesanan) . '.',
                ));
            });
    }

    private function appendServicePeralatanEntries(Collection $entries): void
    {
        Service::query()
            ->where('status_konfirmasi', 'confirmed')
            ->whereHas('pesanan', fn ($query) => $query->where('stok_dikurangi', true))
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
                        itemTypeLabel: 'Pemakaian Peralatan Service',
                        itemName: (string) ($item['nama'] ?? 'Peralatan'),
                        sourceLabel: 'Pesanan Service',
                        sourceDetail: 'Peralatan dipakai saat service pelanggan',
                        flowLabel: 'Stok keluar',
                        movementType: StockMovement::MOVE_OUT,
                        qty: $qty,
                        satuan: 'Unit',
                        keterangan: 'Stok peralatan berkurang untuk service pelanggan ' . $this->customerName($service) . '.',
                    ));
                }
            });
    }

    private function makeEntry(
        Carbon|string|null $tanggal,
        string $itemTypeLabel,
        string $itemName,
        string $sourceLabel,
        ?string $sourceDetail,
        ?string $flowLabel,
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
            'source_detail' => $sourceDetail,
            'flow_label' => $flowLabel,
            'movement_type' => $movementType,
            'qty' => $qty,
            'satuan' => $satuan,
            'keterangan' => $keterangan,
        ];
    }

    private function appendNote(string $baseText, ?string $note, ?string $ignoreIfSame = null): string
    {
        $baseText = trim($baseText);
        if ($baseText !== '' && !str_ends_with($baseText, '.')) {
            $baseText .= '.';
        }

        $note = trim((string) $note);
        $ignoreIfSame = trim((string) $ignoreIfSame);

        if ($note === '' || strcasecmp($note, $ignoreIfSame) === 0) {
            return $baseText;
        }

        return trim($baseText . ' Catatan: ' . $note);
    }

    private function withinDateRange(?Carbon $tanggal, ?string $tanggalDari, ?string $tanggalSampai): bool
    {
        if (! $tanggal) {
            return true;
        }

        if ($tanggalDari && $tanggal->lt(Carbon::parse($tanggalDari)->startOfDay())) {
            return false;
        }

        if ($tanggalSampai && $tanggal->gt(Carbon::parse($tanggalSampai)->endOfDay())) {
            return false;
        }

        return true;
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
