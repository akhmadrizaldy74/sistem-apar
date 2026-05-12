<?php

namespace App\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TugasTeknisiDiperbarui implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;
    public int $teknisiId;

    public function __construct(Pesanan $pesanan)
    {
        $pesanan->loadMissing(['pelanggan']);

        $this->teknisiId = (int) ($pesanan->teknisi_id ?? 0);

        $this->payload = [
            'id'            => $pesanan->id,
            'status'        => $pesanan->status,
            'tipe'          => $pesanan->tipe,
            'pelanggan'     => $pesanan->pelanggan?->nama ?? '-',
            'teknisi_id'    => $this->teknisiId,
            'updated_at'    => now()->toIso8601String(),
        ];
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('admin-notifications'),
        ];

        // Jika ada teknisi yang di-assign, kirim ke channel private teknisi tsb
        if ($this->teknisiId > 0) {
            $channels[] = new Channel("teknisi-{$this->teknisiId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'tugas.diperbarui';
    }
}
