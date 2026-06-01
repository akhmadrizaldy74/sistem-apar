<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900">Manajemen Komplain</h2>
                <p class="text-sm font-medium text-gray-500">Daftar komplain dari pelanggan yang masuk melalui formulir publik.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-700">{{ $counts['menunggu'] }} Menunggu</span>
                <span class="rounded-xl bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">{{ $counts['diproses'] }} Diproses</span>
                <span class="rounded-xl bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">{{ $counts['selesai'] }} Selesai</span>
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
                <tbody class="divide-y divide-gray-50">
                    @forelse($complains as $complain)
                        @php
                            $relatedType = $complain->relatedTransactionType();
                            $relatedPesanan = $complain->relatedPesanan();
                            $relatedService = $complain->relatedService();
                            $relatedRefill = $complain->relatedRefill();

                            $statusClass = match($complain->status_penyelesaian) {
                                'menunggu' => 'bg-red-50 text-red-700',
                                'diproses' => 'bg-amber-50 text-amber-700',
                                'selesai' => 'bg-emerald-50 text-emerald-700',
                                default => 'bg-gray-50 text-gray-700',
                            };

                            $waMessage = 'Halo ' . $complain->pelanggan->nama . ", kami dari PD. Anugrah Utama menindaklanjuti komplain Anda";
                            if ($relatedType !== 'umum') {
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
                </tbody>
            </table>
        </div>
        @if($complains->hasPages())
            <div class="border-t border-gray-50 px-8 py-4">
                {{ $complains->links() }}
            </div>
        @endif
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
</x-app-layout>
