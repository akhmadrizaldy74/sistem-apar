@forelse($testimonis as $t)
    <tr class="hover:bg-gray-50/30 transition">
        <td class="px-8 py-5 text-sm font-black text-gray-900">
            {{ $t->pelanggan->nama ?? '-' }}
            @if($t->is_anonymous)
                <span class="block mt-1 text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md w-fit">Sembunyikan Nama</span>
            @endif
        </td>
        <td class="px-8 py-5">
            <x-rating-stars :rating="$t->rating" sizeClass="text-base" activeClass="text-amber-400" inactiveClass="text-slate-300" emptyClass="text-xs font-semibold text-gray-400" />
        </td>
        <td class="px-8 py-5">
            <p class="text-sm text-gray-600 max-w-xs line-clamp-2">{{ $t->review }}</p>
            @if(!empty($t->foto_path))
                <div class="mt-2">
                    <button type="button" onclick="openPhotoModal('{{ asset('storage/' . $t->foto_path) }}')" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-bold text-gray-600 shadow-sm transition hover:bg-gray-50">
                        <i class="fa-regular fa-image text-gray-400"></i>
                        Lihat Foto
                    </button>
                </div>
            @else
                <p class="mt-2 text-[10px] italic text-gray-400">Tidak ada foto</p>
            @endif
            @if($t->admin_note)
                <p class="text-[10px] text-blue-600 mt-1 italic">
                    {{ $t->status === 'rejected' ? 'Catatan Admin' : 'Balasan Admin' }}: {{ $t->admin_note }}
                </p>
            @endif
        </td>
        <td class="px-8 py-5 text-xs font-bold text-gray-500 whitespace-nowrap">{{ $t->displaySubmittedDateTime() }}</td>
        <td class="px-8 py-5">
            <div class="flex gap-1.5 flex-wrap">
                <button onclick="openReply({{ $t->id }}, '{{ addslashes($t->admin_note ?? '') }}')" class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition shadow-sm border border-blue-200">
                    Balas
                </button>
                <form action="{{ route('admin.testimoni.destroy', $t) }}" method="POST" data-confirm="Hapus testimoni ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition shadow-sm border border-red-200">
                        Hapus
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-8 py-16 text-center text-sm text-gray-400">Belum ada testimoni.</td>
    </tr>
@endforelse
