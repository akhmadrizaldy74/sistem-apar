@props(['tasks', 'emptyMessage' => 'Belum ada pekerjaan yang diberikan oleh admin.'])

<div class="space-y-4">
    @forelse($tasks as $task)
        @php
            $isProduct = $task->isProductOrder();
            $jobCategory = $isProduct ? 'Pesanan Produk' : 'Service / Refill';
            $statusLabel = $task->publicStatusLabel();
        @endphp

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-4 flex-1">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Tanggal Transaksi</p>
                            <h3 class="mt-1 text-lg font-black text-slate-900">{{ $task->displayTransactionDateTime('d M Y, H:i') }}</h3>
                        </div>

                        <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $task->publicStatusClasses() }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Pelanggan</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $task->pelanggan?->nama ?? $task->nama_penerima ?? '-' }}</p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis Pekerjaan</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $jobCategory }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat Pelanggan</p>
                        <p class="mt-1 text-sm font-medium leading-6 text-slate-700">{{ $task->pelanggan?->alamat ?? $task->alamat_pengiriman ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Detail Produk / Layanan</p>
                        <div class="mt-2 space-y-2 text-sm text-slate-700">
                            @if($isProduct)
                                <p class="font-semibold text-slate-900">{{ $task->details->pluck('produk.nama')->filter()->implode(', ') ?: '-' }}</p>
                            @else
                                <p class="font-semibold text-slate-900">{{ $task->isRefillOrder() ? 'Refill APAR' : 'Service APAR' }}</p>
                                <p>{{ $task->trackingItemLabel() }}</p>
                                @if($task->service_jumlah_unit)
                                    <p>{{ $task->service_jumlah_unit }} unit</p>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Status Pekerjaan</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $statusLabel }}</p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Catatan Teknisi</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ $task->teknisi_catatan ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-3xl border border-slate-200 bg-white p-12 text-center text-sm font-semibold text-slate-500">
            {{ $emptyMessage }}
        </div>
    @endforelse
</div>
