<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900">Pelanggan</h2>
                <p class="text-sm font-medium text-gray-500">Daftar pelanggan yang berasal dari akun dengan role pelanggan.</p>
            </div>
            <p class="max-w-3xl text-xs font-semibold leading-relaxed text-slate-500">
                Data pelanggan berasal dari akun dengan role pelanggan. Untuk menambahkan pelanggan baru, buat akun pelanggan melalui
                <a href="{{ route('admin.akun.index') }}" class="font-black text-red-700 hover:underline">Manajemen Akun</a>.
            </p>
        </div>
    </x-slot>

    <div class="space-y-8">
        <div id="pelanggan-summary-cards">
            @include('admin.pelanggan.partials.summary-cards', ['summary' => $summary])
        </div>

        <section class="overflow-hidden rounded-[2.5rem] border border-white/60 bg-white/80 shadow-xl shadow-slate-200/50 backdrop-blur-md">
            <form method="GET" class="flex flex-col gap-3 border-b border-gray-100/70 bg-slate-50/60 p-6 sm:flex-row">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, WhatsApp, atau alamat pelanggan..." class="w-full rounded-2xl border border-gray-200 bg-white py-3.5 pl-11 pr-5 text-sm font-medium shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white transition hover:bg-black">
                        Cari
                    </button>
                    @if($search !== '')
                        <a href="{{ route('admin.pelanggan.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-red-100 bg-white px-6 py-3.5 text-xs font-black uppercase tracking-widest text-red-600 transition hover:bg-red-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left">
                    <thead class="border-b border-gray-100/70 bg-slate-50/80">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Email</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">WhatsApp / HP</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Transaksi</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pelanggan-desktop-rows" class="divide-y divide-gray-100/70">
                        @include('admin.pelanggan.partials.desktop-rows', ['pelanggans' => $pelanggans])
                    </tbody>
                </table>
            </div>

            <div id="pelanggan-mobile-rows" class="divide-y divide-gray-100/80 lg:hidden">
                @include('admin.pelanggan.partials.mobile-rows', ['pelanggans' => $pelanggans])
            </div>

            <div id="pelanggan-pagination" class="border-t border-gray-100/70 px-6 py-4 {{ $pelanggans->hasPages() ? '' : 'hidden' }}">
                {!! $pelanggans->hasPages() ? $pelanggans->links()->render() : '' !!}
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const snapshotUrl = new URL(@js(route('admin.realtime.pelanggan')), window.location.origin);

                if (window.location.search) {
                    snapshotUrl.search = window.location.search;
                }

                window.createPollingUpdater({
                    url: snapshotUrl.toString(),
                    interval: 10000,
                    onSuccess(payload) {
                        const summary = document.getElementById('pelanggan-summary-cards');
                        const desktopRows = document.getElementById('pelanggan-desktop-rows');
                        const mobileRows = document.getElementById('pelanggan-mobile-rows');
                        const pagination = document.getElementById('pelanggan-pagination');

                        if (summary && typeof payload.summary_html === 'string') {
                            summary.innerHTML = payload.summary_html;
                        }
                        if (desktopRows && typeof payload.desktop_rows_html === 'string') {
                            desktopRows.innerHTML = payload.desktop_rows_html;
                        }
                        if (mobileRows && typeof payload.mobile_rows_html === 'string') {
                            mobileRows.innerHTML = payload.mobile_rows_html;
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
