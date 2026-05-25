@extends('layouts.public')

@section('title', 'Produk - PD. Anugrah Utama')

@section('content')
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6" data-reveal>
                <div class="max-w-3xl">
                    <p class="text-[10px] font-black text-red-600 uppercase tracking-[0.3em] mb-4">Produk</p>
                    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Produk APAR</h1>
                    <p class="text-gray-600 font-medium leading-relaxed mt-5">
                        Semua produk APAR yang tersedia dapat dilihat dan dipilih pelanggan dari halaman produk ini.
                    </p>
                </div>
                <a href="{{ route('cek-apar') }}" class="px-6 py-3 bg-gray-50 border border-gray-100 text-gray-900 font-bold rounded-2xl hover:shadow-md transition">
                    Cek APAR
                </a>
            </div>

            @if(session('success'))
                <div class="mt-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 font-semibold text-sm flex items-center gap-3" data-reveal>
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 font-semibold text-sm flex items-center gap-3" data-reveal>
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" class="mt-8 bg-gray-50 border border-gray-100 rounded-[1.75rem] p-6 grid md:grid-cols-4 gap-4 items-end" data-reveal>
                <div class="md:col-span-2">
                    <label for="jenis_apar_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Filter Jenis APAR</label>
                    <select name="jenis_apar_id" id="jenis_apar_id" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisApars as $jenisApar)
                            <option value="{{ $jenisApar->id }}" @selected($filters['jenis_apar_id'] == $jenisApar->id)>{{ $jenisApar->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="merek" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Filter Merek</label>
                    <select name="merek" id="merek" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                        <option value="">Semua Merek</option>
                        @foreach($mereks as $merek)
                            <option value="{{ $merek }}" @selected($filters['merek'] === $merek)>{{ $merek }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-5 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition uppercase tracking-widest text-xs">
                        Filter
                    </button>
                    <a href="{{ route('produk.index') }}" class="flex-1 px-5 py-4 bg-white border border-gray-100 text-gray-700 font-black rounded-2xl hover:shadow-md transition text-center uppercase tracking-widest text-xs">
                        Reset
                    </a>
                </div>
            </form>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($produks as $produk)
                    <x-product-card :produk="$produk" />
                @empty
                    <div class="col-span-full bg-gray-50 border border-gray-100 rounded-2xl p-12 text-center text-gray-600 font-medium" data-reveal>
                        Belum ada produk di database.
                    </div>
                @endforelse
            </div>

            <div class="mt-12" data-reveal>
                {{ $produks->links() }}
            </div>
        </div>
    </section>
@endsection
