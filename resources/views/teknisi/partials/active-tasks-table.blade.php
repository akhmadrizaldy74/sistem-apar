@props([
    'tasks',
    'emptyMessage' => 'Belum ada pekerjaan aktif.',
    'emptyDescription' => 'Pekerjaan dari admin akan muncul di halaman ini.',
])

<div class="space-y-3">
    @forelse($tasks as $task)
        @php
            $isProduct = $task->isProductOrder();
            $jobCategory = $isProduct
                ? 'Pesanan Produk'
                : ($task->isRefillOrder() ? 'Refill APAR' : 'Service APAR');
            $statusLabel = $task->technicianStatusLabel();
            $customerName = $task->pelanggan?->nama ?? $task->nama_penerima ?? '-';
            $customerPhone = $task->pelanggan?->no_wa ?? $task->nomor_wa_penerima ?? '-';
            $customerAddress = trim((string) ($task->pelanggan?->alamat ?? $task->alamat_pengiriman ?? '')) ?: '-';
            $serviceUnitDisplay = $task->serviceUnitDisplay();
            $serviceType = $task->isRefillOrder()
                ? ($task->serviceJenisRefill?->nama_label ?? $task->trackingItemLabel())
                : ($task->servicePaket?->nama ?? $task->trackingItemLabel());
        @endphp

        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/40 sm:p-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-slate-700">
                    {{ $jobCategory }}
                </span>
                <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] {{ $task->publicStatusClasses() }}">
                    {{ $statusLabel }}
                </span>
                <span class="text-xs font-semibold text-slate-500 sm:ml-auto">
                    {{ $task->technicianTaskDateTime('d M Y, H:i') }}
                </span>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div class="space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Pelanggan</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $customerName }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">WhatsApp</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">{{ $customerPhone }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Alamat</p>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-700">{{ $customerAddress }}</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Detail Pekerjaan</p>

                        @if($isProduct)
                            <div class="mt-2 space-y-2 text-sm text-slate-700">
                                @forelse($task->details as $detail)
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:gap-3">
                                        <span class="font-black text-slate-500 sm:w-32 sm:shrink-0">
                                            {{ $loop->first ? 'Produk' : 'Produk Lain' }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900">{{ $detail->produk?->nama ?? '-' }}</p>
                                            <p class="text-slate-600">Jumlah: {{ $detail->jumlah }} unit</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col gap-1 sm:flex-row sm:gap-3">
                                        <span class="font-black text-slate-500 sm:w-32 sm:shrink-0">Produk</span>
                                        <span class="text-slate-600">Detail produk tidak tersedia.</span>
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <div class="mt-2 space-y-2 text-sm text-slate-700">
                                <div class="flex flex-col gap-1 sm:flex-row sm:gap-3">
                                    <span class="font-black text-slate-500 sm:w-32 sm:shrink-0">Unit APAR</span>
                                    <div class="min-w-0 space-y-1">
                                        <p class="font-semibold text-slate-900">{{ $serviceUnitDisplay['summary'] ?? '-' }}</p>
                                        @if(($serviceUnitDisplay['is_registered'] ?? false))
                                            @foreach(array_slice($serviceUnitDisplay['entries'] ?? [], 0, 2) as $entry)
                                                @if(!empty($entry['code']))
                                                    <p class="text-xs text-slate-500">{{ $entry['code'] }}</p>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if(count($serviceUnitDisplay['entries'] ?? []) > 1)
                                            <p class="text-xs text-slate-500">+{{ count($serviceUnitDisplay['entries']) - 1 }} unit lainnya</p>
                                        @endif
                                        @if((int) ($serviceUnitDisplay['quantity'] ?? 0) > 1)
                                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $serviceUnitDisplay['quantity_label'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1 sm:flex-row sm:gap-3">
                                    <span class="font-black text-slate-500 sm:w-32 sm:shrink-0">
                                        {{ $task->isRefillOrder() ? 'Jenis Refill' : 'Jenis Service' }}
                                    </span>
                                    <span>{{ $serviceType ?: '-' }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 lg:pl-4">
                    @if($task->status === \App\Models\Pesanan::STATUS_DITUGASKAN_KE_TEKNISI)
                        <form action="{{ route('teknisi.tugas.mulai', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex min-w-[116px] justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black uppercase tracking-widest text-white transition hover:bg-blue-700">
                                Kerjakan
                            </button>
                        </form>
                    @elseif($task->status === \App\Models\Pesanan::STATUS_DIKERJAKAN_TEKNISI)
                        <form action="{{ route('teknisi.tugas.selesai', $task) }}" method="POST" data-confirm="Tandai pekerjaan ini sebagai selesai?" data-confirm-title="Konfirmasi Selesai" data-confirm-button="Ya, Selesaikan">
                            @csrf
                            <button type="submit" class="inline-flex min-w-[116px] justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                                Selesai
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white px-6 py-10 text-center shadow-sm shadow-slate-200/30 sm:px-8 sm:py-12">
            <p class="text-lg font-black text-slate-900">{{ $emptyMessage }}</p>
            <p class="mx-auto mt-2 max-w-md text-sm font-medium leading-6 text-slate-500">{{ $emptyDescription }}</p>
        </div>
    @endforelse
</div>
