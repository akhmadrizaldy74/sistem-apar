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
                                @forelse($task->details as $detail)
                                    <div class="flex flex-col gap-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                        <span class="font-semibold text-slate-900">{{ $detail->produk?->nama ?? '-' }}</span>
                                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $detail->jumlah }} unit</span>
                                    </div>
                                @empty
                                    <p class="font-medium text-slate-500">Detail produk tidak tersedia.</p>
                                @endforelse
                            @else
                                <div class="space-y-2 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                    <p class="font-semibold text-slate-900">
                                        {{ $task->isRefillOrder() ? 'Refill APAR' : 'Service APAR' }}
                                    </p>
                                    <p>
                                        {{ $task->trackingItemLabel() }}
                                        @if($task->service_jenis_apar || $task->service_ukuran_apar)
                                            - {{ trim(collect([$task->service_jenis_apar, $task->service_ukuran_apar])->filter()->implode(' ')) }}
                                        @endif
                                    </p>
                                    @if($task->service_jumlah_unit)
                                        <p>{{ $task->service_jumlah_unit }} unit</p>
                                    @endif
                                    @if($task->service_metode_penanganan)
                                        <p>Metode: {{ ucwords((string) $task->service_metode_penanganan) }}</p>
                                    @endif
                                </div>
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

                <div class="w-full lg:max-w-sm" x-data="{ showDone: false }">
                    @if($task->status === \App\Models\Pesanan::STATUS_DITUGASKAN_KE_TEKNISI)
                        <form action="{{ route('teknisi.tugas.mulai', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black uppercase tracking-widest text-white transition hover:bg-blue-700">
                                Proses
                            </button>
                        </form>
                    @elseif($task->status === \App\Models\Pesanan::STATUS_DIKERJAKAN_TEKNISI)
                        <button type="button" @click="showDone = !showDone" class="w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                            Selesai
                        </button>

                        <div x-show="showDone" x-cloak class="mt-4 rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                            <form action="{{ route('teknisi.tugas.selesai', $task) }}" method="POST" class="space-y-3">
                                @csrf
                                <label for="catatan-{{ $task->id }}" class="block text-[10px] font-black uppercase tracking-widest text-emerald-800">
                                    Catatan Teknisi
                                </label>
                                <textarea id="catatan-{{ $task->id }}" name="catatan" rows="4" class="w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-400" placeholder="Tambahkan catatan teknisi jika ada."></textarea>
                                <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                                    Simpan Selesai
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-3xl border border-slate-200 bg-white p-12 text-center text-sm font-semibold text-slate-500">
            {{ $emptyMessage }}
        </div>
    @endforelse
</div>
