<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.purchase-orders.index') }}" class="p-2.5 bg-white rounded-2xl border border-slate-200 text-slate-400 hover:text-red-700 hover:border-red-200 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ $purchaseOrder->nomor_po }}</h2>
                        @if($purchaseOrder->status === 'draft')
                            <span class="px-4 py-1 bg-slate-100 text-slate-600 border border-slate-200 text-xs font-black uppercase tracking-widest rounded-full">
                                Draft
                            </span>
                        @elseif($purchaseOrder->status === 'dikirim')
                            <span class="px-4 py-1 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-black uppercase tracking-widest rounded-full">
                                Dikirim
                            </span>
                        @elseif($purchaseOrder->status === 'diterima')
                            <span class="px-4 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-black uppercase tracking-widest rounded-full flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                Diterima / Selesai
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500 font-semibold mt-0.5">Tanggal PO: {{ $purchaseOrder->tanggal_po ? $purchaseOrder->tanggal_po->format('d F Y') : '-' }}</p>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Cetak PDF -->
                <a href="{{ route('admin.purchase-orders.pdf', $purchaseOrder->id) }}" target="_blank"
                    class="px-6 py-3.5 bg-slate-700 text-white font-black rounded-2xl hover:bg-slate-800 transition flex items-center gap-2 uppercase tracking-wider text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak PDF
                </a>

                <!-- Kirim via WA (Draft & Dikirim) -->
                @if(in_array($purchaseOrder->status, ['draft', 'dikirim']))
                    <form action="{{ route('admin.purchase-orders.kirim', $purchaseOrder->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-6 py-3.5 bg-emerald-600 text-white font-black rounded-2xl hover:bg-emerald-700 transition flex items-center gap-2 uppercase tracking-wider text-xs shadow-lg shadow-emerald-600/20">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.14 4.162 4.162-1.144z"/></svg>
                            Kirim via WhatsApp
                        </button>
                    </form>
                @endif

                @if($purchaseOrder->status === 'draft')
                    <a href="{{ route('admin.purchase-orders.edit', $purchaseOrder->id) }}"
                        class="px-6 py-3.5 bg-amber-500 text-white font-black rounded-2xl hover:bg-amber-600 transition flex items-center gap-2 uppercase tracking-wider text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit PO
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Info Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Info PO Card -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Dokumen</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1">{{ $purchaseOrder->nomor_po }}</h3>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Tanggal PO:</span>
                        <span class="font-bold text-slate-900">{{ $purchaseOrder->tanggal_po ? $purchaseOrder->tanggal_po->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Status PO:</span>
                        <span class="font-bold uppercase tracking-wider text-xs">
                            @if($purchaseOrder->status === 'draft')
                                <span class="text-slate-600">Draft</span>
                            @elseif($purchaseOrder->status === 'dikirim')
                                <span class="text-blue-600">Dikirim</span>
                            @else
                                <span class="text-emerald-600">Diterima</span>
                            @endif
                        </span>
                    </div>
                    @if($purchaseOrder->catatan)
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-400 font-semibold block text-xs">Catatan:</span>
                            <p class="text-xs font-semibold text-slate-700 mt-1 italic bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $purchaseOrder->catatan }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Info Supplier Card -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Supplier</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1">{{ $purchaseOrder->supplier?->nama_supplier }}</h3>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Kontak Person:</span>
                        <span class="font-bold text-slate-900">{{ $purchaseOrder->supplier?->kontak_person ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">No. Telepon:</span>
                        <span class="font-bold text-slate-900">{{ $purchaseOrder->supplier?->no_telepon ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-semibold">No. WhatsApp:</span>
                        @php
                            $supplierPhone = $purchaseOrder->supplier?->no_wa ?: $purchaseOrder->supplier?->telepon ?: '';
                            $supplierWaClean = preg_replace('/\D/', '', (string) $supplierPhone);
                            if (str_starts_with($supplierWaClean, '0')) {
                                $supplierWaClean = '62' . substr($supplierWaClean, 1);
                            }
                        @endphp
                        @if($supplierWaClean)
                            <a href="https://wa.me/{{ $supplierWaClean }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 font-bold hover:underline">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.14 4.162 4.162-1.144z"/></svg>
                                {{ $purchaseOrder->supplier?->no_wa ?: $purchaseOrder->supplier?->telepon }}
                            </a>
                        @else
                            <span class="text-slate-400 font-bold">-</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Surat Jalan Card (Jika Dikirim atau Diterima) -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Surat Jalan Supplier</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1">
                        {{ $purchaseOrder->no_surat_jalan ?: 'Belum Ada' }}
                    </h3>
                </div>

                @if($purchaseOrder->status === 'dikirim')
                    <form action="{{ route('admin.purchase-orders.simpan-surat-jalan', $purchaseOrder->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="no_surat_jalan" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-1">
                                No. Surat Jalan <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="no_surat_jalan" id="no_surat_jalan" value="{{ old('no_surat_jalan', $purchaseOrder->no_surat_jalan) }}" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-red-500 outline-none"
                                placeholder="Masukkan No. Surat Jalan">
                        </div>

                        <div>
                            <label for="tanggal_surat_jalan" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-1">
                                Tanggal Surat Jalan <span class="text-red-600">*</span>
                            </label>
                            <input type="date" name="tanggal_surat_jalan" id="tanggal_surat_jalan" value="{{ old('tanggal_surat_jalan', $purchaseOrder->tanggal_surat_jalan ? $purchaseOrder->tanggal_surat_jalan->format('Y-m-d') : date('Y-m-d')) }}" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-red-500 outline-none">
                        </div>

                        <button type="submit" class="w-full py-3 bg-red-700 text-white font-black rounded-xl hover:bg-red-800 transition text-xs uppercase tracking-wider">
                            Simpan Surat Jalan
                        </button>
                    </form>
                @elseif($purchaseOrder->status === 'diterima')
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-semibold">No. Surat Jalan:</span>
                            <span class="font-bold text-slate-900">{{ $purchaseOrder->no_surat_jalan }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-semibold">Tanggal Surat Jalan:</span>
                            <span class="font-bold text-slate-900">{{ $purchaseOrder->tanggal_surat_jalan ? $purchaseOrder->tanggal_surat_jalan->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Stok & Pengeluaran Terupdate
                            </span>
                        </div>
                    </div>
                @else
                    <p class="text-xs font-semibold text-slate-400 italic">Status masih Draft. Kirim PO ke supplier terlebih dahulu untuk mengaktifkan form surat jalan.</p>
                @endif
            </div>
        </div>

        <!-- Confirm Terima Barang Action Banner (Status Dikirim & Surat Jalan Ready) -->
        @if($purchaseOrder->status === 'dikirim' && $purchaseOrder->no_surat_jalan && $purchaseOrder->tanggal_surat_jalan)
            <div class="bg-gradient-to-r from-red-600 via-red-700 to-red-800 text-white p-8 rounded-[2.5rem] shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h4 class="text-xl font-black uppercase tracking-wide">Barang Sudah Diterima Sesuai Surat Jalan?</h4>
                    <p class="text-sm text-red-100 font-semibold mt-1">Konfirmasi penerimaan akan menambah stok produk/refill/peralatan secara otomatis ke sistem dan mencatat pengeluaran secara otomatis.</p>
                </div>
                <form action="{{ route('admin.purchase-orders.terima', $purchaseOrder->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sudah diterima lengkap sesuai surat jalan {{ $purchaseOrder->no_surat_jalan }}? Stok dan Pengeluaran akan diperbarui otomatis.')">
                    @csrf
                    <button type="submit" class="px-8 py-4 bg-white text-red-700 font-black rounded-2xl hover:bg-slate-100 transition uppercase tracking-widest text-xs shadow-lg whitespace-nowrap">
                        Konfirmasi Terima Barang
                    </button>
                </form>
            </div>
        @endif

        <!-- Table Detail Item -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-lg font-black text-slate-900 uppercase tracking-wide">Detail Item Pembelian</h3>
                <p class="text-xs text-slate-400 font-semibold">Rincian item barang dan harga satuan PO</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center w-16">No</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Item</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Kategori</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Jumlah</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Harga Satuan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right pr-8">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($purchaseOrder->details as $index => $detail)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-slate-400 text-center">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-base font-black text-slate-900">{{ $detail->nama_item }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-widest rounded-full">
                                        {{ $detail->kategori }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-slate-900">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-900">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right pr-8 text-sm font-black text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 bg-slate-50/50">
                            <td colspan="5" class="px-6 py-4 text-right font-black text-slate-700 uppercase tracking-wider text-xs">Total Keseluruhan:</td>
                            <td class="px-6 py-4 text-right pr-8 text-xl font-black text-red-700">Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @if(session('wa_url'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const pdfUrl = @js(session('pdf_download_url'));
                const waUrl = @js(session('wa_url'));

                if (pdfUrl) {
                    const link = document.createElement('a');
                    link.href = pdfUrl;
                    link.download = '';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }

                if (waUrl) {
                    setTimeout(function() {
                        window.open(waUrl, '_blank');
                    }, 500);
                }
            });
        </script>
    @endif
</x-app-layout>
