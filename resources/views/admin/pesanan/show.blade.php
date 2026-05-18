<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Invoice Pesanan</h2>
                <p class="text-sm text-gray-500 font-medium">Detail transaksi pembelian yang masuk dari Form Order / WhatsApp.</p>
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

    <div class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 md:p-10">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomor Invoice</p>
                        <h3 class="text-3xl font-black text-gray-900 mt-3">INV-{{ str_pad((string) $pesanan->id, 5, '0', STR_PAD_LEFT) }}</h3>
                        <p class="text-sm font-medium text-gray-500 mt-3">Tanggal: {{ $pesanan->tanggal->format('d M Y') }}</p>
                        <p class="text-xs font-black uppercase tracking-widest mt-3 text-emerald-700">
                            Pesanan {{ ucfirst($pesanan->tipe) }}
                        </p>
                        <span class="inline-block mt-3 px-3 py-1 text-xs font-bold uppercase rounded-full 
                            @if($pesanan->status == 'menunggu') bg-gray-100 text-gray-600 
                            @elseif($pesanan->status == 'menunggu persetujuan') bg-amber-100 text-amber-700 
                            @elseif($pesanan->status == 'diproses') bg-sky-100 text-sky-700 
                            @elseif($pesanan->status == 'selesai') bg-emerald-100 text-emerald-700 
                            @endif">
                            {{ $pesanan->status }}
                        </span>
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
                            <img src="{{ asset('storage/' . ltrim($pesanan->bukti_pembayaran, '/')) }}" alt="Bukti pembayaran pesanan #{{ $pesanan->id }}" class="w-full max-h-72 object-contain rounded-xl border border-gray-200 bg-white">
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

                <div class="mt-8 flex justify-end">
                    <div class="w-full md:w-[360px] rounded-[2rem] bg-gray-50 border border-gray-100 p-6 space-y-4">
                        @php
                            $customerOffer = $pesanan->harga_penawaran_pelanggan ?? $pesanan->harga_usulan;
                            $approvedDeal = $pesanan->harga_usulan;
                        @endphp
                        <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                            <span>Subtotal Sistem</span>
                            @if($pesanan->tipe === 'produk')
                                <span>Rp {{ number_format($pesanan->details->sum('subtotal'), 0, ',', '.') }}</span>
                            @else
                                <span>Rp 0</span>
                            @endif
                        </div>
                        
                        @if($customerOffer)
                        <div class="flex items-center justify-between text-sm font-semibold text-amber-600 bg-amber-50 p-2 rounded-lg">
                            <span>Penawaran Pelanggan</span>
                            <span>Rp {{ number_format($customerOffer, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($approvedDeal && (!$customerOffer || (float) $approvedDeal !== (float) $customerOffer || $pesanan->kode_nego))
                        <div class="flex items-center justify-between text-sm font-semibold text-blue-700 bg-blue-50 p-2 rounded-lg">
                            <span>Harga Deal Admin</span>
                            <span>Rp {{ number_format($approvedDeal, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                            <span class="text-sm font-black text-gray-900 uppercase tracking-widest">Total Akhir</span>
                            <span class="text-2xl font-black text-red-700">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight mb-4">Proses Transaksi</h3>
                    <form action="{{ route('admin.pesanan.update', $pesanan) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ubah Status</label>
                            <select name="status" class="w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 text-sm">
                                <option value="menunggu" {{ $pesanan->status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="menunggu persetujuan" {{ $pesanan->status == 'menunggu persetujuan' ? 'selected' : '' }}>Menunggu Persetujuan Nego</option>
                                <option value="pending" {{ $pesanan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="diproses" {{ $pesanan->status == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                <option value="ditugaskan ke teknisi" {{ $pesanan->status == 'ditugaskan ke teknisi' ? 'selected' : '' }}>Ditugaskan ke Teknisi</option>
                                <option value="dikerjakan teknisi" {{ $pesanan->status == 'dikerjakan teknisi' ? 'selected' : '' }}>Dikerjakan Teknisi</option>
                                <option value="selesai oleh teknisi" {{ $pesanan->status == 'selesai oleh teknisi' ? 'selected' : '' }}>Selesai oleh Teknisi</option>
                                <option value="dikonfirmasi admin" {{ $pesanan->status == 'dikonfirmasi admin' ? 'selected' : '' }}>Dikonfirmasi Admin</option>
                                <option value="selesai final" {{ $pesanan->status == 'selesai final' ? 'selected' : '' }}>Selesai Final</option>
                                <option value="selesai" {{ $pesanan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="ditolak" {{ $pesanan->status == 'ditolak' ? 'selected' : '' }}>Ditolak / Batal</option>
                            </select>
                        </div>

                        @if(in_array($pesanan->status, ['selesai oleh teknisi', 'dikonfirmasi admin']))
                        <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-200">
                            <p class="text-xs font-bold text-emerald-800 mb-3">Pengerjaan selesai oleh teknisi. Klik tombol di bawah untuk menyelesaikan final.</p>
                            <form action="{{ route('admin.pesanan.selesai-final', $pesanan) }}" method="POST" onsubmit="return confirm('Selesaikan final pesanan ini?')">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-emerald-600 text-white font-black text-sm rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Selesaikan Final
                                </button>
                            </form>
                        </div>
                        @endif

                        @if($pesanan->status === 'menunggu persetujuan' && ($pesanan->harga_penawaran_pelanggan || $pesanan->harga_usulan))
                        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-200">
                            @php $customerOffer = $pesanan->harga_penawaran_pelanggan ?? $pesanan->harga_usulan; @endphp
                            <p class="text-xs font-bold text-amber-800 mb-2">Pelanggan Mengajukan Negosiasi</p>
                            <p class="text-sm text-amber-900 mb-3 flex justify-between">
                                <span>Dari: Rp {{ number_format($pesanan->details->sum('subtotal'), 0, ',', '.') }}</span><br>
                                <strong>Menjadi: Rp {{ number_format($customerOffer, 0, ',', '.') }}</strong>
                            </p>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="terima_negosiasi" value="1" class="rounded text-red-600 focus:ring-red-500">
                                <span class="text-sm font-bold text-red-700">Terima Harga Nego</span>
                            </label>
                        </div>
                        @endif

                        <button type="submit" class="w-full py-4 mt-6 bg-red-700 text-white rounded-2xl text-sm font-black hover:bg-red-800 transition shadow-lg shadow-red-700/30 flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Konfirmasi & Simpan
                        </button>
                    </form>
                    
                    @if($pesanan->tipe === 'produk')
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <p class="text-xs text-gray-500 font-medium">Jika status diubah ke <b>Selesai</b>, sistem otomatis mengurangi Stok APAR dan meregistrasikan Data Unit APAR kepada Pelanggan ini dengan garansi masa berlaku berdasarkan tabung.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
