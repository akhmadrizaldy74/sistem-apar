<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\StokBatch;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Tampilkan daftar Purchase Order.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $supplierId = $request->input('supplier_id');

        $query = PurchaseOrder::with(['supplier', 'details']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                  ->orWhere('no_surat_jalan', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('nama_supplier', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($status) && in_array($status, ['draft', 'dikirim', 'diterima'], true)) {
            $query->where('status', $status);
        }

        if (!empty($supplierId)) {
            $query->where('supplier_id', $supplierId);
        }

        $purchaseOrders = $query->latest()->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        $allPoDetails = PurchaseOrderDetail::whereHas('purchaseOrder')->get();
        $stats = [
            'total_pembelian' => (float) PurchaseOrder::sum('total'),
            'total_transaksi' => PurchaseOrder::count(),
            'total_apar'      => (float) $allPoDetails->where('kategori', 'produk')->sum('jumlah'),
            'total_refill'    => (float) $allPoDetails->where('kategori', 'refill')->sum('jumlah'),
            'total_peralatan'  => (float) $allPoDetails->where('kategori', 'peralatan')->sum('jumlah'),
        ];

        return view('admin.purchase-order.index', compact('purchaseOrders', 'suppliers', 'search', 'status', 'supplierId', 'stats'));
    }

    /**
     * Tampilkan form buat PO baru.
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::where('status', 'aktif')->orderBy('nama_supplier')->get();
        $produks = Produk::orderBy('nama')->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $peralatans = Peralatan::orderBy('nama')->get();

        $selectedKategori = $request->query('kategori', 'produk');
        if (!in_array($selectedKategori, ['produk', 'refill', 'peralatan'], true)) {
            $selectedKategori = 'produk';
        }

        $selectedProdukId = $request->query('produk_id');
        $selectedRefillId = $request->query('jenis_refill_id');
        $selectedPeralatanId = $request->query('peralatan_id');

        $selectedItemNama = '';
        if ($selectedKategori === 'produk' && $selectedProdukId) {
            $p = Produk::with('jenisApar')->find($selectedProdukId);
            if ($p) {
                $jenisNama = $p->jenisApar?->nama;
                $selectedItemNama = $p->nama;
                if ($jenisNama && !str_contains(strtolower($p->nama), strtolower($jenisNama))) {
                    $selectedItemNama .= ' - ' . $jenisNama;
                }
            }
        } elseif ($selectedKategori === 'refill' && $selectedRefillId) {
            $r = JenisRefill::find($selectedRefillId);
            if ($r) {
                $satuan = $r->satuan_label ?? $r->satuan ?? '';
                $selectedItemNama = $r->nama . ($satuan ? ' - ' . $satuan : '');
            }
        } elseif ($selectedKategori === 'peralatan' && $selectedPeralatanId) {
            $pr = Peralatan::find($selectedPeralatanId);
            if ($pr) {
                $selectedItemNama = $pr->nama;
            }
        }

        return view('admin.purchase-order.create', compact(
            'suppliers', 'produks', 'jenisRefills', 'peralatans',
            'selectedKategori', 'selectedItemNama'
        ));
    }

    /**
     * Simpan Purchase Order baru (status default: draft).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'tanggal_po'           => 'required|date',
            'catatan'              => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.nama_item'    => 'required|string|max:255',
            'items.*.kategori'     => 'required|in:produk,refill,peralatan',
            'items.*.jumlah'       => 'required|numeric|min:0.01',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ], [
            'supplier_id.required'       => 'Supplier wajib dipilih.',
            'supplier_id.exists'         => 'Supplier tidak valid.',
            'tanggal_po.required'        => 'Tanggal PO wajib diisi.',
            'items.required'             => 'Minimal 1 item pembelian harus ditambahkan.',
            'items.min'                  => 'Minimal 1 item pembelian harus ditambahkan.',
            'items.*.nama_item.required' => 'Nama item wajib diisi.',
            'items.*.kategori.required'  => 'Kategori item wajib dipilih.',
            'items.*.jumlah.required'    => 'Jumlah item wajib diisi.',
            'items.*.harga_satuan.required' => 'Harga satuan wajib diisi.',
        ]);

        DB::transaction(function () use ($validated, &$po) {
            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['jumlah'] * (float) $item['harga_satuan'];
                $total += $subtotal;
            }

            $po = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'tanggal_po'  => $validated['tanggal_po'],
                'catatan'     => $validated['catatan'] ?? null,
                'total'       => $total,
                'status'      => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['jumlah'] * (float) $item['harga_satuan'];
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $po->id,
                    'nama_item'         => $item['nama_item'],
                    'kategori'          => $item['kategori'],
                    'jumlah'            => $item['jumlah'],
                    'harga_satuan'      => $item['harga_satuan'],
                    'subtotal'          => $subtotal,
                ]);
            }
        });

        return redirect()
            ->route('admin.purchase-orders.show', $po->id)
            ->with('success', "Purchase Order {$po->nomor_po} berhasil dibuat dengan status Draft.");
    }

    /**
     * Tampilkan detail Purchase Order.
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'details'])->findOrFail($id);
        return view('admin.purchase-order.show', compact('purchaseOrder'));
    }

    /**
     * Tampilkan form edit PO (hanya jika draft).
     */
    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'details'])->findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Hanya Purchase Order berstatus Draft yang dapat diedit.');
        }

        $suppliers = Supplier::where('status', 'aktif')->orderBy('nama_supplier')->get();
        $produks = Produk::orderBy('nama')->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $peralatans = Peralatan::orderBy('nama')->get();

        return view('admin.purchase-order.edit', compact('purchaseOrder', 'suppliers', 'produks', 'jenisRefills', 'peralatans'));
    }

    /**
     * Update Purchase Order (hanya jika draft).
     */
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Hanya Purchase Order berstatus Draft yang dapat diedit.');
        }

        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'tanggal_po'           => 'required|date',
            'catatan'              => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.nama_item'    => 'required|string|max:255',
            'items.*.kategori'     => 'required|in:produk,refill,peralatan',
            'items.*.jumlah'       => 'required|numeric|min:0.01',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['jumlah'] * (float) $item['harga_satuan'];
                $total += $subtotal;
            }

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'tanggal_po'  => $validated['tanggal_po'],
                'catatan'     => $validated['catatan'] ?? null,
                'total'       => $total,
            ]);

            $purchaseOrder->details()->delete();

            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['jumlah'] * (float) $item['harga_satuan'];
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'nama_item'         => $item['nama_item'],
                    'kategori'          => $item['kategori'],
                    'jumlah'            => $item['jumlah'],
                    'harga_satuan'      => $item['harga_satuan'],
                    'subtotal'          => $subtotal,
                ]);
            }
        });

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder->id)
            ->with('success', "Purchase Order {$purchaseOrder->nomor_po} berhasil diperbarui.");
    }

    /**
     * Hapus Purchase Order (hanya jika draft).
     */
    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Hanya Purchase Order berstatus Draft yang dapat dihapus.');
        }

        $purchaseOrder->delete();

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', "Purchase Order {$purchaseOrder->nomor_po} berhasil dihapus.");
    }

    /**
     * Ubah status PO ke dikirim & siapkan link WhatsApp supplier.
     */
    public function kirim($id)
    {
        $po = PurchaseOrder::with('supplier')->findOrFail($id);

        if ($po->status === 'draft') {
            $po->update(['status' => 'dikirim']);
        }

        $phone = $po->supplier?->no_wa ?: $po->supplier?->telepon ?: '';
        $noWaClean = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($noWaClean, '0')) {
            $noWaClean = '62' . substr($noWaClean, 1);
        }

        $tanggalFormatted = $po->tanggal_po ? $po->tanggal_po->format('d/m/Y') : date('d/m/Y');
        $pesan = "Halo {$po->supplier?->nama_supplier}, berikut Purchase Order dari PD Anugrah Utama dengan No. PO: {$po->nomor_po} tanggal {$tanggalFormatted}. Mohon segera diproses. Terima kasih.";

        $waUrl = $noWaClean !== '' ? "https://wa.me/{$noWaClean}?text=" . urlencode($pesan) : null;

        return redirect()
            ->route('admin.purchase-orders.show', $po->id)
            ->with('success', "Status Purchase Order {$po->nomor_po} berhasil diubah ke Dikirim.")
            ->with('wa_url', $waUrl);
    }

    /**
     * Link langsung ke WhatsApp supplier.
     */
    public function kirimWA($id)
    {
        return $this->kirim($id);
    }

    /**
     * Form input Surat Jalan.
     */
    public function inputSuratJalan($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'details'])->findOrFail($id);
        return view('admin.purchase-order.surat-jalan', compact('purchaseOrder'));
    }

    /**
     * Simpan Surat Jalan (No & Tanggal).
     */
    public function simpanSuratJalan(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        $validated = $request->validate([
            'no_surat_jalan'      => 'required|string|max:255',
            'tanggal_surat_jalan' => 'required|date',
        ], [
            'no_surat_jalan.required'      => 'Nomor surat jalan wajib diisi.',
            'tanggal_surat_jalan.required' => 'Tanggal surat jalan wajib diisi.',
        ]);

        $po->update([
            'no_surat_jalan'      => $validated['no_surat_jalan'],
            'tanggal_surat_jalan' => $validated['tanggal_surat_jalan'],
        ]);

        return redirect()
            ->route('admin.purchase-orders.show', $po->id)
            ->with('success', 'Data Surat Jalan berhasil disimpan.');
    }

    /**
     * Konfirmasi penerimaan barang, update stok & pengeluaran.
     */
    public function konfirmasiTerima($id)
    {
        $po = PurchaseOrder::with(['supplier', 'details'])->findOrFail($id);

        if ($po->status !== 'dikirim') {
            return redirect()
                ->route('admin.purchase-orders.show', $po->id)
                ->with('error', 'Status Purchase Order harus Dikirim terlebih dahulu sebelum konfirmasi penerimaan.');
        }

        if (empty($po->no_surat_jalan) || empty($po->tanggal_surat_jalan)) {
            return redirect()
                ->route('admin.purchase-orders.show', $po->id)
                ->with('error', 'Nomor dan Tanggal Surat Jalan wajib diisi terlebih dahulu sebelum konfirmasi terima barang.');
        }

        try {
            DB::transaction(function () use ($po) {
                $po->update(['status' => 'diterima']);

                foreach ($po->details as $detail) {
                    $kategori = strtolower(trim((string) $detail->kategori));

                    if ($kategori === 'produk') {
                        $cleanNama = explode(' - ', $detail->nama_item)[0];
                        $produk = Produk::where('nama', 'like', "%{$cleanNama}%")
                            ->orWhere('nama', 'like', "%{$detail->nama_item}%")
                            ->first() ?? Produk::first();

                        if ($produk) {
                            StokBatch::create([
                                'produk_id'         => $produk->id,
                                'jumlah_masuk'      => (int) $detail->jumlah,
                                'sisa_qty'          => (int) $detail->jumlah,
                                'tgl_produksi'      => $po->tanggal_po ?? now(),
                                'tgl_expired'       => now()->addMonths(12),
                                'keterangan'        => "Penerimaan PO {$po->nomor_po} (Surat Jalan: {$po->no_surat_jalan})",
                                'sumber'            => 'purchase_order',
                                'purchase_order_id' => $po->id,
                            ]);
                        }
                    } elseif ($kategori === 'refill') {
                        $cleanNama = explode(' - ', $detail->nama_item)[0];
                        $jenisRefill = JenisRefill::where('nama', 'like', "%{$cleanNama}%")
                            ->orWhere('nama', 'like', "%{$detail->nama_item}%")
                            ->first();

                        if ($jenisRefill) {
                            $jenisRefill->increment('stok', (float) $detail->jumlah);
                        }
                    } elseif ($kategori === 'peralatan') {
                        $peralatan = Peralatan::where('nama', 'like', "%{$detail->nama_item}%")->first();
                        if ($peralatan) {
                            $peralatan->increment('stok', (int) $detail->jumlah);
                        }
                    }
                }
            });

            return redirect()
                ->route('admin.purchase-orders.show', $po->id)
                ->with('success', 'Barang berhasil diterima. Stok telah diperbarui otomatis.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('admin.purchase-orders.show', $po->id)
                ->with('error', 'Terjadi kesalahan saat memperbarui stok. Silakan coba lagi.');
        }
    }

    /**
     * Cetak PDF Purchase Order.
     */
    public function cetakPDF($id)
    {
        $po = PurchaseOrder::with(['supplier', 'details'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.purchase-order', compact('po'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("PURCHASE_ORDER_{$po->nomor_po}.pdf");
    }

    /**
     * Fetch list item berdasarkan kategori untuk dropdown PO.
     */
    public function getItems(string $kategori)
    {
        $kategori = strtolower(trim($kategori));

        if ($kategori === 'produk') {
            $items = Produk::with('jenisApar')->get()->map(function (Produk $p) {
                $jenisNama = $p->jenisApar?->nama;
                $namaDisplay = $p->nama;
                if ($jenisNama && !str_contains(strtolower($p->nama), strtolower($jenisNama))) {
                    $namaDisplay .= ' - ' . $jenisNama;
                }
                return [
                    'id'     => $p->id,
                    'nama'   => $namaDisplay,
                    'harga'  => (float) $p->harga,
                ];
            });
        } elseif ($kategori === 'refill') {
            $items = JenisRefill::all()->map(function (JenisRefill $r) {
                $satuan = $r->satuan_label ?? $r->satuan ?? '';
                $namaDisplay = $r->nama . ($satuan ? ' - ' . $satuan : '');
                return [
                    'id'     => $r->id,
                    'nama'   => $namaDisplay,
                    'harga'  => (float) ($r->harga ?? 0),
                ];
            });
        } else { // peralatan
            $items = Peralatan::all()->map(function (Peralatan $p) {
                return [
                    'id'     => $p->id,
                    'nama'   => $p->nama,
                    'harga'  => (float) ($p->harga_standar ?? 0),
                ];
            });
        }

        return response()->json($items);
    }
}
