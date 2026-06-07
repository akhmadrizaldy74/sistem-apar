<x-guest-layout>
    <div class="mb-8" data-reveal>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Lupa Password</h2>
        <p class="mt-2 text-sm font-medium text-gray-500">
            Masukkan email atau nomor WhatsApp yang terdaftar. Link reset password akan dikirim ke email akun Anda.
        </p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6" data-reveal>
        @csrf

        <div>
            <label for="login" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Email atau Nomor WhatsApp</label>
            <x-text-input id="login" class="block w-full pl-6 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-red-600/20 focus:bg-white font-bold text-gray-900 placeholder:text-gray-300 transition shadow-sm focus:shadow-md" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="Masukkan email atau nomor WhatsApp terdaftar" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-5 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-900/35 hover:shadow-red-900/50 uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:-translate-y-0.5 transform">
                Kirim Link Reset Password
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm font-black text-red-700 hover:text-red-800 transition uppercase tracking-widest">
                Kembali ke Login
            </a>
        </div>
    </form>
</x-guest-layout>
