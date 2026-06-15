@php
    $actionButtonBase = 'inline-flex items-center justify-center px-3 py-2 rounded-xl border text-[10px] font-black uppercase tracking-widest transition shadow-sm';
    $actionButtonNeutral = $actionButtonBase . ' border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
    $actionButtonPrimary = $actionButtonBase . ' border-red-600 bg-red-600 text-white hover:bg-red-700 hover:border-red-700';
    $actionButtonProof = $actionButtonBase . ' min-w-[92px] border-transparent bg-blue-600 text-white hover:bg-blue-700';
    $actionButtonProofStyle = 'background-color:#2563eb;border-color:#2563eb;color:#fff;';
    $actionButtonDanger = $actionButtonBase . ' border-red-200 bg-white text-red-600 hover:bg-red-50';
@endphp

@forelse($pesananRiwayat as $pesanan)
    @php
        $pricingSummary = $pesanan->pricingSummary();
        $s = $pesanan->status;
        $isOffline = in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin'], true);
        $statusBadge = match(true) {
            $s === 'selesai final' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
            $s === 'selesai' => ['bg-emerald-50 text-emerald-700', 'SELESAI FINAL'],
            $s === 'selesai oleh teknisi' => ['bg-cyan-50 text-cyan-700', 'SELESAI OLEH TEKNISI'],
            $s === 'dikonfirmasi admin' => ['bg-cyan-50 text-cyan-700', 'DIKONFIRMASI'],
            $s === 'disetujui' => ['bg-emerald-50 text-emerald-700', 'SIAP BAYAR'],
            $s === 'ditolak' => ['bg-red-50 text-red-700', 'DITOLAK'],
            default => ['bg-gray-50 text-gray-700', strtoupper($s)],
        };
        $paymentBadge = $pesanan->isPaymentConfirmed()
            ? ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Pembayaran Lunas']
            : ['bg-amber-50 text-amber-700 border-amber-200', 'Belum Bayar'];
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
        <td class="px-8 py-5 whitespace-nowrap">
            <p class="text-xs font-bold text-gray-900">{{ $pesanan->displayTransactionDateTime() }}</p>
            <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $pesanan->transactionDisplayName() }}</p>
        </td>
        <td class="px-8 py-5">
            <p class="text-sm font-black text-gray-900">{{ $pesanan->pelanggan?->nama ?? '-' }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $pesanan->pelanggan?->no_wa ?? '-' }}</p>
        </td>
        <td class="px-8 py-5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex px-3 py-1 rounded-full {{ $isOffline ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600' }} text-[10px] font-black uppercase tracking-widest">{{ $isOffline ? 'Offline' : 'Online' }}</span>
            </div>
            <p class="mt-2 text-sm font-black text-gray-900 max-w-[220px] leading-6">{{ $firstProdukNama }}</p>
            <p class="mt-1 text-xs font-semibold text-gray-500">{{ $itemCount }} item - {{ $unitCount }} unit</p>
        </td>
        <td class="px-8 py-5 whitespace-nowrap">
            <span class="text-sm font-semibold text-gray-900">Rp {{ number_format((float) $pricingSummary['totalPembayaran'], 0, ',', '.') }}</span>
        </td>
        <td class="px-8 py-5">
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
        </td>
        <td class="px-8 py-5 text-right">
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
                <button type="button" onclick="openPesananDetailModal({{ $pesanan->id }})" class="{{ $actionButtonNeutral }}">
                    Detail
                </button>
                <a href="{{ route('invoice.show', $pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat Invoice">
                    Lihat Invoice
                </a>
                <form action="{{ route('admin.pesanan.destroy', $pesanan) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus riwayat pesanan ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="{{ $actionButtonDanger }}" title="Hapus">Hapus</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada riwayat pesanan yang selesai.</td>
    </tr>
@endforelse
