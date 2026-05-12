<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Master Peralatan</h2>
                <p class="text-sm font-medium text-gray-500">Kelola daftar peralatan/perlengkapan yang bisa dipilih saat transaksi pembelian di menu Pengeluaran.</p>
            </div>
            <button
                type="button"
                onclick="openCreateModal()"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-700 px-6 py-3 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/20 transition hover:bg-red-800"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                Tambah Master
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-blue-100 bg-blue-50/70 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">Catatan Alur</p>
            <p class="mt-2 text-sm font-semibold leading-relaxed text-blue-900">Stok tidak diubah dari halaman ini. Untuk menambah stok peralatan/perlengkapan, gunakan menu <a href="{{ route('admin.pengeluaran.index') }}" class="font-black underline decoration-blue-300 underline-offset-4">Pengeluaran</a> agar transaksi pembelian dan mutasi stok tercatat otomatis.</p>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/70">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Nama Item</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Stok Saat Ini</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Min. Stok</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Standar</th>
                            <th class="px-8 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($peralatans as $item)
                            <tr class="transition hover:bg-gray-50/40">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900">{{ $item->nama }}</p>
                                </td>
                                <td class="px-8 py-5 text-sm font-black text-gray-900">{{ number_format((int) $item->stok, 0, ',', '.') }} unit</td>
                                <td class="px-8 py-5 text-sm font-bold text-gray-600">{{ number_format((int) $item->stok_minimum, 0, ',', '.') }} unit</td>
                                <td class="px-8 py-5 text-sm font-black text-gray-900">Rp {{ number_format((float) $item->harga_standar, 0, ',', '.') }}</td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            onclick='openEditModal(@json(["id" => $item->id, "nama" => $item->nama, "harga_standar" => $item->harga_standar, "stok_minimum" => $item->stok_minimum]))'
                                            class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-600 transition hover:bg-gray-100"
                                        >
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.peralatan.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus master peralatan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-600 transition hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada master peralatan/perlengkapan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="masterPeralatanModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-gray-950/55 p-4 backdrop-blur-sm">
        <div class="w-full max-w-xl overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl shadow-gray-900/20">
            <div class="flex items-center justify-between border-b border-gray-100 px-8 py-6">
                <div>
                    <h3 id="masterPeralatanTitle" class="text-xl font-black text-gray-900">Tambah Master Peralatan</h3>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Lengkapi data referensi item. Stok akan bertambah dari transaksi pembelian.</p>
                </div>
                <button type="button" onclick="closePeralatanModal()" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 transition hover:text-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form id="masterPeralatanForm" action="{{ route('admin.peralatan.store') }}" method="POST" class="space-y-5 p-8">
                @csrf
                <input id="masterPeralatanMethod" type="hidden" name="_method" value="POST">

                <div>
                    <label for="master_nama" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Nama Peralatan / Perlengkapan</label>
                    <input id="master_nama" type="text" name="nama" required class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="Contoh: Safety Pin APAR">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="master_harga" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Standar</label>
                        <input id="master_harga" type="number" min="0" name="harga_standar" required class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20" placeholder="0">
                    </div>
                    <div>
                        <label for="master_stok_minimum" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-gray-400">Min. Stok</label>
                        <input id="master_stok_minimum" type="number" min="0" name="stok_minimum" value="3" class="w-full rounded-xl border-none bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 transition focus:ring-2 focus:ring-red-600/20">
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-700">Info</p>
                    <p class="mt-2 text-sm font-semibold text-amber-900">Setelah master dibuat, item ini akan muncul di dropdown pembelian peralatan pada menu Pengeluaran.</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closePeralatanModal()" class="px-5 py-3 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900">Batal</button>
                    <button type="submit" class="rounded-xl bg-red-700 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const masterModal = document.getElementById('masterPeralatanModal');
        const masterForm = document.getElementById('masterPeralatanForm');
        const masterTitle = document.getElementById('masterPeralatanTitle');
        const masterMethod = document.getElementById('masterPeralatanMethod');

        function openCreateModal() {
            masterTitle.textContent = 'Tambah Master Peralatan';
            masterForm.action = @js(route('admin.peralatan.store'));
            masterMethod.value = 'POST';
            document.getElementById('master_nama').value = '';
            document.getElementById('master_harga').value = '';
            document.getElementById('master_stok_minimum').value = 3;
            masterModal.style.display = 'flex';
        }

        function openEditModal(item) {
            masterTitle.textContent = 'Edit Master Peralatan';
            masterForm.action = `/admin/peralatan/${item.id}`;
            masterMethod.value = 'PUT';
            document.getElementById('master_nama').value = item.nama ?? '';
            document.getElementById('master_harga').value = item.harga_standar ?? 0;
            document.getElementById('master_stok_minimum').value = item.stok_minimum ?? 3;
            masterModal.style.display = 'flex';
        }

        function closePeralatanModal() {
            masterModal.style.display = 'none';
        }
    </script>
</x-app-layout>
