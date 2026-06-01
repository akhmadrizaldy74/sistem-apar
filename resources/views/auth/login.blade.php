<x-guest-layout>
    <div class="mb-8" data-reveal>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Masuk ke Sistem</h2>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6" data-reveal>
        @csrf

        <!-- Email Address -->
        <!-- Login Field -->
        <div>
            <label for="login" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Email atau Nomor Telepon</label>
            <x-text-input id="login" class="block w-full pl-6 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-red-600/20 focus:bg-white font-bold text-gray-900 placeholder:text-gray-300 transition shadow-sm focus:shadow-md" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="Contoh: example@gmail.com atau 08123456789" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <x-input-error :messages="$errors->get('no_telpon')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-[10px] font-black text-red-700 uppercase tracking-widest hover:underline" href="{{ route('password.request') }}">
                        Lupa Password
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full pl-14 pr-12 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-red-600/20 focus:bg-white font-bold text-gray-900 placeholder:text-gray-300 transition shadow-sm focus:shadow-md"
                    placeholder="••••••••••••">
                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-6 flex items-center text-gray-400 hover:text-gray-600 transition cursor-pointer">
                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded-lg border-gray-200 text-red-700 focus:ring-red-600/20 shadow-sm transition cursor-pointer">
            <label for="remember_me" class="ms-3 text-[10px] font-black text-gray-400 uppercase tracking-widest cursor-pointer select-none">
                Ingat Saya
            </label>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-5 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-900/35 hover:shadow-red-900/50 uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:-translate-y-0.5 transform">
                Masuk
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
            </button>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 font-medium">Belum punya akun?</p>
            <a href="{{ route('register') }}" class="mt-2 inline-block text-sm font-black text-red-700 hover:text-red-800 transition uppercase tracking-widest">
                Buat Akun
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'text') {
                        eyeIcon.classList.add('hidden');
                        eyeOffIcon.classList.remove('hidden');
                    } else {
                        eyeIcon.classList.remove('hidden');
                        eyeOffIcon.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</x-guest-layout>
