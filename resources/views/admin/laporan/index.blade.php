<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Pusat Laporan</h2>
            <p class="text-sm text-gray-500 font-medium">Rekapitulasi operasional APAR, pesanan, service, dan keuangan untuk dokumentasi bisnis</p>
        </div>
    </x-slot>

    <div class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total APAR</p>
                <p class="text-3xl font-black text-gray-900 mt-3">{{ $summary['totalApar'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">APAR Expired</p>
                <p class="text-3xl font-black text-red-700 mt-3">{{ $summary['totalExpired'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pesanan</p>
                <p class="text-3xl font-black text-emerald-600 mt-3">{{ $summary['totalPesanan'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Service</p>
                <p class="text-3xl font-black text-blue-600 mt-3">{{ $summary['totalService'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nilai Pesanan</p>
                <p class="text-2xl font-black text-gray-900 mt-3">Rp {{ number_format($summary['totalNilaiPesanan'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-4 gap-6">
            <a href="{{ route('admin.laporan.apar') }}" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Laporan 01</p>
                <h3 class="text-2xl font-black text-gray-900 mt-4">Laporan APAR</h3>
                <p class="text-sm font-medium text-gray-500 mt-3 leading-relaxed">
                    Menampilkan seluruh data APAR dengan filter tanggal beli dan pelanggan.
                </p>
            </a>
            <a href="{{ route('admin.laporan.pesanan') }}" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Laporan 02</p>
                <h3 class="text-2xl font-black text-gray-900 mt-4">Laporan Pesanan</h3>
                <p class="text-sm font-medium text-gray-500 mt-3 leading-relaxed">
                    Menampilkan seluruh pesanan dari WhatsApp lengkap dengan filter tanggal dan pelanggan.
                </p>
            </a>
            <a href="{{ route('admin.laporan.service') }}" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Laporan 03</p>
                <h3 class="text-2xl font-black text-gray-900 mt-4">Laporan Service</h3>
                <p class="text-sm font-medium text-gray-500 mt-3 leading-relaxed">
                    Menampilkan riwayat service lengkap dengan jenis service, tanggal, dan total biaya.
                </p>
            </a>
            <a href="{{ route('admin.laporan.keuangan') }}" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Laporan 04</p>
                <h3 class="text-2xl font-black text-gray-900 mt-4">Laporan Keuangan</h3>
                <p class="text-sm font-medium text-gray-500 mt-3 leading-relaxed">
                    Merangkum total pemasukan service dan nilai transaksi pesanan yang berjalan.
                </p>
            </a>
        </div>
    </div>
</x-app-layout>
