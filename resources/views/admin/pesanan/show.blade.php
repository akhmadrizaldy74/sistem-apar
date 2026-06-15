<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ $pesanan->invoiceTitle() }}</h2>
                <p class="text-sm text-gray-500 font-medium">Detail transaksi pelanggan dengan format tanggal dan jam yang lebih mudah dibaca.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.pesanan.index') }}" class="px-6 py-3 bg-white border border-gray-100 rounded-2xl text-sm font-black text-gray-700 hover:shadow-lg transition">
                    Kembali
                </a>
                <a href="{{ route('admin.pesanan.invoice.pdf', $pesanan) }}" class="px-6 py-3 bg-red-700 text-white rounded-2xl text-sm font-black hover:bg-red-800 transition shadow-xl shadow-red-700/25">
                    Cetak Invoice
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $pricingSummary = $pesanan->pricingSummary();
        $purchasePriceLabel = $pesanan->purchasePriceStatusLabel();
    @endphp

    <div class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 md:p-10">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Dokumen Transaksi</p>
                        <h3 class="mt-3 text-3xl font-black text-gray-900">{{ $pesanan->invoiceTitle() }}</h3>
                        <p class="mt-3 text-sm font-medium text-gray-500">Tanggal Transaksi: {{ $pesanan->displayTransactionDateTime() }}</p>
                        <span class="inline-block mt-3 px-3 py-1 text-xs font-bold uppercase rounded-full
                            @if($pesanan->hasPendingPurchasePriceRequest()) bg-amber-100 text-amber-700
                            @elseif(in_array($pesanan->status, ['selesai final', 'selesai', 'selesai oleh teknisi', 'dikonfirmasi admin'])) bg-emerald-100 text-emerald-700
                            @elseif($pesanan->status == 'ditolak') bg-red-100 text-red-700
                            @elseif($pesanan->status == 'diproses') bg-sky-100 text-sky-700
                            @elseif(in_array($pesanan->status, ['ditugaskan ke teknisi', 'dikerjakan teknisi'])) bg-purple-100 text-purple-700
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $pesanan->publicStatusLabel() }}
                        </span>
                        @if($purchasePriceLabel)
                            <span class="inline-block mt-3 ml-2 px-3 py-1 text-xs font-bold rounded-full {{ $pesanan->purchasePriceStatusClasses() }}">
                                {{ $purchasePriceLabel }}
                            </span>
                        @endif
                        <p class="mt-4 text-[10px] font-medium text-gray-300">Nomor referensi internal: {{ $pesanan->invoiceDisplayNumber() }}</p>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</p>
                        <p class="text-xl font-black text-gray-900 mt-3">{{ $pesanan->pelanggan->nama }}</p>
                        <p class="text-sm font-semibold text-gray-500 mt-2">{{ $pesanan->pelanggan->no_wa }}</p>
                        <p class="text-sm font-medium text-gray-500 mt-2">{{ $pesanan->pelanggan->alamat }}</p>
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $pesanan->pelanggan->no_wa) }}" target="_blank" class="inline-block mt-3 px-4 py-2 bg-green-500 text-white rounded-xl text-xs font-bold hover:bg-green-600 transition">
                            <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                        </a>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-2xl border border-gray-100 bg-gray-50">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Bukti Pembayaran</p>
                    @if($pesanan->bukti_pembayaran)
                        <a href="{{ asset('storage/' . ltrim($pesanan->bukti_pembayaran, '/')) }}" target="_blank" class="block">
                            <img src="{{ asset('storage/' . ltrim($pesanan->bukti_pembayaran, '/')) }}" alt="Bukti pembayaran {{ $pesanan->transactionDisplayName() }}" class="w-full max-h-72 object-contain rounded-xl border border-gray-200 bg-white">
                        </a>
                        <p class="text-xs text-emerald-700 font-bold mt-2">Klik gambar untuk buka ukuran penuh.</p>
                    @else
                        <p class="text-sm text-gray-500 font-semibold">Belum ada bukti transfer dari pelanggan.</p>
                    @endif
                </div>

                @if($pesanan->tipe === 'produk')
                <div class="mt-10 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jumlah</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Harga</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan->details as $detail)
                                <tr class="border-b border-gray-100">
                                    <td class="px-6 py-6 border-b border-gray-50">
                                        <p class="text-sm font-black text-gray-900">{{ $detail->produk->nama ?? 'Produk Terhapus' }}</p>
                                        <p class="text-xs font-semibold text-gray-500 mt-1">{{ optional($detail->produk)->jenisApar->nama ?? '' }} - {{ $detail->kapasitas }}</p>
                                    </td>
                                    <td class="px-6 py-6 border-b border-gray-50 text-sm font-semibold text-gray-600">{{ $detail->jumlah }} unit</td>
                                    <td class="px-6 py-6 border-b border-gray-50 text-sm font-semibold text-gray-600">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                    <td class="px-6 py-6 border-b border-gray-50 text-sm font-black text-red-700">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($pesanan->keterangan)
                <div class="mt-6 p-4 bg-gray-50 rounded-2xl border border-gray-100 text-sm text-gray-700">
                    <p class="font-bold text-gray-900 mb-1">Keterangan / Catatan:</p>
                    {{ $pesanan->keterangan }}
                </div>
                @endif

                @if($pesanan->isServiceOrder())
                @php
                    $serviceLines = $pesanan->servicePricingBreakdown();
                    $servicePeralatan = $pesanan->servicePeralatanItems();
                @endphp
                <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Harga Per Unit</p>
                        <div class="space-y-3">
                            @foreach($serviceLines as $line)
                                <div class="rounded-xl border border-white bg-white px-4 py-3">
                                    <p class="text-sm font-black text-gray-900">{{ $line['label'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-gray-500">{{ (int) ($line['qty'] ?? 1) }} unit • Rp {{ number_format((float) ($line['total'] ?? 0), 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Peralatan Paket</p>
                        <div class="space-y-3">
                            @forelse($servicePeralatan as $item)
                                <div class="flex items-center justify-between rounded-xl border border-white bg-white px-4 py-3">
                                    <span class="text-sm font-black text-gray-900">{{ $item['nama'] ?? '-' }}</span>
                                    <span class="text-xs font-semibold text-gray-500">x{{ (int) ($item['jumlah'] ?? 0) }}</span>
                                </div>
                            @empty
                                <p class="text-sm font-semibold text-gray-500">Tidak ada peralatan terhubung.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-8 flex justify-end">
                    <div class="w-full md:w-[360px] rounded-[2rem] bg-gray-50 border border-gray-100 p-6 space-y-4">
                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                            <span>Subtotal Sistem</span>
                            @if($pesanan->tipe === 'produk')
                                <span>Rp {{ number_format((float) $pricingSummary['subtotalProduk'], 0, ',', '.') }}</span>
                            @else
                                <span>Rp 0</span>
                            @endif
                        </div>
                        
                        @if((float) $pricingSummary['nominalDiskon'] > 0)
                        <div class="flex items-center justify-between text-sm font-semibold text-green-700 bg-green-50 p-2 rounded-lg mt-2">
                            <span>Diskon Promo Pembelian</span>
                            <span>-Rp {{ number_format((float) $pricingSummary['nominalDiskon'], 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                            <span>Ongkir</span>
                            <span>Rp {{ number_format((float) $pricingSummary['ongkir'], 0, ',', '.') }}</span>
                        </div>

                        @if(!empty($pricingSummary['specialPriceActive']))
                        <div class="flex items-center justify-between text-sm font-semibold text-emerald-700 bg-emerald-50 p-2 rounded-lg mt-2">
                            <span>Harga Final</span>
                            <span>Rp {{ number_format((float) $pricingSummary['hargaFinal'], 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                            <span class="text-sm font-black text-gray-900 uppercase tracking-widest">Total Akhir</span>
                            <span class="text-2xl font-black text-red-700">Rp {{ number_format((float) $pricingSummary['totalPembayaran'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @if($pesanan->hasPurchasePriceRequest())
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-lg font-black text-gray-900 tracking-tight">Pengajuan Harga Pembelian</h3>
                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black {{ $pesanan->purchasePriceStatusClasses() }}">
                                {{ $purchasePriceLabel }}
                            </span>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Harga Pengajuan Pelanggan</p>
                                <p class="mt-2 text-xl font-black text-gray-900">Rp {{ number_format((float) ($pricingSummary['hargaPengajuan'] ?? 0), 0, ',', '.') }}</p>
                                @if($pesanan->purchasePriceCustomerNote())
                                    <p class="mt-3 text-xs font-semibold leading-relaxed text-gray-600">{{ $pesanan->purchasePriceCustomerNote() }}</p>
                                @endif
                            </div>

                            @if($pesanan->hasPendingPurchasePriceRequest())
                                <form method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="harga_final" class="block text-sm font-bold text-gray-700 mb-2">Harga Final</label>
                                        <input
                                            type="text"
                                            id="harga_final"
                                            name="harga_final"
                                            value="{{ old('harga_final') }}"
                                            placeholder="Rp 0"
                                            inputmode="numeric"
                                            class="w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 text-sm font-semibold"
                                        >
                                        @error('harga_final')
                                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="catatan_admin" class="block text-sm font-bold text-gray-700 mb-2">Catatan Admin</label>
                                        <textarea
                                            id="catatan_admin"
                                            name="catatan_admin"
                                            rows="3"
                                            class="w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 text-sm"
                                            placeholder="Opsional. Tambahkan alasan singkat jika diperlukan."
                                        >{{ old('catatan_admin') }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <button
                                            type="submit"
                                            formaction="{{ route('admin.pesanan.pengajuan-harga.acc', $pesanan) }}"
                                            class="w-full py-3 bg-emerald-600 text-white font-black text-sm rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/25"
                                        >
                                            ACC
                                        </button>
                                        <button
                                            type="submit"
                                            formaction="{{ route('admin.pesanan.pengajuan-harga.tolak', $pesanan) }}"
                                            class="w-full py-3 bg-red-600 text-white font-black text-sm rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-600/25"
                                        >
                                            Tolak
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 space-y-3">
                                    @if(!empty($pricingSummary['hargaFinal']))
                                        <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                                            <span>Harga Final</span>
                                            <span class="text-emerald-700">Rp {{ number_format((float) $pricingSummary['hargaFinal'], 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                    @if($pesanan->catatan_admin)
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Catatan Admin</p>
                                            <p class="mt-2 text-sm font-semibold leading-relaxed text-gray-700">{{ $pesanan->catatan_admin }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($showAssignAction)
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Penugasan Teknisi</h3>
                        <p class="mt-2 text-sm font-medium text-gray-500">Pesanan ini sudah siap diproses dan bisa langsung dibagikan ke teknisi sesuai alur lama.</p>
                        <form action="{{ route('admin.pesanan.assign-teknisi', $pesanan) }}" method="POST" class="mt-5">
                            @csrf
                            <button type="submit" class="w-full py-3 bg-red-700 text-white font-black text-sm rounded-xl hover:bg-red-800 transition shadow-lg shadow-red-700/30">
                                Assign Teknisi
                            </button>
                        </form>
                    </div>
                @endif

                @if($showFinalizeAction)
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Penyelesaian Pesanan</h3>
                        <p class="mt-2 text-sm font-medium text-gray-500">Pengerjaan sudah selesai. Finalkan pesanan untuk menutup transaksi sesuai alur sistem.</p>
                        <form action="{{ route('admin.pesanan.selesai-final', $pesanan) }}" method="POST" class="mt-5" data-confirm="Selesaikan final pesanan ini?" data-confirm-title="Konfirmasi Final" data-confirm-button="Ya, Finalkan">
                            @csrf
                            <button type="submit" class="w-full py-3 bg-emerald-600 text-white font-black text-sm rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/25">
                                Selesaikan Final
                            </button>
                        </form>
                    </div>
                @endif

                @if(!$pesanan->hasPurchasePriceRequest() && !$showAssignAction && !$showFinalizeAction)
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Status Otomatis</h3>
                        <p class="mt-2 text-sm font-medium leading-relaxed text-gray-500">Status pesanan ini mengikuti alur sistem secara otomatis. Tidak ada pengajuan harga yang perlu ditindaklanjuti pada detail ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function () {
            const input = document.getElementById('harga_final');
            if (!input) return;

            const formatInput = () => {
                const digits = String(input.value || '').replace(/\D+/g, '');
                input.value = digits ? 'Rp ' + Number(digits).toLocaleString('id-ID') : '';
            };

            input.addEventListener('input', formatInput);
            formatInput();
        }());
    </script>
</x-app-layout>
