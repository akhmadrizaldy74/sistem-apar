@forelse($complains as $complain)
    @php
        $statusClass = match($complain->status_penyelesaian) {
            'menunggu' => 'bg-red-50 text-red-700',
            'diproses' => 'bg-amber-50 text-amber-700',
            'selesai' => 'bg-emerald-50 text-emerald-700',
            default => 'bg-gray-50 text-gray-700',
        };

        $waMessage = 'Halo ' . $complain->pelanggan->nama . ", kami dari PD. Anugrah Utama menindaklanjuti komplain Anda";
        if ($complain->relatedTransactionType() !== 'umum') {
            $waMessage .= ' untuk ' . $complain->relatedTransactionLabel() . ' pada ' . $complain->relatedTransactionDateTime();
        }
        $waMessage .= ".\n\nRingkasan keluhan:\n" . $complain->isi_complain . "\n\nSilakan balas chat ini agar tim kami bisa bantu sampai selesai.";
    @endphp
    <tr class="transition hover:bg-gray-50/30">
        <td class="whitespace-nowrap px-8 py-5 text-xs font-bold text-gray-600">{{ $complain->displaySubmittedDateTime() }}</td>
        <td class="px-8 py-5">
            <p class="text-sm font-black text-gray-900">{{ $complain->pelanggan->nama }}</p>
            <p class="text-xs font-medium text-gray-500">{{ $complain->pelanggan->no_wa }}</p>
            <p class="mt-1 text-[10px] font-semibold text-gray-400">Follow up utama dilakukan lewat WhatsApp.</p>
        </td>
        <td class="px-8 py-5">
            <div class="block">
                <p class="text-sm font-black text-gray-700">{{ $complain->relatedTransactionLabel() }}</p>
                <p class="mt-1 text-xs font-semibold text-gray-500">{{ $complain->relatedTransactionDateTime() }}</p>
            </div>
        </td>
        <td class="px-8 py-5">
            <p class="max-w-xs text-sm text-gray-600 line-clamp-2">{{ $complain->isi_complain }}</p>
            @if(!empty($complain->foto_path))
                <div class="mt-2">
                    <button type="button" onclick="openPhotoModal('{{ asset('storage/' . $complain->foto_path) }}')" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-bold text-gray-600 shadow-sm transition hover:bg-gray-50">
                        <i class="fa-regular fa-image text-gray-400"></i>
                        Lihat Foto
                    </button>
                </div>
            @else
                <p class="mt-2 text-[10px] italic text-gray-400">Tidak ada foto</p>
            @endif
        </td>
        <td class="px-8 py-5">
            <span class="rounded-lg px-3 py-1 text-xs font-bold uppercase {{ $statusClass }}">{{ $complain->status_penyelesaian }}</span>
        </td>
        <td class="px-8 py-5">
            <div class="flex flex-wrap items-center gap-2">
                <select
                    name="status_penyelesaian"
                    data-current-status="{{ $complain->status_penyelesaian }}"
                    data-update-url="{{ route('admin.complain.update', $complain) }}"
                    onchange="updateStatus(this)"
                    class="rounded-xl border-gray-200 py-2 text-xs focus:border-red-400"
                >
                    <option value="menunggu" {{ $complain->status_penyelesaian == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="diproses" {{ $complain->status_penyelesaian == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai" {{ $complain->status_penyelesaian == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                @if($complain->pelanggan->no_wa)
                    <button
                        type="button"
                        data-update-url="{{ route('admin.complain.update', $complain) }}"
                        onclick="processAndChat(this, '{{ preg_replace('/^0/', '62', $complain->pelanggan->no_wa) }}', `{{ str_replace('`', '\`', $waMessage) }}`)"
                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-700 shadow-sm transition hover:bg-emerald-100"
                    >
                        Proses & Chat
                    </button>
                @endif
                <form action="{{ route('admin.complain.destroy', $complain) }}" method="POST" data-confirm="Hapus komplain ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-600 shadow-sm transition hover:bg-red-100">
                        Hapus
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-8 py-16 text-center text-sm font-medium text-gray-400">Belum ada komplain.</td>
    </tr>
@endforelse
