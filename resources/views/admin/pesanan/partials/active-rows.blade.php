@php
    $actionButtonBase = 'inline-flex items-center justify-center px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition shadow-sm';
    $actionButtonNeutral = $actionButtonBase . ' border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
    $actionButtonPrimary = $actionButtonBase . ' border-red-600 bg-red-600 text-white hover:bg-red-700 hover:border-red-700';
    $actionButtonProof = $actionButtonBase . ' min-w-[92px] border-transparent bg-blue-600 text-white hover:bg-blue-700';
    $actionButtonProofStyle = 'background-color:#2563eb;border-color:#2563eb;color:#fff;';
    $actionButtonDanger = $actionButtonBase . ' border-red-200 bg-white text-red-600 hover:bg-red-50';
    $actionButtonDisabled = $actionButtonBase . ' border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed';
@endphp

@forelse($pesananAktif as $pesanan)
    @php
        $pricingSummary = $pesanan->pricingSummary();
        $s = $pesanan->status;
        $isOffline = in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin'], true);
        $hasProof = !empty($pesanan->bukti_pembayaran);
        $canAssign = $pesanan->isPaymentConfirmed() && !$pesanan->teknisi_id;

        $statusBadge = match(true) {
            $s === 'selesai final' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
            $s === 'dikonfirmasi admin' => ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI'],
            $s === 'selesai oleh teknisi' => ['bg-cyan-50 text-cyan-700', 'SELESAI OLEH TEKNISI'],
            $s === 'dikerjakan teknisi' => ['bg-indigo-50 text-indigo-700', 'DIPROSES'],
            $s === 'ditugaskan ke teknisi' => ['bg-purple-50 text-purple-700', 'DITUGASKAN'],
            $s === 'menunggu persetujuan' => ['bg-amber-50 text-amber-700', 'MENUNGGU HARGA'],
            $s === 'disetujui' => ['bg-emerald-50 text-emerald-700', 'SIAP BAYAR'],
            $s === 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
            $s === 'ditolak' => ['bg-red-50 text-red-700', 'DITOLAK'],
            $s === 'diproses' && $hasProof => ['bg-emerald-50 text-emerald-700', 'DIPROSES'],
            $s === 'diproses' => ['bg-red-50 text-blue-700', 'DIPROSES'],
            $s === 'pending' && $hasProof => ['bg-emerald-50 text-emerald-700', 'DIPROSES'],
            $s === 'pending' => ['bg-amber-50 text-amber-700', 'MENUNGGU'],
            default => ['bg-gray-50 text-gray-700', strtoupper($s)],
        };

        $paymentBadge = match (true) {
            $pesanan->isPaymentConfirmed() => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Pembayaran Lunas'],
            $hasProof => ['bg-blue-50 text-blue-700 border-blue-200', 'Menunggu Verifikasi Pembayaran'],
            default => ['bg-amber-50 text-amber-700 border-amber-200', 'Belum Bayar'],
        };
        $hidePaymentBadge = $pesanan->shouldHidePaymentStatusBadge();

        $firstProduk = $pesanan->details->first();
        $firstProdukNama = $firstProduk?->produk?->nama ?? 'Pesanan Produk';
        if ($pesanan->details->count() > 1) {
            $firstProdukNama .= ' +' . ($pesanan->details->count() - 1) . ' lainnya';
        }
        $itemCount = $pesanan->details->count();
        $unitCount = $pesanan->details->sum('jumlah');
    @endphp
    <tr class="hover:bg-gray-50/40 transition-colors">
        <td class="px-8 py-6 whitespace-nowrap">
            <p class="text-xs font-bold text-gray-900">{{ $pesanan->displayTransactionDateTime() }}</p>
            <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $pesanan->transactionDisplayName() }}</p>
        </td>
        <td class="px-8 py-6">
            <p class="text-sm font-black text-gray-900">{{ $pesanan->pelanggan?->nama ?? '-' }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $pesanan->pelanggan?->no_wa ?? '-' }}</p>
        </td>
        <td class="px-8 py-6">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex px-3 py-1 rounded-full {{ $isOffline ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600' }} text-[10px] font-black uppercase tracking-widest">{{ $isOffline ? 'Offline' : 'Online' }}</span>
            </div>
            <p class="mt-2 text-sm font-black text-gray-900 max-w-[220px] leading-6">{{ $firstProdukNama }}</p>
            <p class="mt-1 text-xs font-semibold text-gray-500">{{ $itemCount }} item - {{ $unitCount }} unit</p>
        </td>
        <td class="px-8 py-6 whitespace-nowrap">
            <span class="text-sm font-semibold text-gray-900">Rp {{ number_format((float) $pricingSummary['totalPembayaran'], 0, ',', '.') }}</span>
        </td>
        <td class="px-8 py-6">
            <span class="inline-flex px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusBadge[0] }}">
                {{ $statusBadge[1] }}
            </span>
            @unless($hidePaymentBadge)
                <p class="mt-2">
                    <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-black {{ $paymentBadge[0] }}">
                        {{ $paymentBadge[1] }}
                    </span>
                </p>
            @endunless
            @if($pesanan->hasPendingPurchasePriceRequest())
                <p class="mt-2">
                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-black text-amber-700">
                        Menunggu Persetujuan Harga
                    </span>
                </p>
            @endif
        </td>
        <td class="px-8 py-6 text-right">
            <div class="flex flex-wrap items-center justify-end gap-2">
                @if(!$isOffline)
                    <button
                        type="button"
                        onclick="openPesananProofModal(@js(!empty($pesanan->bukti_pembayaran) ? '/storage/' . ltrim($pesanan->bukti_pembayaran, '/') : null), @js([
                            'customer' => $pesanan->pelanggan?->nama ?? '-',
                            'date' => $pesanan->displayTransactionDateTime(),
                            'type' => 'Pesanan',
                        ]))"
                        class="{{ $actionButtonProof }}"
                        style="{{ $actionButtonProofStyle }}"
                    >
                        Bukti TF
                    </button>
                @endif
                @if(in_array((string) $pesanan->status, ['selesai oleh teknisi', 'dikonfirmasi admin'], true))
                    <form action="{{ route('admin.pesanan.selesai-final', $pesanan) }}" method="POST" class="inline" data-confirm="Selesaikan final pesanan ini dan kurangi stok?" data-confirm-title="Konfirmasi Final" data-confirm-button="Ya, Finalkan">
                        @csrf
                        <button type="submit" class="{{ $actionButtonPrimary }}">Final</button>
                    </form>
                @elseif($canAssign)
                    <form action="{{ route('admin.pesanan.assign-teknisi', $pesanan) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="{{ $actionButtonPrimary }}">Assign</button>
                    </form>
                @endif
                <button type="button" onclick="openPesananDetailModal({{ $pesanan->id }})" class="{{ $actionButtonNeutral }}">
                    Detail
                </button>
                <a href="{{ route('invoice.show', $pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                    Lihat Invoice
                </a>
                @if($pesanan->status !== 'selesai' && $pesanan->status !== 'selesai final')
                    <form action="{{ route('admin.pesanan.destroy', $pesanan) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus pesanan ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="{{ $actionButtonDanger }}">Hapus</button>
                    </form>
                @else
                    <button type="button" disabled class="{{ $actionButtonDisabled }}" title="Hapus">Hapus</button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada data pesanan aktif dari pelanggan.</td>
    </tr>
@endforelse
