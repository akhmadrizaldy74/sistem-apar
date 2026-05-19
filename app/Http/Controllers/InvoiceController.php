<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Tampilkan halaman detail Invoice (HTML).
     */
    public function show(Pesanan $pesanan)
    {
        $this->authorizeAccess($pesanan);

        $pesanan->load(['pelanggan', 'details.produk', 'servicePaket', 'serviceJenisRefill', 'teknisi', 'service.unitApar']);

        $isLunas = $this->checkIsLunas($pesanan);

        return view('public.invoice.show', compact('pesanan', 'isLunas'));
    }

    /**
     * Unduh PDF Invoice.
     */
    public function pdf(Pesanan $pesanan)
    {
        $this->authorizeAccess($pesanan);

        $pesanan->load(['pelanggan', 'details.produk', 'servicePaket', 'serviceJenisRefill', 'teknisi', 'service.unitApar']);

        $isLunas = $this->checkIsLunas($pesanan);

        $pdf = Pdf::loadView('public.invoice.pdf', compact('pesanan', 'isLunas'));
        
        $filename = 'invoice-' . strtolower($pesanan->tipe) . '-' . $pesanan->id . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Memastikan hak akses pengguna: Admin bisa melihat semua, pelanggan hanya miliknya.
     */
    protected function authorizeAccess(Pesanan $pesanan)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        // Cek jika pesanan milik user ini (melalui user_id atau user_id pelanggan)
        $ownerUserId = $pesanan->user_id ?? ($pesanan->pelanggan?->user_id ?? null);

        if ($ownerUserId === $user->id) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses untuk melihat invoice ini.');
    }

    /**
     * Menentukan apakah transaksi dianggap Lunas / Paid.
     */
    protected function checkIsLunas(Pesanan $pesanan): bool
    {
        // 1. Transaksi offline otomatis dianggap lunas
        if (in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin', 'telepon'], true)) {
            return true;
        }

        // 2. Pembayaran terkonfirmasi
        if ($pesanan->isPaymentConfirmed()) {
            return true;
        }

        // 3. Status selesai/selesai final biasanya lunas
        if ($pesanan->isCompleted()) {
            return true;
        }

        return false;
    }
}
