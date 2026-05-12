<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen Komplain</h2>
                <p class="text-sm text-gray-500 font-medium">Daftar komplain dari pelanggan yang masuk melalui formulir publik.</p>
            </div>
            <div class="flex gap-3 items-center flex-wrap">
                <span class="px-4 py-2 bg-red-50 text-red-700 font-bold text-sm rounded-xl">{{ $counts['menunggu'] }} Menunggu</span>
                <span class="px-4 py-2 bg-amber-50 text-amber-700 font-bold text-sm rounded-xl">{{ $counts['diproses'] }} Diproses</span>
                <span class="px-4 py-2 bg-emerald-50 text-emerald-700 font-bold text-sm rounded-xl">{{ $counts['selesai'] }} Selesai</span>
            </div>
        </div>
    </x-slot>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <select name="status" onchange="this.form.submit()" class="text-sm rounded-xl border-gray-200 px-4 py-2.5 focus:border-red-400">
            <option value="">Semua Status</option>
            <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari komplain..." class="text-sm rounded-xl border-gray-200 px-4 py-2.5 focus:border-red-400 w-64" />
        @if(request('status') || request('search'))
            <a href="{{ route('admin.complain.index') }}" class="px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pesanan / Service</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Isi Komplain</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($complains as $complain)
                        <tr class="hover:bg-gray-50/30 transition">
                            <td class="px-8 py-5 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $complain->tanggal->format('d M Y') }}</td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-gray-900">{{ $complain->pelanggan->nama }}</p>
                                <p class="text-xs font-medium text-gray-500">{{ $complain->pelanggan->no_wa }}</p>
                                @if($complain->pelanggan->no_wa)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $complain->pelanggan->no_wa) }}?text={{ urlencode('Halo '.$complain->pelanggan->nama.', kami dari PD. Anugrah Utama ingin merespons komplain Anda.') }}"
                                   target="_blank" class="inline-block mt-1 px-3 py-1 bg-green-50 text-green-700 text-[10px] font-bold rounded-lg hover:bg-green-100 transition">
                                    <i class="fa-brands fa-whatsapp me-1"></i>Chat WA
                                </a>
                                @endif
                            </td>
                            <td class="px-8 py-5">
                                @if($complain->pesanan)
                                    <a href="{{ route('admin.pesanan.show', $complain->pesanan) }}" class="text-xs font-bold text-blue-600 hover:underline">
                                        #{{ $complain->pesanan->id }} — Pesanan
                                    </a>
                                @elseif($complain->service)
                                    <a href="{{ route('admin.service.show', $complain->service) }}" class="text-xs font-bold text-violet-600 hover:underline">
                                        #{{ $complain->service->id }} — Service
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm text-gray-600 max-w-xs line-clamp-2">{{ $complain->isi_complain }}</p>
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $statusClass = match($complain->status_penyelesaian) {
                                        'menunggu' => 'bg-red-50 text-red-700',
                                        'diproses' => 'bg-amber-50 text-amber-700',
                                        'selesai'  => 'bg-emerald-50 text-emerald-700',
                                        default    => 'bg-gray-50 text-gray-700',
                                    };
                                @endphp
                                <span class="px-3 py-1 {{ $statusClass }} text-xs font-bold uppercase rounded-lg">
                                    {{ $complain->status_penyelesaian }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <select name="status_penyelesaian" onchange="updateStatus(this, {{ $complain->id }})" class="text-xs rounded-xl border-gray-200 py-2 focus:border-red-400">
                                        <option value="menunggu" {{ $complain->status_penyelesaian == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="diproses" {{ $complain->status_penyelesaian == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                        <option value="selesai"  {{ $complain->status_penyelesaian == 'selesai'  ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    <form action="{{ route('admin.complain.destroy', $complain) }}" method="POST" onsubmit="return confirm('Hapus komplain ini?')">
                                        @csrf @method('DELETE')
                                        <button class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition"><i class="fa-solid fa-trash text-xs"></i></button>
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
        <div class="px-8 py-4 border-t border-gray-50">
            {{ $complains->links() }}
        </div>
        @endif
    </div>

    <script>
        function updateStatus(select, id) {
            const status = select.value;
            fetch(`/admin/complain/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status_penyelesaian: status }),
            }).then(() => {
                select.closest('td').previousElementSibling.querySelector('span').textContent = status;
                // Update badge color
                const badge = select.closest('td').previousElementSibling.querySelector('span');
                if (status === 'menunggu') badge.className = 'px-3 py-1 bg-red-50 text-red-700 text-xs font-bold uppercase rounded-lg';
                if (status === 'diproses') badge.className = 'px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold uppercase rounded-lg';
                if (status === 'selesai') badge.className = 'px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold uppercase rounded-lg';
            });
        }
    </script>
</x-app-layout>