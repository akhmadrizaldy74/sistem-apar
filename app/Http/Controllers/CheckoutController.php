<?php

namespace App\Http\Controllers;

use App\Events\PesananBaru;
use App\Models\Keranjang;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private function pendingPaymentOrderForUser(int $userId): ?Pesanan
    {
        $pelanggan = Pelanggan::where('user_id', $userId)->first();

        if (! $pelanggan) {
            return null;
        }

        return Pesanan::where('pelanggan_id', $pelanggan->id)
            ->whereNotIn('status', [
                Pesanan::STATUS_SELESAI,
                Pesanan::STATUS_SELESAI_FINAL,
                Pesanan::STATUS_DITOLAK,
            ])
            ->latest()
            ->get()
            ->first(fn (Pesanan $pesanan) => ! $pesanan->isPaymentConfirmed());
    }

    private function combineAddress(?string $mapsAddress, ?string $detailAddress): ?string
    {
        $parts = array_filter([
            trim((string) $mapsAddress),
            trim((string) $detailAddress),
        ], fn (string $value) => $value !== '');

        if (empty($parts)) {
            return null;
        }

        return implode(' | Detail: ', $parts);
    }

    private function resolvePelangganForUser(int $userId, string $normalizedPhone): Pelanggan
    {
        $pelanggan = Pelanggan::query()
            ->where('user_id', $userId)
            ->first();

        if ($pelanggan) {
            return $pelanggan;
        }

        $pelanggan = Pelanggan::query()
            ->where('no_wa', $normalizedPhone)
            ->where(function ($query) use ($userId) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $userId);
            })
            ->first();

        return $pelanggan ?: new Pelanggan();
    }

    /**
     * Tampilkan halaman checkout dengan form data pengiriman.
     * Data auto-fill dari Auth::user() + Pelanggan terkait.
     */
    public function index()
    {
        return redirect()->route('order.create')
            ->with('success', 'Checkout produk sekarang digabung ke halaman pemesanan agar alurnya lebih sederhana.');
    }

    /**
     * Proses checkout: buat pesanan dari keranjang.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($this->pendingPaymentOrderForUser($user->id)) {
            return redirect()
                ->route('riwayat-apar')
                ->with('warning', 'Selesaikan pembayaran sebelumnya sebelum membuat pesanan baru.');
        }

        $request->validate([
            'nama_penerima'     => 'required|string|max:255',
            'nomor_wa_penerima' => 'required|string|max:20',
            'alamat_pengiriman' => 'required|string|max:2000',
        ]);

        $normalizedPhone = $this->normalizePhone($request->nomor_wa_penerima);

        $keranjangs = Keranjang::with('produk')
            ->where('user_id', $user->id)
            ->get();

        if ($keranjangs->isEmpty()) {
            return redirect()->route('keranjang.index')
                ->with('error', 'Keranjang Anda kosong.');
        }

        // Validasi stok sebelum proses
        foreach ($keranjangs as $item) {
            if (!$item->produk) {
                return back()->with('error', 'Produk tidak ditemukan di keranjang.');
            }
            $stokTersedia = (int) $item->produk->stok_tersedia;
            if ($stokTersedia < $item->qty) {
                return back()->with('error', 'Stok siap jual "' . $item->produk->nama . '" tidak mencukupi. Tersedia: ' . $stokTersedia);
            }
        }

        DB::beginTransaction();
        try {
            // Sinkronkan profil pelanggan agar checkout berikutnya terisi otomatis.
            $pelanggan = $this->resolvePelangganForUser($user->id, $normalizedPhone);
            $pelanggan->fill([
                'user_id' => $user->id,
                'nama' => $request->nama_penerima,
                'no_wa' => $normalizedPhone,
                'alamat' => $request->alamat_pengiriman,
                'status' => $pelanggan->status ?: 'calon',
                'sumber_data' => $pelanggan->sumber_data ?: 'manual',
            ]);
            $pelanggan->save();

            // Generate no_pesanan
            $noPesanan = 'ORD-' . date('Ymd') . '-' . str_pad((string) (Pesanan::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

            // Buat pesanan master
            $pesanan = Pesanan::create([
                'pelanggan_id'      => $pelanggan->id,
                'user_id'           => $user->id,
                'no_pesanan'        => $noPesanan,
                'nama_penerima'     => $request->nama_penerima,
                'nomor_wa_penerima' => $normalizedPhone,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'tipe'              => 'produk',
                'sumber_pesanan'    => 'website',
                'status'            => 'pending',
                'tipe_harga'        => 'normal',
                'tanggal'           => now(),
                'metode_pengiriman' => 'pickup',
                'ongkir'            => 0,
                'total'             => 0,
                'total_harga'       => 0,
                'keterangan'        => 'Pesanan via Keranjang Belanja [' . $noPesanan . ']',
            ]);

            // Simpan detail dari keranjang
            $totalHarga = 0;
            foreach ($keranjangs as $item) {
                $subtotal = $item->harga * $item->qty;
                $totalHarga += $subtotal;

                PesananDetail::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id'  => $item->produk_id,
                    'merek'      => $item->produk->merek ?? 'SAFETY',
                    'kapasitas'  => $item->produk->kapasitas ?? '-',
                    'jumlah'     => $item->qty,
                    'harga'      => $item->harga,
                    'subtotal'   => $subtotal,
                ]);
            }

            // Update total harga pesanan
            $pesanan->update([
                'total'       => $totalHarga,
                'total_harga' => $totalHarga,
            ]);

            // Kosongkan keranjang user
            Keranjang::where('user_id', $user->id)->delete();

            DB::commit();

            // Broadcast ke admin
            try {
                broadcast(new PesananBaru($pesanan))->toOthers();
            } catch (\Exception $e) {
                // Broadcast gagal bukan masalah kritis
            }

            return redirect()->route('order.payment', $pesanan)
                ->with('success', '🎉 Pesanan berhasil dibuat! No. Pesanan: ' . $noPesanan . '. Silakan lanjutkan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }
        if (str_starts_with($digits, '8')) {
            return '0' . $digits;
        }
        return $digits;
    }
}
