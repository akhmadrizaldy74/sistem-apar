@extends('layouts.public')

@section('title', 'Checkout — PD. Anugrah Utama')

@section('content')
    <section class="bg-gradient-to-b from-gray-50 to-white min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">

            {{-- Header --}}
            <div data-reveal>
                <a href="{{ route('keranjang.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-red-700 transition mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Keranjang
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-700 rounded-2xl flex items-center justify-center shadow-lg shadow-red-700/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-gray-900">Checkout</h1>
                        <p class="text-gray-500 font-medium mt-1">Lengkapi data pengiriman untuk menyelesaikan pesanan</p>
                    </div>
                </div>
            </div>

            {{-- Stepper --}}
            <div class="mt-8 flex items-center justify-center gap-2 sm:gap-4" data-reveal>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-black">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 hidden sm:inline">Keranjang</span>
                </div>
                <div class="w-8 sm:w-16 h-0.5 bg-red-700 rounded-full"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-700 text-white rounded-full flex items-center justify-center text-xs font-black">2</div>
                    <span class="text-xs font-bold text-red-700 hidden sm:inline">Checkout</span>
                </div>
                <div class="w-8 sm:w-16 h-0.5 bg-gray-200 rounded-full"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center text-xs font-black">3</div>
                    <span class="text-xs font-bold text-gray-400 hidden sm:inline">Pembayaran</span>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 font-semibold text-sm flex items-center gap-3" data-reveal>
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('checkout.store') }}" method="POST" class="mt-10" id="checkout-form">
                @csrf

                <div class="grid lg:grid-cols-3 gap-8">
                    {{-- Form Data Pengiriman --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Info Penerima --}}
                        <div class="bg-white rounded-[1.75rem] border border-gray-100 shadow-xl shadow-gray-200/50 p-7" data-reveal>
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-gray-900">Data Penerima</h3>
                                    <p class="text-xs text-gray-400 font-medium">Otomatis terisi dari profil Anda. Bisa di-edit jika berbeda.</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="nama_penerima" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Penerima <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_penerima" id="nama_penerima"
                                        value="{{ old('nama_penerima', $defaultData['nama_penerima']) }}"
                                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl font-semibold text-gray-900 focus:ring-2 focus:ring-red-600/20 focus:border-red-400 transition placeholder:text-gray-300"
                                        placeholder="Masukkan nama penerima" required>
                                    @error('nama_penerima')
                                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="nomor_wa_penerima" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor WhatsApp Penerima <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-1.5 text-gray-400">
                                            <i class="fa-brands fa-whatsapp text-[#25D366]"></i>
                                        </div>
                                        <input type="text" name="nomor_wa_penerima" id="nomor_wa_penerima"
                                            value="{{ old('nomor_wa_penerima', $defaultData['nomor_wa_penerima']) }}"
                                            class="w-full pl-10 pr-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl font-semibold text-gray-900 focus:ring-2 focus:ring-red-600/20 focus:border-red-400 transition placeholder:text-gray-300"
                                            placeholder="08xxxxxxxxxx" required>
                                    </div>
                                    @error('nomor_wa_penerima')
                                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Alamat Pengiriman --}}
                        <div class="bg-white rounded-[1.75rem] border border-gray-100 shadow-xl shadow-gray-200/50 p-7" data-reveal>
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-gray-900">Alamat Pengiriman</h3>
                                    <p class="text-xs text-gray-400 font-medium">Alamat ini otomatis diambil dari profil pelanggan dan tetap bisa disesuaikan jika diperlukan.</p>
                                </div>
                            </div>

                            @if(blank($defaultData['alamat_pengiriman']))
                                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                                    Alamat profil Anda belum tersimpan. Isi sekali di checkout ini atau lengkapi di menu profil agar pembelian berikutnya otomatis terisi.
                                </div>
                            @endif

                            <div>
                                <label for="alamat_pengiriman" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea name="alamat_pengiriman" id="alamat_pengiriman" rows="4"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl font-semibold text-gray-900 focus:ring-2 focus:ring-red-600/20 focus:border-red-400 transition placeholder:text-gray-300 resize-none"
                                    placeholder="Contoh: Jl. Raya Bogor No. 123, RT 01/RW 02, Kel. Baranangsiang, Kec. Bogor Timur, Kota Bogor, Jawa Barat 16143"
                                    required>{{ old('alamat_pengiriman', $defaultData['alamat_pengiriman']) }}</textarea>
                                @error('alamat_pengiriman')
                                    <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Item Summary (Mobile) --}}
                        <div class="lg:hidden bg-white rounded-[1.75rem] border border-gray-100 shadow-xl shadow-gray-200/50 p-7" data-reveal>
                            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Produk Dipesan</h3>
                            <div class="space-y-3">
                                @foreach($keranjangs as $item)
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                            @if($item->produk->gambar)
                                                <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $item->produk->nama }}</p>
                                            <p class="text-xs text-gray-400 font-medium">×{{ $item->qty }} @ Rp {{ number_format($item->harga, 0, ',', '.') }}</p>
                                        </div>
                                        <p class="text-sm font-black text-gray-900 whitespace-nowrap">Rp {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="border-t border-gray-100 mt-4 pt-4 flex justify-between items-center">
                                <p class="text-sm font-bold text-gray-500">Total</p>
                                <p class="text-xl font-black text-red-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Order Summary (Desktop Sidebar) --}}
                    <div class="lg:col-span-1 hidden lg:block">
                        <div class="bg-white rounded-[1.75rem] border border-gray-100 shadow-xl shadow-gray-200/50 p-7 sticky top-24" data-reveal>
                            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Pesanan</h3>

                            <div class="mt-6 space-y-4">
                                @foreach($keranjangs as $item)
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                            @if($item->produk->gambar)
                                                <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $item->produk->nama }}</p>
                                            <p class="text-xs text-gray-400 font-medium">×{{ $item->qty }}</p>
                                        </div>
                                        <p class="text-sm font-bold text-gray-900 whitespace-nowrap">Rp {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-gray-100 mt-6 pt-5 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <p class="text-gray-400 font-medium">Subtotal ({{ $keranjangs->sum('qty') }} item)</p>
                                    <p class="font-bold text-gray-900">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <p class="text-gray-400 font-medium">Ongkir</p>
                                    <p class="font-bold text-emerald-600">Dihitung Admin</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 mt-4 pt-4">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-bold text-gray-500">Total</p>
                                    <p class="text-2xl font-black text-red-600 tracking-tight">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <button type="submit" form="checkout-form" class="block w-full mt-6 py-4 bg-red-700 text-white font-black text-sm rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/25 text-center uppercase tracking-widest">
                                <svg class="w-5 h-5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Buat Pesanan
                            </button>

                            <p class="text-[10px] text-gray-400 font-medium text-center mt-4 leading-relaxed">
                                Dengan menekan "Buat Pesanan", Anda menyetujui syarat dan ketentuan yang berlaku.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Mobile Submit Button --}}
                <div class="lg:hidden mt-8" data-reveal>
                    <button type="submit" class="block w-full py-4 bg-red-700 text-white font-black text-sm rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/25 text-center uppercase tracking-widest">
                        <svg class="w-5 h-5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Buat Pesanan
                    </button>
                    <p class="text-[10px] text-gray-400 font-medium text-center mt-3">
                        Dengan menekan "Buat Pesanan", Anda menyetujui syarat dan ketentuan yang berlaku.
                    </p>
                </div>
            </form>
        </div>
    </section>
@endsection
