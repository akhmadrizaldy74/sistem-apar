<?php

namespace App\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusPesananDiperbarui implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;
    public int $teknisiId;

    public function __construct(Pesanan $pesanan)
    {
        $pesanan->loadMissing(['pelanggan']);
        $this->teknisiId = (int) ($pesanan->teknisi_id ?? 0);

        $this->payload = [
            'id'           => $pesanan->id,
            'status'       => $pesanan->status,
            'status_label' => $pesanan->publicStatusLabel(),
            'pelanggan'    => $pesanan->pelanggan?->nama ?? '-',
            'pelanggan_id' => (int) ($pesanan->pelanggan_id ?? 0),
            'total'        => (float) ($pesanan->total_harga ?? $pesanan->total ?? 0),
            'tipe'         => $pesanan->tipe,
            'teknisi_id'   => $this->teknisiId,
            'updated_at'   => now()->toIso8601String(),
        ];
    }

    public function broadcastOn(): array
    {
        $pelangganId = $this->payload['pelanggan_id'] ?? 0;

        $channels = [
            new Channel('admin-notifications'),
        ];

        if ($this->teknisiId > 0) {
            $channels[] = new Channel("teknisi-{$this->teknisiId}");
        }

        if ($pelangganId > 0) {
            $channels[] = new Channel("pelanggan-{$pelangganId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'pesanan.status-diperbarui';
    }
}
