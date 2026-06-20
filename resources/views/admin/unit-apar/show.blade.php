<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start gap-4">
            <a
                href="{{ route('admin.unit-apar.index') }}"
                class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:text-red-700"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>

            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900">Detail Unit APAR</h2>
                <p class="mt-2 text-sm font-medium text-slate-500">Informasi unit APAR ditampilkan untuk pemantauan tanpa proses edit.</p>
            </div>
        </div>
    </x-slot>

    @php
        $customerWaUrl = $unit->pelanggan?->no_wa
            ? \App\Support\WhatsApp::customerLink(
                $unit->pelanggan->no_wa,
                'Halo Bapak/Ibu, kami ingin menginformasikan status APAR Anda dengan nomor unit ' . ($unit->no_seri ?: '-') . '.'
            )
            : null;
        $refillHistories = $refillHistories ?? collect();
        $serviceHistories = $serviceHistories ?? collect();
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="rounded-3xl border px-6 py-5 shadow-sm {{ $statusMeta['notice_class'] }}">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-widest">Status Masa Berlaku</p>
                    <p class="mt-2 text-lg font-black">{{ $statusMeta['label'] }}</p>
                    <p class="mt-1 text-sm font-semibold">{{ $statusMeta['notice_text'] }}</p>
                </div>

                <span class="inline-flex rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest {{ $statusMeta['badge_class'] }}">
                    {{ $statusMeta['label'] }}
                </span>
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                <h3 class="text-lg font-black text-slate-900">Informasi Unit APAR</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Data di bawah ini bersifat baca saja untuk kebutuhan monitoring.</p>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 px-6 py-6 sm:grid-cols-2 sm:px-8 lg:grid-cols-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nomor Unit</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ $unit->no_seri ?: '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Pelanggan</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ $unit->pelanggan?->nama ?: '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">WhatsApp Pelanggan</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ $unit->pelanggan?->no_wa ?: '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Produk APAR</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ $unit->produk?->nama ?: '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis APAR</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ $unit->produk?->jenisApar?->nama ?: ($unit->bahan ?: '-') }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ukuran</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ $unit->ukuran ?: ($unit->produk?->kapasitas ?: '-') }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal Beli</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ optional($unit->tgl_beli ?? $unit->tgl_produksi)->format('d M Y') ?: '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal Produksi / Dasar Expired</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ optional($unit->tgl_produksi)->format('d M Y') ?: '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal Expired</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ optional($unit->tgl_expired)->format('d M Y') ?: '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Status Masa Berlaku</p>
                    <span class="mt-2 inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $statusMeta['badge_class'] }}">
                        {{ $statusMeta['label'] }}
                    </span>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Kondisi Awal</p>
                    <p class="mt-2 text-sm font-bold capitalize text-slate-900">{{ str_replace('_', ' ', $unit->kondisi_awal ?: '-') }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Lokasi Unit</p>
                    <p class="mt-2 text-sm font-bold text-slate-900">{{ $unit->lokasi_unit ?: '-' }}</p>
                </div>

                <div class="sm:col-span-2 lg:col-span-1">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Keterangan</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ $unit->catatan_unit ?: '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                    <h3 class="text-lg font-black text-slate-900">Riwayat Refill</h3>
                    <p class="mt-1 text-sm font-medium text-slate-500">Riwayat refill terakhir untuk unit ini.</p>
                </div>

                <div class="space-y-4 px-6 py-6 sm:px-8">
                    @forelse ($refillHistories as $service)
                        @php
                            $refill = $service->refill;
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ $refill?->jenisRefill?->nama_label ?: 'Refill APAR' }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">{{ optional($service->tgl_service)->format('d M Y') ?: '-' }}</p>
                                </div>
                                <p class="text-sm font-black text-slate-700">Rp {{ number_format((float) ($service->biaya ?? 0), 0, ',', '.') }}</p>
                            </div>

                            <p class="mt-3 text-sm font-medium text-slate-600">
                                {{ trim((string) ($service->keterangan ?: $service->catatan_teknisi ?: 'Tidak ada catatan tambahan.')) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-slate-500">Belum ada riwayat refill untuk unit ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                    <h3 class="text-lg font-black text-slate-900">Riwayat Service</h3>
                    <p class="mt-1 text-sm font-medium text-slate-500">Riwayat service dan perawatan unit ini.</p>
                </div>

                <div class="space-y-4 px-6 py-6 sm:px-8">
                    @forelse ($serviceHistories as $service)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ $service->jenis_service ?: ($service->servicePaket?->nama ?: 'Service APAR') }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">{{ optional($service->tgl_service)->format('d M Y') ?: '-' }}</p>
                                </div>
                                <p class="text-sm font-black text-slate-700">Rp {{ number_format((float) ($service->biaya ?? 0), 0, ',', '.') }}</p>
                            </div>

                            <p class="mt-3 text-sm font-medium text-slate-600">
                                {{ trim((string) ($service->keterangan ?: $service->catatan_teknisi ?: 'Tidak ada catatan tambahan.')) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-slate-500">Belum ada riwayat service untuk unit ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            @if($customerWaUrl)
                <a
                    href="{{ $customerWaUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center rounded-2xl bg-green-500 px-6 py-3 text-xs font-black uppercase tracking-widest text-white transition hover:bg-green-600"
                >
                    Hubungi Pelanggan
                </a>
            @endif
            <a
                href="{{ route('admin.unit-apar.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-xs font-black uppercase tracking-widest text-white transition hover:bg-slate-800"
            >
                Kembali ke Daftar Unit
            </a>
        </div>
    </div>
</x-app-layout>
