<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\UnitApar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $since = $request->query('since', now()->subHours(24)->toIso8601String());

        // Pesanan baru (bukan status 'selesai final' atau 'ditolak')
        $newOrders = Pesanan::with('pelanggan')
            ->whereNotIn('status', ['selesai final', 'ditolak'])
            ->where('updated_at', '>=', Carbon::parse($since))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'kode'       => 'Pesanan #' . $p->id,
                'pelanggan'  => $p->pelanggan?->nama ?? '-',
                'status'     => $p->status,
                'total'      => $p->total_harga ?: $p->total,
                'tipe'       => $p->tipe,
                'created_at' => $p->created_at->toIso8601String(),
            ]);

        // APAR expiring dalam 30 hari
        $expiringApar = UnitApar::with('pelanggan')
            ->whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('tgl_expired', 'asc')
            ->limit(10)
            ->get()
            ->map(fn($u) => [
                'id'          => $u->id,
                'no_seri'     => $u->no_seri,
                'pelanggan'   => $u->pelanggan?->nama ?? '-',
                'tgl_expired' => $u->tgl_expired->toDateString(),
                'days_left'   => now()->diffInDays($u->tgl_expired, false),
            ]);

        // APAR sudah expired
        $expiredApar = UnitApar::with('pelanggan')
            ->whereDate('tgl_expired', '<', now()->toDateString())
            ->orderBy('tgl_expired', 'asc')
            ->limit(10)
            ->get()
            ->map(fn($u) => [
                'id'          => $u->id,
                'no_seri'    => $u->no_seri,
                'pelanggan'  => $u->pelanggan?->nama ?? '-',
                'tgl_expired' => $u->tgl_expired->toDateString(),
                'days_over'   => now()->diffInDays($u->tgl_expired),
            ]);

        $stats = [
            'new_orders_today' => Pesanan::whereNotIn('status', ['selesai final', 'ditolak'])
                ->whereDate('created_at', now()->toDateString())
                ->count(),
            'expiring_soon' => UnitApar::whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
            'already_expired' => UnitApar::whereDate('tgl_expired', '<', now()->toDateString())->count(),
        ];

        return response()->json([
            'success'      => true,
            'server_time'  => now()->toIso8601String(),
            'orders'       => $newOrders,
            'expiring_apar' => $expiringApar,
            'expired_apar'  => $expiredApar,
            'stats'        => $stats,
        ]);
    }
}
