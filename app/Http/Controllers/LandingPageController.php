<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Testimoni;
use App\Models\UnitApar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    private function feedbackLinkDescription(Pelanggan $pelanggan, Pesanan $pesanan): string
    {
        return 'testimoni-order:' . $pesanan->id . ':pelanggan:' . $pelanggan->id;
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

    private function phoneCandidates(string $phone): array
    {
        $digits = preg_replace('/\D+/', '', $phone);
        $normalized = $this->normalizePhone($phone);

        $candidates = [];

        if ($digits !== '') {
            $candidates[] = $digits;
        }
        if ($normalized !== '') {
            $candidates[] = $normalized;
        }

        if (str_starts_with($digits, '62')) {
            $candidates[] = '0' . substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $candidates[] = '62' . substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            $candidates[] = '0' . $digits;
            $candidates[] = '62' . $digits;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    public function index()
    {
        $produks = Produk::with('jenisApar')
            ->latest()
            ->take(4)
            ->get();

        $testimonis = Testimoni::with('pelanggan')
            ->where('status', 'approved')
            ->latest('tanggal')
            ->take(6)
            ->get();

        return view('welcome', compact('produks', 'testimonis'));
    }

    public function cekAparForm()
    {
        $pelanggan = null;

        if (session()->has('pelanggan_id')) {
            $pelangganRaw = Pelanggan::find(session('pelanggan_id'));
            if ($pelangganRaw) {
                $this->syncUnitsForPelanggan($pelangganRaw);
            }
            $pelanggan = Pelanggan::with([
                'units.produk.jenisApar',
                'pesanan.details.produk.jenisApar',
                'pesanan.servicePaket',
                'pesanan.serviceJenisRefill',
            ])
                ->find(session('pelanggan_id'));
        }

        return view('public.cek-apar.index', compact('pelanggan'));
    }

    public function cekApar(Request $request)
    {
        $request->validate([
            'no_wa' => 'required|string',
        ]);

        $normalizedNoWa = $this->normalizePhone((string) $request->no_wa);
        $phoneCandidates = $this->phoneCandidates((string) $request->no_wa);

        $pelanggan = Pelanggan::whereIn('no_wa', $phoneCandidates)->first();

        if (! $pelanggan) {
            return redirect()
                ->route('cek-apar')
                ->withInput()
                ->with('error', 'Data tidak ditemukan. Pastikan nomor WhatsApp sudah benar.');
        }

        $this->syncUnitsForPelanggan($pelanggan);

        return redirect()
            ->route('cek-apar')
            ->withInput(['no_wa' => $normalizedNoWa])
            ->with('pelanggan_id', $pelanggan->id);
    }

    public function produkIndex(Request $request)
    {
        $filters = [
            'jenis_apar_id' => $request->string('jenis_apar_id')->toString() ?: null,
            'merek' => $request->string('merek')->toString() ?: null,
        ];

        $produks = Produk::with(['jenisApar', 'stokBatches'])
            ->when($filters['jenis_apar_id'], fn ($query, $jenisAparId) => $query->where('jenis_apar_id', $jenisAparId))
            ->when($filters['merek'], fn ($query, $merek) => $query->where('merek', $merek))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $jenisApars = JenisApar::orderBy('nama')->get();
        $mereks = Produk::query()
            ->select('merek')
            ->distinct()
            ->orderBy('merek')
            ->pluck('merek');

        return view('public.produk.index', compact('produks', 'jenisApars', 'mereks', 'filters'));
    }

    public function produkShow(Produk $produk)
    {
        $produk->load(['jenisApar', 'stokBatches']);

        return view('public.produk.show', compact('produk'));
    }

    public function riwayatApar()
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->isAdmin() || $user->isTeknisi()) {
            return redirect()->route('home');
        }

        $pelanggan = Pelanggan::where('user_id', $user->id)->first();

        if (!$pelanggan) {
            return redirect()->route('home')->with('error', 'Profil pelanggan tidak ditemukan.');
        }

        $this->syncUnitsForPelanggan($pelanggan);

        $pelanggan->load([
            'units.produk.jenisApar',
            'units.services',
            'pesanan.details.produk.jenisApar',
            'pesanan.servicePaket',
            'pesanan.serviceJenisRefill',
            'pesanan.teknisi',
            'pesanan.unitApars',
            'pesanan.complain',
            'testimonis',
            'complains.pesanan',
        ]);

        $totalTransaksi = $pelanggan->pesanan->count();
        $activeOrders = $pelanggan->pesanan
            ->filter(fn($p) => $p->isActiveOrder())
            ->count();

        $pendingPaymentOrder = $pelanggan->pesanan
            ->filter(fn($p) => $p->isActiveOrder() && !$p->isPaymentConfirmed())
            ->sortByDesc(fn($p) => $p->created_at)
            ->first();

        $feedbackDescriptions = $pelanggan->pesanan
            ->map(fn (Pesanan $pesanan) => $this->feedbackLinkDescription($pelanggan, $pesanan))
            ->all();

        $feedbackLinks = ActivityLog::query()
            ->where('log_name', 'feedback')
            ->where('event', 'linked_to_order')
            ->where('subject_type', Testimoni::class)
            ->whereIn('description', $feedbackDescriptions)
            ->get()
            ->keyBy('description');

        $linkedTestimoniIds = $feedbackLinks
            ->pluck('subject_id')
            ->filter()
            ->unique()
            ->values();

        $linkedTestimonis = Testimoni::query()
            ->whereIn('id', $linkedTestimoniIds)
            ->get()
            ->keyBy('id');

        $pelanggan->pesanan->each(function (Pesanan $pesanan) use ($feedbackLinks, $linkedTestimonis, $pelanggan) {
            $description = $this->feedbackLinkDescription($pelanggan, $pesanan);
            $feedbackLink = $feedbackLinks->get($description);
            $linkedTestimoni = $feedbackLink ? $linkedTestimonis->get($feedbackLink->subject_id) : null;

            $pesanan->setAttribute('linked_testimoni_id', $linkedTestimoni?->id);
            $pesanan->setRelation('linkedTestimoni', $linkedTestimoni);
        });

        $recentExpiries = $pelanggan->units
            ->filter(fn($u) => $u->tgl_expired && $u->tgl_expired->diffInDays(now()) <= 60)
            ->sortBy(fn($u) => $u->tgl_expired)
            ->take(3);

        return view('public.riwayat-apar.index', compact(
            'pelanggan',
            'totalTransaksi',
            'activeOrders',
            'pendingPaymentOrder',
            'recentExpiries'
        ));
    }

    public function riwayatAparStatus(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->isAdmin() || $user->isTeknisi()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pelanggan = Pelanggan::where('user_id', $user->id)->first();

        if (!$pelanggan) {
            return response()->json(['error' => 'Pelanggan not found'], 404);
        }

        $since = $request->query('since');

        $orders = Pesanan::where('pelanggan_id', $pelanggan->id)
            ->when($since, fn($q) => $q->where('updated_at', '>', $since))
            ->latest('updated_at')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'order_code' => $p->orderCode(),
                'status' => $p->publicStatusLabel(),
                'status_class' => $p->publicStatusClasses(),
                'tipe' => $p->tipe,
                'type_label' => $p->trackingTypeLabel(),
                'item_label' => $p->trackingItemLabel(),
                'timeline_step' => $p->getTimelineStep(),
                'total' => (float) ($p->total_harga ?: $p->total),
                'updated_at' => $p->updated_at->toIso8601String(),
            ]);

        $units = $pelanggan->units->map(fn($u) => [
            'id' => $u->id,
            'no_seri' => $u->no_seri,
            'nama' => $u->produk?->nama ?? 'APAR',
            'jenis' => $u->produk?->jenisApar?->nama ?? '-',
            'ukuran' => $u->produk?->kapasitas ?? $u->ukuran ?? '-',
            'tgl_expired' => $u->tgl_expired?->format('d M Y'),
            'is_expired' => $u->tgl_expired && $u->tgl_expired->isPast(),
            'is_expiring_soon' => $u->tgl_expired && !$u->tgl_expired->isPast() && $u->tgl_expired->diffInDays(now()) <= 30,
            'days_until_expiry' => $u->tgl_expired ? $u->tgl_expired->diffInDays(now()) : null,
        ]);

        return response()->json([
            'success' => true,
            'server_time' => now()->toIso8601String(),
            'orders' => $orders,
            'units' => $units,
        ]);
    }

    private function syncUnitsForPelanggan(Pelanggan $pelanggan): void
    {
        Pesanan::with(['details.produk.jenisApar', 'unitApars'])
            ->where('pelanggan_id', $pelanggan->id)
            ->where('tipe', 'produk')
            ->whereIn('status', ['diproses', 'selesai', 'selesai final'])
            ->get()
            ->each(fn (Pesanan $pesanan) => $this->syncUnitAparsForPesanan($pesanan));
    }

    private function syncUnitAparsForPesanan(Pesanan $pesanan): void
    {
        if ($pesanan->tipe !== 'produk' || !in_array($pesanan->status, ['diproses', 'selesai', 'selesai final'], true)) {
            return;
        }

        $pesanan->loadMissing(['details.produk.jenisApar', 'unitApars']);

        foreach ($pesanan->details as $detail) {
            $existingCount = $pesanan->unitApars
                ->where('produk_id', $detail->produk_id)
                ->count();

            $missingCount = max(0, ((int) $detail->jumlah) - $existingCount);

            if ($missingCount <= 0 || !$detail->produk) {
                continue;
            }

            $this->createUnitAparsFromDetail($pesanan, $detail->produk, $missingCount, $existingCount + 1);
            $pesanan->load('unitApars');
        }
    }

    private function createUnitAparsFromDetail(Pesanan $pesanan, Produk $produk, int $jumlah, int $startFrom = 1): void
    {
        for ($urutan = $startFrom; $urutan < $startFrom + $jumlah; $urutan++) {
            $serial = $this->generateSerialNumber($pesanan, $produk, $urutan);

            UnitApar::create([
                'pelanggan_id' => $pesanan->pelanggan_id,
                'pesanan_id' => $pesanan->id,
                'produk_id' => $produk->id,
                'no_seri' => $serial,
                'tgl_beli' => $pesanan->tanggal,
                'tgl_produksi' => $pesanan->tanggal,
                'ukuran' => $produk->kapasitas ?? '-',
                'bahan' => $produk->jenisApar?->nama ?? '-',
                'tgl_expired' => UnitApar::calculateExpiry($pesanan->tanggal, $produk->kapasitas ?? '-', $produk->jenisApar?->nama ?? '-'),
            ]);
        }
    }

    private function generateSerialNumber(Pesanan $pesanan, Produk $produk, int $urutan): string
    {
        $pesanan->loadMissing('pelanggan');
        return UnitApar::generateSerialNumber($pesanan->pelanggan, $pesanan->tanggal);
    }
}
