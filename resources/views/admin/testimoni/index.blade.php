<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen Testimoni</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola ulasan pelanggan yang tampil di Landing Page. Hanya testimoni berstatus <strong>Approved</strong> yang tampil di publik.</p>
            </div>
            <button onclick="document.getElementById('addModal').style.display='flex'" class="px-6 py-3 bg-red-700 text-white rounded-2xl text-sm font-black hover:bg-red-800 transition shadow-xl shadow-red-700/25 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Tambah Testimoni
            </button>
        </div>
    </x-slot>

    {{-- Status Counts --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('admin.testimoni.index') }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ !request('status') ? 'ring-2 ring-red-500' : '' }}">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ $counts['total'] }}</p>
        </a>
        <a href="{{ route('admin.testimoni.index', ['status' => 'pending']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ request('status') == 'pending' ? 'ring-2 ring-amber-500' : '' }}">
            <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Menunggu</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ $counts['pending'] }}</p>
        </a>
        <a href="{{ route('admin.testimoni.index', ['status' => 'approved']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ request('status') == 'approved' ? 'ring-2 ring-emerald-500' : '' }}">
            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Disetujui</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ $counts['approved'] }}</p>
        </a>
        <a href="{{ route('admin.testimoni.index', ['status' => 'rejected']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ request('status') == 'rejected' ? 'ring-2 ring-red-500' : '' }}">
            <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Ditolak</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ $counts['rejected'] }}</p>
        </a>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Rating</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Review</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($testimonis as $t)
                        <tr class="hover:bg-gray-50/30 transition">
                            <td class="px-8 py-5 text-sm font-black text-gray-900">{{ $t->pelanggan->nama ?? '-' }}</td>
                            <td class="px-8 py-5">
                                <x-rating-stars :rating="$t->rating" sizeClass="text-base" activeClass="text-amber-400" inactiveClass="text-slate-300" emptyClass="text-xs font-semibold text-gray-400" />
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm text-gray-600 max-w-xs line-clamp-2">{{ $t->review }}</p>
                                @if($t->admin_note)
                                    <p class="text-[10px] text-blue-600 mt-1 italic">
                                        {{ $t->status === 'rejected' ? 'Catatan Admin' : 'Balasan Admin' }}: {{ $t->admin_note }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $statusClass = match($t->status) {
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'approved' => 'bg-emerald-50 text-emerald-700',
                                        'rejected' => 'bg-red-50 text-red-700',
                                        default => 'bg-gray-50 text-gray-700',
                                    };
                                @endphp
                                <span class="px-3 py-1 {{ $statusClass }} text-xs font-bold uppercase rounded-lg">
                                    {{ $t->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-xs font-bold text-gray-500 whitespace-nowrap">{{ $t->displaySubmittedDateTime() }}</td>
                            <td class="px-8 py-5">
                                <div class="flex gap-1.5 flex-wrap">
                                    @if($t->status !== 'approved')
                                        <form action="{{ route('admin.testimoni.approve', $t) }}" method="POST">
                                            @csrf
                                            <button class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-700 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition shadow-sm border border-emerald-200">
                                                Setujui
                                            </button>
                                        </form>
                                    @endif
                                    @if($t->status !== 'rejected')
                                        <button onclick="openReject({{ $t->id }})" class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition shadow-sm border border-red-200">
                                            Tolak
                                        </button>
                                    @endif
                                    @if($t->status !== 'pending')
                                        <form action="{{ route('admin.testimoni.pending', $t) }}" method="POST">
                                            @csrf
                                            <button class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-amber-600 bg-amber-50 rounded-xl hover:bg-amber-100 transition shadow-sm border border-amber-200">
                                                Menunggu
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="openEdit({{ $t->id }}, {{ $t->rating }}, '{{ addslashes($t->review) }}', '{{ addslashes($t->admin_note ?? '') }}')"
                                        class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition shadow-sm border border-blue-200">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.testimoni.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus testimoni ini?')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition shadow-sm border border-red-200">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-8 py-16 text-center text-sm text-gray-400">Belum ada testimoni.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($testimonis->hasPages())
        <div class="px-8 py-4 border-t border-gray-50">
            {{ $testimonis->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <div id="addModal" class="fixed inset-0 bg-gray-900/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl">
            <h3 class="text-xl font-black text-gray-900 mb-6">Tambah Testimoni</h3>
            <form action="{{ route('admin.testimoni.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pelanggan</label>
                    <select name="pelanggan_id" required class="w-full rounded-xl border-gray-200">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($pelanggans as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->no_wa }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Rating (1-5 Bintang)</label>
                    <div class="flex gap-1" id="addRatingStars">
                        @for($i=1;$i<=5;$i++)
                            <button type="button" onclick="setRating({{ $i }}, 'add')" id="add-star-{{ $i }}" class="text-2xl text-gray-200 hover:text-amber-400 transition">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        @endfor
                        <input type="hidden" name="rating" id="addRatingInput" value="5" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Review</label>
                    <textarea name="review" required rows="3" class="w-full rounded-xl border-gray-200"></textarea>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" onclick="closeModals()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm">Batal</button>
                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="editModal" class="fixed inset-0 bg-gray-900/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl">
            <h3 class="text-xl font-black text-gray-900 mb-6">Edit Testimoni</h3>
            <form id="editForm" action="" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Rating</label>
                    <div class="flex gap-1" id="editRatingStars">
                        @for($i=1;$i<=5;$i++)
                            <button type="button" onclick="setRating({{ $i }}, 'edit')" id="edit-star-{{ $i }}" class="text-2xl text-gray-200 hover:text-amber-400 transition">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        @endfor
                        <input type="hidden" name="rating" id="editRatingInput" value="5" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Review</label>
                    <textarea name="review" id="editReview" required rows="3" class="w-full rounded-xl border-gray-200"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Balasan Admin</label>
                    <textarea name="admin_note" id="editAdminNote" rows="3" class="w-full rounded-xl border-gray-200" placeholder="Balasan singkat untuk pelanggan..."></textarea>
                    <p class="mt-2 text-xs text-gray-500">Balasan ini juga bisa dipakai sebagai catatan saat review ditolak.</p>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" onclick="closeModals()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm">Batal</button>
                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tolak --}}
    <div id="rejectModal" class="fixed inset-0 bg-gray-900/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl">
            <h3 class="text-xl font-black text-gray-900 mb-2">Tolak Testimoni</h3>
            <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin menolak testimoni ini? Tambahkan catatan opsional.</p>
            <form id="rejectForm" action="" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Catatan (opsional)</label>
                    <textarea name="admin_note" rows="3" class="w-full rounded-xl border-gray-200" placeholder="Alasan penolakan..."></textarea>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" onclick="closeModals()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm">Batal</button>
                    <button type="submit" class="px-6 py-3 bg-red-700 text-white rounded-xl font-bold text-sm">Tolak Testimoni</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setRating(value, prefix) {
            for (let i = 1; i <= 5; i++) {
                const el = document.getElementById(prefix + '-star-' + i);
                if (i <= value) {
                    el.className = 'text-2xl text-amber-400 hover:text-amber-300 transition';
                } else {
                    el.className = 'text-2xl text-gray-200 hover:text-amber-400 transition';
                }
            }
            document.getElementById(prefix + 'RatingInput').value = value;
        }

        function openEdit(id, rating, review, adminNote) {
            document.getElementById('editForm').action = '/admin/testimoni/' + id;
            document.getElementById('editReview').value = review;
            document.getElementById('editAdminNote').value = adminNote;
            document.getElementById('editRatingInput').value = rating;
            setRating(rating, 'edit');
            document.getElementById('editModal').style.display = 'flex';
        }

        function openReject(id) {
            document.getElementById('rejectForm').action = '/admin/testimoni/' + id + '/reject';
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeModals() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('rejectModal').style.display = 'none';
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('bg-gray-900\/50')) closeModals();
        });
    </script>
</x-app-layout>
