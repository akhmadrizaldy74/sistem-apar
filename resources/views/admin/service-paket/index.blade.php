<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900">Service &amp; Peralatan</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Kelola jenis service dan peralatan service yang digunakan dalam layanan APAR.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between rounded-3xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data Layanan</p>
                <p class="mt-2 text-sm font-semibold text-slate-500">Harga service bersifat standar per jenis service. Peralatan yang tampil di halaman ini dipakai sebagai acuan pengurangan stok saat service selesai final.</p>
            </div>
            <span class="inline-flex rounded-full bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-700">
                {{ $servicePakets->count() }} Jenis Aktif
            </span>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis Service</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Harga Standar</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Peralatan Digunakan</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Transaksi</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($servicePakets as $servicePaket)
                            <tr class="align-top hover:bg-slate-50/70 transition">
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-900">{{ $servicePaket->nama }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $servicePaket->label ?: 'Tanpa label tambahan' }}</p>
                                    @if($servicePaket->jenisRefill)
                                        <p class="mt-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-amber-700">{{ $servicePaket->jenisRefill->nama }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-900">Rp {{ number_format((float) $servicePaket->harga, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">Per unit APAR</p>
                                </td>
                                <td class="px-6 py-5">
                                    @if($servicePaket->peralatans->isEmpty())
                                        <p class="text-xs font-semibold text-slate-400">Belum ada peralatan terhubung.</p>
                                    @else
                                        <div class="space-y-2">
                                            @foreach($servicePaket->peralatans as $peralatan)
                                                <div class="rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
                                                    <span class="font-black text-slate-900">{{ $peralatan->nama }}</span>
                                                    <span class="text-slate-500">• {{ (int) ($peralatan->pivot->jumlah_estimasi ?? 0) }} unit</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700">Aktif</span>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-900">{{ $servicePaket->services_count }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">service terhubung</p>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.service-paket.edit', $servicePaket) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600 transition hover:bg-slate-100">Edit</a>
                                        <form action="{{ route('admin.service-paket.destroy', $servicePaket) }}" method="POST" data-confirm="Hapus paket service ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-red-200 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-600 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">Belum ada jenis service final.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
