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
    <div id="testimoni-counts" class="mb-6">
        @include('admin.testimoni.partials.counts', ['counts' => $counts, 'currentStatus' => (string) request('status', '')])
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
                <tbody id="testimoni-rows" class="divide-y divide-gray-50">
                    @include('admin.testimoni.partials.rows', ['testimonis' => $testimonis])
                </tbody>
            </table>
        </div>
        <div id="testimoni-pagination" class="px-8 py-4 border-t border-gray-50 {{ $testimonis->hasPages() ? '' : 'hidden' }}">
            {!! $testimonis->hasPages() ? $testimonis->links()->render() : '' !!}
        </div>
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

    {{-- Modal Foto --}}
    <div id="photoModal" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[100] hidden items-center justify-center p-4" onclick="if(event.target===this)closePhotoModal()">
        <div class="relative max-h-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <h3 class="font-bold text-gray-900">Foto Testimoni</h3>
                <button type="button" onclick="closePhotoModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-4 bg-gray-50 flex justify-center">
                <img id="modalPhotoImage" src="" alt="Foto" class="max-h-[70vh] w-auto rounded-lg object-contain" />
            </div>
        </div>
    </div>

    <script>
        function openPhotoModal(src) {
            document.getElementById('modalPhotoImage').src = src;
            document.getElementById('photoModal').classList.remove('hidden');
            document.getElementById('photoModal').classList.add('flex');
        }
        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
            document.getElementById('photoModal').classList.remove('flex');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePhotoModal();
        });

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
            document.getElementById('photoModal').classList.add('hidden');
            document.getElementById('photoModal').classList.remove('flex');
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('bg-gray-900\/50')) closeModals();
        });
    </script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const snapshotUrl = new URL(@js(route('admin.realtime.testimoni')), window.location.origin);

                if (window.location.search) {
                    snapshotUrl.search = window.location.search;
                }

                window.createPollingUpdater({
                    url: snapshotUrl.toString(),
                    interval: 10000,
                    onSuccess(payload) {
                        const counts = document.getElementById('testimoni-counts');
                        const rows = document.getElementById('testimoni-rows');
                        const pagination = document.getElementById('testimoni-pagination');

                        if (counts && typeof payload.counts_html === 'string') {
                            counts.innerHTML = payload.counts_html;
                        }
                        if (rows && typeof payload.rows_html === 'string') {
                            rows.innerHTML = payload.rows_html;
                        }
                        if (pagination) {
                            pagination.innerHTML = payload.pagination_html || '';
                            pagination.classList.toggle('hidden', !(payload.pagination_html || '').trim());
                        }
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
