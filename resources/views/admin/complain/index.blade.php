<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900">Manajemen Komplain</h2>
                <p class="text-sm font-medium text-gray-500">Daftar komplain dari pelanggan yang masuk melalui akun pelanggan.</p>
            </div>
            <div id="complain-counts">
                @include('admin.complain.partials.counts', ['counts' => $counts])
            </div>
        </div>
    </x-slot>

    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <select name="status" onchange="this.form.submit()" class="rounded-xl border-gray-200 px-4 py-2.5 text-sm focus:border-red-400">
            <option value="">Semua Status</option>
            <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari komplain..." class="w-64 rounded-xl border-gray-200 px-4 py-2.5 text-sm focus:border-red-400" />
        @if(request('status') || request('search'))
            <a href="{{ route('admin.complain.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Waktu Komplain</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Pelanggan</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Transaksi Terkait</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Isi Komplain</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody id="complain-rows" class="divide-y divide-gray-50">
                    @include('admin.complain.partials.rows', ['complains' => $complains])
                </tbody>
            </table>
        </div>
        <div id="complain-pagination" class="border-t border-gray-50 px-8 py-4 {{ $complains->hasPages() ? '' : 'hidden' }}">
            {!! $complains->hasPages() ? $complains->links()->render() : '' !!}
        </div>
    </div>

    <script>
        async function parseErrorResponse(response) {
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                const data = await response.json();
                return data.message || data.error || 'Gagal memperbarui status komplain.';
            }

            const text = await response.text();
            return text || 'Gagal memperbarui status komplain.';
        }

        async function persistStatus(url, status) {
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status_penyelesaian: status }),
            });

            if (!response.ok) {
                throw new Error(await parseErrorResponse(response));
            }

            return response.json();
        }

        function paintStatus(select, status) {
            select.value = status;
            select.dataset.currentStatus = status;
            const badge = select.closest('td').previousElementSibling.querySelector('span');
            badge.textContent = status;
            if (status === 'menunggu') badge.className = 'rounded-lg px-3 py-1 text-xs font-bold uppercase bg-red-50 text-red-700';
            if (status === 'diproses') badge.className = 'rounded-lg px-3 py-1 text-xs font-bold uppercase bg-amber-50 text-amber-700';
            if (status === 'selesai') badge.className = 'rounded-lg px-3 py-1 text-xs font-bold uppercase bg-emerald-50 text-emerald-700';
        }

        async function updateStatus(select) {
            const url = select.dataset.updateUrl;
            const status = select.value;
            const previousStatus = select.dataset.currentStatus || status;
            select.disabled = true;

            try {
                await persistStatus(url, status);
                paintStatus(select, status);
            } catch (error) {
                select.value = previousStatus;
                showAppAlert(error.message, 'error', 'Gagal');
            } finally {
                select.disabled = false;
            }
        }

        async function processAndChat(button, phone, message) {
            const url = button.dataset.updateUrl;
            const row = button.closest('td');
            const select = row.querySelector('select');
            const previousStatus = select.dataset.currentStatus || select.value;
            button.disabled = true;
            select.disabled = true;

            try {
                await persistStatus(url, 'diproses');
                paintStatus(select, 'diproses');
                window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
            } catch (error) {
                select.value = previousStatus;
                showAppAlert(error.message, 'error', 'Gagal');
            } finally {
                button.disabled = false;
                select.disabled = false;
            }
        }
    </script>


    <!-- Photo Modal -->
    <div id="photoModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" onclick="if(event.target===this)closePhotoModal()">
        <div class="relative max-h-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <h3 class="font-bold text-gray-900">Foto Komplain</h3>
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
    </script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const snapshotUrl = new URL(@js(route('admin.realtime.complain')), window.location.origin);

                if (window.location.search) {
                    snapshotUrl.search = window.location.search;
                }

                window.createPollingUpdater({
                    url: snapshotUrl.toString(),
                    interval: 10000,
                    onSuccess(payload) {
                        const counts = document.getElementById('complain-counts');
                        const rows = document.getElementById('complain-rows');
                        const pagination = document.getElementById('complain-pagination');

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
