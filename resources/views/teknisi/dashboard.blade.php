<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900">Dashboard Teknisi</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Ringkasan pekerjaan teknisi dari admin.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pekerjaan Aktif</p>
                <p class="mt-3 text-4xl font-black text-slate-900">{{ $summary['pekerjaan_aktif'] }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Sedang Dikerjakan</p>
                <p class="mt-3 text-4xl font-black text-blue-700">{{ $summary['sedang_dikerjakan'] }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Selesai Bulan Ini</p>
                <p class="mt-3 text-4xl font-black text-emerald-700">{{ $summary['selesai_bulan_ini'] }}</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <a href="{{ route('teknisi.pekerjaan-aktif') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40 transition hover:border-slate-300 hover:shadow-md">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Halaman Utama</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">Pekerjaan Aktif</h3>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-600">Lihat daftar pekerjaan aktif dan lanjutkan proses teknisi dengan cepat.</p>
            </a>

            <a href="{{ route('teknisi.riwayat-pekerjaan') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40 transition hover:border-slate-300 hover:shadow-md">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Arsip</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">Riwayat Pekerjaan</h3>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-600">Cek pekerjaan yang sudah diselesaikan teknisi beserta catatan yang tersimpan.</p>
            </a>
        </div>
    </div>
</x-app-layout>
