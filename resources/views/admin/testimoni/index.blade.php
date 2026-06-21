<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen Testimoni</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola testimoni pelanggan yang masuk dari akun pelanggan. Hanya testimoni yang disetujui yang tampil di landing page.</p>
            </div>
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

    {{-- Modal Balas --}}
    <div id="editModal" class="fixed inset-0 bg-gray-900/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl">
            <h3 class="text-xl font-black text-gray-900 mb-6">Balas Testimoni</h3>
            <form id="editForm" action="" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Balasan Admin</label>
                    <textarea name="admin_note" id="editAdminNote" rows="4" class="w-full rounded-xl border-gray-200" placeholder="Ketikkan balasan untuk pelanggan..."></textarea>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" onclick="closeModals()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm">Batal</button>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm">Simpan Balasan</button>
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

        function openReply(id, adminNote) {
            document.getElementById('editForm').action = '/admin/testimoni/' + id;
            document.getElementById('editAdminNote').value = adminNote;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModals() {
            const editModal = document.getElementById('editModal');
            const photoModal = document.getElementById('photoModal');

            if (editModal) {
                editModal.style.display = 'none';
            }
            if (photoModal) {
                photoModal.classList.add('hidden');
                photoModal.classList.remove('flex');
            }
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
