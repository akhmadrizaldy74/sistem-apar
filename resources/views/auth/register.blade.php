<x-guest-layout>
    <div class="mb-8" data-reveal>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Buat Akun Pelanggan</h2>
        <p class="text-sm text-gray-500 font-medium">Daftar sekarang untuk mulai berbelanja dan melacak pesanan.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-text-input id="name" class="block mt-1 w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-2xl focus:ring-red-700 focus:border-red-700 block w-full p-4 font-medium transition" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Lengkap" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-text-input id="no_telpon" class="block mt-1 w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-2xl focus:ring-red-700 focus:border-red-700 block w-full p-4 font-medium transition" type="tel" name="no_telpon" :value="old('no_telpon')" required autocomplete="tel" placeholder="Nomor Telepon / WhatsApp" />
            <x-input-error :messages="$errors->get('no_telpon')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-text-input id="password" class="block mt-1 w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-2xl focus:ring-red-700 focus:border-red-700 block w-full p-4 font-medium transition"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="Password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-text-input id="password_confirmation" class="block mt-1 w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-2xl focus:ring-red-700 focus:border-red-700 block w-full p-4 font-medium transition"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Konfirmasi Password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full py-5 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-900/35 hover:shadow-red-900/50 uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:-translate-y-0.5 transform">
                Daftar Sekarang
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
            </button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 font-medium">Sudah punya akun?</p>
            <a href="{{ route('login') }}" class="mt-2 inline-block text-sm font-black text-red-700 hover:text-red-800 transition uppercase tracking-widest">
                Masuk di sini
            </a>
        </div>
    </form>
</x-guest-layout>
