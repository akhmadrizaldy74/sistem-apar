<x-guest-layout>
    <div class="mb-8" data-reveal>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Atur Password Baru</h2>
        <p class="mt-2 text-sm font-medium text-gray-500">
            Masukkan password baru minimal 8 karakter, lalu konfirmasi untuk menyelesaikan reset password.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6" data-reveal>
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
        <x-input-error :messages="$errors->get('email')" class="mt-2" />

        <!-- Password -->
        <div>
            <label for="password" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Password Baru</label>
            <x-text-input id="password" class="block w-full pl-6 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-red-600/20 focus:bg-white font-bold text-gray-900 placeholder:text-gray-300 transition shadow-sm focus:shadow-md" type="password" name="password" required autofocus autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Konfirmasi Password Baru</label>
            <x-text-input id="password_confirmation" class="block w-full pl-6 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-red-600/20 focus:bg-white font-bold text-gray-900 placeholder:text-gray-300 transition shadow-sm focus:shadow-md"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password baru" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-5 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-900/35 hover:shadow-red-900/50 uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:-translate-y-0.5 transform">
                Simpan Password Baru
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </button>
        </div>
    </form>
</x-guest-layout>
