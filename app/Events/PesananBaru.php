<?php

namespace App\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesananBaru implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(Pesanan $pesanan)
    {
        $pesanan->loadMissing(['pelanggan', 'details']);

        $this->payload = [
            'id'          => $pesanan->id,
            'tipe'        => $pesanan->tipe,
            'status'      => $pesanan->status,
            'sumber'      => $pesanan->sumber_pesanan,
            'pelanggan'   => [
                'nama'  => $pesanan->pelanggan?->nama ?? '-',
                'no_wa' => $pesanan->pelanggan?->no_wa ?? '-',
            ],
            'total'       => (float) ($pesanan->total ?? 0),
            'detail_count'=> $pesanan->details->count(),
            'tanggal'     => $pesanan->tanggal?->format('d M Y') ?? now()->format('d M Y'),
            'created_at'  => $pesanan->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    /**
     * Channel publik untuk admin (tidak perlu auth).
     * Channel admin-notifications: semua admin yang sedang buka halaman ini akan terima event.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pesanan.baru';
    }
}
