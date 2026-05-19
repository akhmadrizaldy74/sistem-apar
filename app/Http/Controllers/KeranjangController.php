<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeranjangController extends Controller
{

    /**
     * Tampilkan halaman keranjang belanja.
     */
    public function index()
    {
        $keranjangs = Keranjang::with('produk.jenisApar')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $totalHarga = $keranjangs->sum(fn ($item) => $item->harga * $item->qty);

        return view('public.keranjang.index', compact('keranjangs', 'totalHarga'));
    }

    /**
     * Tambah produk ke keranjang.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty'       => 'nullable|integer|min:1|max:999',
        ]);

        $produk = Produk::findOrFail($request->produk_id);
        $qty = $request->input('qty', 1);
        $stokTersedia = (int) $produk->stok_tersedia;

        if ($stokTersedia < $qty) {
            return back()->with('error', 'Stok siap jual tidak mencukupi. Stok tersedia: ' . $stokTersedia);
        }

        $keranjang = Keranjang::where('user_id', Auth::id())
            ->where('produk_id', $produk->id)
            ->where('tipe_item', 'produk')
            ->first();

        if ($keranjang) {
            $newQty = $keranjang->qty + $qty;
            if ($newQty > $stokTersedia) {
                return back()->with('error', 'Total qty melebihi stok siap jual (' . $stokTersedia . ').');
            }
            $keranjang->update(['qty' => $newQty]);
        } else {
            Keranjang::create([
                'user_id'   => Auth::id(),
                'produk_id' => $produk->id,
                'qty'       => $qty,
                'harga'     => $produk->harga,
                'tipe_item' => 'produk',
            ]);
        }

        return back()
            ->with('success', '✅ "' . $produk->nama . '" berhasil ditambahkan ke keranjang!');
    }

    /**
     * Update qty item di keranjang.
     */
    public function update(Request $request, Keranjang $keranjang)
    {
        if ($keranjang->user_id !== Auth::id()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Aksi tidak diizinkan.'], 403);
            }
            abort(403);
        }

        $request->validate([
            'qty' => 'required|integer|min:1|max:999',
        ]);

        $produk = $keranjang->produk;
        $stokTersedia = (int) $produk->stok_tersedia;

        if ($request->qty > $stokTersedia) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok ' . $produk->nama . ' tersedia: ' . $stokTersedia . ' unit.'
                ], 422);
            }
            return back()->with('error', 'Qty melebihi stok siap jual (' . $stokTersedia . ').');
        }

        $keranjang->update(['qty' => $request->qty]);

        if ($request->wantsJson() || $request->ajax()) {
            $allCart = Keranjang::where('user_id', Auth::id())->get();
            $cartTotal = $allCart->sum(fn ($item) => $item->harga * $item->qty);
            $cartCount = $allCart->sum('qty');
            $negotiationEligible = $cartCount >= 10;
            $remainingToNego = max(0, 10 - $cartCount);

            return response()->json([
                'success' => true,
                'message' => 'Qty berhasil diubah.',
                'item_qty' => $keranjang->qty,
                'item_subtotal' => $keranjang->qty * $keranjang->harga,
                'item_subtotal_formatted' => 'Rp ' . number_format($keranjang->qty * $keranjang->harga, 0, ',', '.'),
                'cart_total' => $cartTotal,
                'cart_total_formatted' => 'Rp ' . number_format($cartTotal, 0, ',', '.'),
                'cart_count' => $cartCount,
                'negotiation_eligible' => $negotiationEligible,
                'remaining_to_nego' => $remainingToNego
            ]);
        }

        return back()->with('success', 'Qty berhasil diubah.');
    }

    /**
     * Hapus item dari keranjang.
     */
    public function destroy(Keranjang $keranjang)
    {
        if ($keranjang->user_id !== Auth::id()) {
            abort(403);
        }

        $keranjang->delete();

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * API: Hitung jumlah item di keranjang (untuk badge).
     */
    public function count()
    {
        $count = Keranjang::where('user_id', Auth::id())->sum('qty');
        return response()->json(['count' => $count]);
    }
}
