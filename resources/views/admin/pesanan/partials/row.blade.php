@php
    use App\Models\Pesanan;

    $actionButtonBase = 'inline-flex w-full items-center justify-center rounded-xl border px-3 py-2.5 text-[10px] font-black uppercase tracking-[0.14em] transition shadow-sm pointer-events-auto relative z-10';
    $actionButtonNeutral = $actionButtonBase . ' border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
    $actionButtonPrimary = $actionButtonBase . ' border-red-600 bg-red-600 text-white hover:border-red-700 hover:bg-red-700';
    $actionButtonProof = $actionButtonBase . ' border-blue-600 bg-blue-600 text-white hover:border-blue-700 hover:bg-blue-700';
    $actionButtonDanger = $actionButtonBase . ' border-red-200 bg-white text-red-600 hover:bg-red-50';
    $proofUrl = !empty($pesanan->bukti_pembayaran)
        ? '/storage/' . ltrim(str_replace('storage/', '', (string) $pesanan->bukti_pembayaran), '/')
        : null;
    $canAssign = $pesanan->isPaymentConfirmed()
        && !$pesanan->teknisi_id
        && in_array((string) $pesanan->status, [
            Pesanan::STATUS_DIPROSES,
            Pesanan::STATUS_DISETUJUI,
            'menunggu diproses admin',
            Pesanan::STATUS_MENUNGGU_PENGAMBILAN,
            Pesanan::STATUS_MENUNGGU_KEDATANGAN_UNIT,
        ], true);
    $canFinalize = in_array((string) $pesanan->status, [
        Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
        Pesanan::STATUS_DIKONFIRMASI_ADMIN,
    ], true);
    $hideConfirmMessage = $pesanan->isLockedForAdminDeletion()
        ? 'Data ini sudah masuk laporan. Hapus hanya akan menyembunyikan transaksi dari menu Pesanan, bukan menghapus dari laporan. Lanjutkan?'
        : 'Yakin ingin menyembunyikan transaksi ini dari menu Pesanan? Data transaksi tetap tersimpan di database dan laporan.';
    $pricingSummary = $pesanan->pricingSummary();
@endphp

<tr class="transition-colors hover:bg-gray-50/50">
    <td class="px-7 py-6 align-top whitespace-nowrap">
        <p class="text-[15px] font-bold leading-6 text-gray-900">{{ $pesanan->displayTransactionDateTime() }}</p>
    </td>
    <td class="px-7 py-6 align-top">
        <p class="text-[15px] font-black leading-6 text-gray-900">{{ $pesanan->pelanggan?->nama ?? '-' }}</p>
        <p class="mt-1 text-[13px] font-semibold leading-5 text-gray-500 break-all">{{ $pesanan->pelanggan?->no_wa ?? '-' }}</p>
    </td>
    <td class="px-7 py-6 align-top whitespace-nowrap">
        <span class="inline-flex rounded-full px-3 py-1.5 text-[11px] font-black leading-none {{ $pesanan->adminOrderTypeBadgeClasses() }}">
            {{ $pesanan->adminOrderTypeLabel() }}
        </span>
    </td>
    <td class="px-7 py-6 align-top">
        <p class="max-w-[320px] break-words text-[15px] font-black leading-6 text-gray-900">{{ $pesanan->adminOrderDetailTitle() }}</p>
        <p class="mt-1 text-[13px] font-semibold leading-5 text-gray-500">{{ $pesanan->adminOrderDetailMeta() }}</p>
    </td>
    <td class="px-7 py-6 align-top whitespace-nowrap">
        <span class="text-[15px] font-black leading-6 text-gray-900">Rp {{ number_format((float) ($pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.') }}</span>
    </td>
    <td class="px-7 py-6 align-top">
        <span class="inline-flex rounded-full px-3 py-1.5 text-[11px] font-black leading-none {{ $pesanan->adminStatusBadgeClasses() }}">
            {{ $pesanan->adminStatusLabel() }}
        </span>
    </td>
    <td class="w-[228px] min-w-[228px] px-7 py-6 align-top text-right overflow-visible">
        <div class="ml-auto grid max-w-[196px] grid-cols-2 gap-2 overflow-visible">
            @if($proofUrl)
                <button
                    type="button"
                    onclick="openPesananProofModal(@js($proofUrl), @js([
                        'customer' => $pesanan->pelanggan?->nama ?? '-',
                        'date' => $pesanan->displayTransactionDateTime(),
                        'type' => $pesanan->adminOrderTypeLabel(),
                    ]))"
                    class="{{ $actionButtonProof }}"
                >
                    Bukti TF
                </button>
            @endif

            @if($canFinalize)
                <form action="{{ route('admin.pesanan.selesai-final', $pesanan) }}" method="POST" class="w-full" data-confirm="Selesaikan final pesanan ini?" data-confirm-title="Konfirmasi Final" data-confirm-button="Ya, Finalkan">
                    @csrf
                    <button type="submit" class="{{ $actionButtonPrimary }}">Final</button>
                </form>
            @elseif($canAssign)
                <form action="{{ route('admin.pesanan.assign-teknisi', $pesanan) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="{{ $actionButtonPrimary }}">Assign</button>
                </form>
            @endif

            <button type="button" onclick="openPesananDetailModal({{ $pesanan->id }})" class="{{ $actionButtonNeutral }}">
                Detail
            </button>

            <a href="{{ route('invoice.show', $pesanan) }}" class="{{ $actionButtonPrimary }}" title="Lihat invoice">
                Invoice
            </a>

            <form action="{{ route('admin.pesanan.hide', ['jenis' => $pesanan->adminDestroyTypeSlug(), 'pesanan' => $pesanan]) }}" method="POST" class="w-full" data-confirm="{{ $hideConfirmMessage }}" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Sembunyikan">
                @csrf
                @method('PATCH')
                <button type="submit" class="{{ $actionButtonDanger }}" title="Sembunyikan transaksi ini dari menu Pesanan.">
                    Hapus
                </button>
            </form>
        </div>
    </td>
</tr>
