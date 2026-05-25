<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Support\SessionCart;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    public function index()
    {
        $keranjangs = SessionCart::items();
        $totalHarga = $keranjangs->sum(fn ($item) => $item->harga * $item->qty);

        return view('public.keranjang.index', compact('keranjangs', 'totalHarga'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty' => 'nullable|integer|min:1|max:999',
        ]);

        $produk = Produk::findOrFail($request->produk_id);
        $qty = (int) $request->input('qty', 1);
        $stokTersedia = (int) $produk->stok_tersedia;

        if ($stokTersedia < $qty) {
            return back()->with('error', 'Stok siap jual tidak mencukupi. Stok tersedia: ' . $stokTersedia);
        }

        $existingQty = SessionCart::items()->firstWhere('produk_id', $produk->id)?->qty ?? 0;
        if (($existingQty + $qty) > $stokTersedia) {
            return back()->with('error', 'Total qty melebihi stok siap jual (' . $stokTersedia . ').');
        }

        SessionCart::add($produk, $qty);

        return back()->with('success', '"' . $produk->nama . '" berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, string $item)
    {
        $request->validate([
            'qty' => 'required|integer|min:1|max:999',
        ]);

        $produkId = (int) $item;
        $keranjang = SessionCart::items()->firstWhere('produk_id', $produkId);

        if (! $keranjang || ! $keranjang->produk) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Item keranjang tidak ditemukan.'], 404);
            }

            return back()->with('error', 'Item keranjang tidak ditemukan.');
        }

        $produk = $keranjang->produk;
        $stokTersedia = (int) $produk->stok_tersedia;

        if ((int) $request->qty > $stokTersedia) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok ' . $produk->nama . ' tersedia: ' . $stokTersedia . ' unit.',
                ], 422);
            }

            return back()->with('error', 'Qty melebihi stok siap jual (' . $stokTersedia . ').');
        }

        SessionCart::update($produkId, (int) $request->qty);
        $keranjang = SessionCart::items()->firstWhere('produk_id', $produkId);

        if ($request->wantsJson() || $request->ajax()) {
            $allCart = SessionCart::items();
            $cartTotal = $allCart->sum(fn ($cartItem) => $cartItem->harga * $cartItem->qty);
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
                'remaining_to_nego' => $remainingToNego,
            ]);
        }

        return back()->with('success', 'Qty berhasil diubah.');
    }

    public function destroy(string $item)
    {
        if (! SessionCart::remove((int) $item)) {
            return back()->with('error', 'Item keranjang tidak ditemukan.');
        }

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function count()
    {
        return response()->json(['count' => SessionCart::count()]);
    }
}
