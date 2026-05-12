<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas Profil - {{ $profile['nama'] }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for standalone feel but following project styles) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#ec4899',
                        dark: '#0f172a',
                        card: '#1e293b'
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    animation: {
                        'gradient': 'gradient 8s linear infinite',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        gradient: {
                            '0%, 100%': { 'background-position': '0% 50%' },
                            '50%': { 'background-position': '100% 50%' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            background-color: #020617;
            color: #f1f5f9;
        }

        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .text-gradient {
            background: linear-gradient(to right, #818cf8, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bg-gradient-animate {
            background: linear-gradient(-45deg, #4f46e5, #9333ea, #db2777, #ea580c);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500/30 overflow-hidden h-screen" x-data="{ activeTab: 'profil' }">

    <!-- Background Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute top-[20%] -right-[5%] w-[30%] h-[30%] bg-pink-600/10 blur-[100px] rounded-full"></div>
        <div class="absolute -bottom-[10%] left-[20%] w-[35%] h-[35%] bg-blue-600/15 blur-[120px] rounded-full"></div>
    </div>

    <!-- Main Container -->
    <div class="relative h-full flex flex-col md:flex-row p-4 md:p-6 gap-6 max-w-[1440px] mx-auto">
        
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-80 flex flex-col gap-6 shrink-0 z-10">
            <!-- Header Card -->
            <div class="glass rounded-3xl p-6 shadow-2xl">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <i data-lucide="user-circle" class="text-white w-7 h-7"></i>
                    </div>
                    <div>
                        <h1 class="text-xs font-black uppercase tracking-widest text-slate-400">NIM dan Nama</h1>
                        <p class="text-lg font-bold text-white leading-tight">{{ $profile['npm'] }}</p>
                    </div>
                </div>
                <p class="text-indigo-400 font-bold text-sm ml-16">{{ $profile['nama'] }}</p>
            </div>

            <!-- Nav Card -->
            <div class="glass rounded-3xl p-3 flex flex-col gap-2 shadow-2xl overflow-hidden relative">
                <div class="px-4 py-3 border-b border-white/5 mb-2">
                    <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Menu Utama</h2>
                </div>
                
                <button 
                    @click="activeTab = 'profil'"
                    :class="activeTab === 'profil' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 scale-[1.02]' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 group"
                >
                    <i data-lucide="user" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                    <span class="font-bold tracking-wide">Profil Saya</span>
                    <i x-show="activeTab === 'profil'" data-lucide="chevron-right" class="w-4 h-4 ml-auto" x-cloak></i>
                </button>

                <button 
                    @click="activeTab = 'kesan'"
                    :class="activeTab === 'kesan' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 scale-[1.02]' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 group"
                >
                    <i data-lucide="message-square" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                    <span class="font-bold tracking-wide">Kesan Pesan</span>
                    <i x-show="activeTab === 'kesan'" data-lucide="chevron-right" class="w-4 h-4 ml-auto" x-cloak></i>
                </button>

                <div class="mt-8 p-4 bg-white/5 rounded-2xl border border-white/5">
                    <p class="text-[10px] font-bold text-slate-500 uppercase leading-relaxed">
                        Dibuat untuk tugas mata kuliah Pemrograman Web.
                    </p>
                </div>
            </div>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 glass rounded-[2.5rem] shadow-2xl flex flex-col overflow-hidden z-10 border border-white/10">
            
            <!-- Profil Section -->
            <div x-show="activeTab === 'profil'" 
                 x-transition:enter="transition ease-out duration-500 delay-100"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="h-full flex flex-col p-8 md:p-12 overflow-y-auto custom-scrollbar"
                 x-cloak>
                
                <div class="max-w-4xl mx-auto w-full">
                    <div class="mb-12">
                        <h2 class="text-4xl md:text-6xl font-extrabold text-white mb-4 tracking-tight">
                            Personal <span class="text-gradient">Profile</span>
                        </h2>
                        <div class="h-1.5 w-24 bg-gradient-to-r from-indigo-500 to-pink-500 rounded-full"></div>
                    </div>

                    <div class="flex flex-col lg:flex-row gap-12 items-start">
                        <!-- Photo Placeholder -->
                        <div class="relative group mx-auto lg:mx-0">
                            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-pink-500 rounded-[2rem] blur opacity-30 group-hover:opacity-60 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative w-64 h-80 rounded-[2rem] bg-slate-800 flex items-center justify-center overflow-hidden border-2 border-white/10">
                                <i data-lucide="image" class="w-16 h-16 text-slate-600 mb-4"></i>
                                <div class="absolute bottom-0 inset-x-0 p-6 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent">
                                    <p class="text-center text-xs font-bold text-indigo-400 uppercase tracking-widest">[ FOTO PROFIL ]</p>
                                </div>
                            </div>
                        </div>

                        <!-- Info Details -->
                        <div class="flex-1 space-y-6 w-full">
                            <div class="grid grid-cols-1 gap-6">
                                <div class="bg-white/5 rounded-3xl p-6 border border-white/5 hover:border-white/10 transition group">
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 group-hover:text-indigo-400 transition">Nama Lengkap</p>
                                    <p class="text-2xl font-bold text-white">{{ $profile['nama'] }}</p>
                                </div>
                                
                                <div class="bg-white/5 rounded-3xl p-6 border border-white/5 hover:border-white/10 transition group">
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 group-hover:text-indigo-400 transition">Alamat</p>
                                    <p class="text-xl font-medium text-slate-300">{{ $profile['alamat'] }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-white/5 rounded-3xl p-6 border border-white/5 hover:border-white/10 transition group">
                                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 group-hover:text-indigo-400 transition">Hobi</p>
                                        <p class="text-xl font-bold text-white">{{ $profile['hobi'] }}</p>
                                    </div>
                                    <div class="bg-white/5 rounded-3xl p-6 border border-white/5 hover:border-white/10 transition group">
                                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 group-hover:text-indigo-400 transition">NPM / ID</p>
                                        <p class="text-xl font-bold text-indigo-400 tracking-wider">{{ $profile['npm'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kesan Pesan Section -->
            <div x-show="activeTab === 'kesan'" 
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="h-full flex flex-col p-8 md:p-12 overflow-y-auto custom-scrollbar"
                 x-cloak>
                
                <div class="max-w-5xl mx-auto w-full">
                    <div class="mb-12">
                        <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
                            Kesan & <span class="text-gradient">Pesan</span>
                        </h2>
                        <div class="h-1.5 w-24 bg-gradient-to-r from-indigo-500 to-pink-500 rounded-full"></div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        <!-- Form Area -->
                        <div class="lg:col-span-5">
                            <div class="bg-white/5 rounded-[2rem] p-8 border border-white/5 shadow-inner">
                                <form action="{{ route('tugas.kesan-pesan.store') }}" method="POST" class="space-y-6">
                                    @csrf
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Nama Lengkap</label>
                                        <input type="text" name="nama" required placeholder="Masukkan nama..." 
                                               class="w-full bg-slate-900/50 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder:text-slate-600">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Alamat</label>
                                        <input type="text" name="alamat" required placeholder="Masukkan alamat..." 
                                               class="w-full bg-slate-900/50 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder:text-slate-600">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Kesan / Pesan</label>
                                        <textarea name="kesan" required rows="4" placeholder="Apa yang ingin Anda sampaikan?..." 
                                                  class="w-full bg-slate-900/50 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder:text-slate-600 resize-none"></textarea>
                                    </div>

                                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-300 transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-3">
                                        <i data-lucide="send" class="w-5 h-5"></i>
                                        Kirim Sekarang
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Data Table Area -->
                        <div class="lg:col-span-7">
                            <div class="bg-white/5 rounded-[2rem] border border-white/5 overflow-hidden shadow-2xl">
                                <div class="px-8 py-5 border-b border-white/5 flex items-center justify-between">
                                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Riwayat Pesan</h3>
                                    <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 text-[10px] font-bold rounded-full border border-indigo-500/20">
                                        {{ count($kesanPesan) }} Entri
                                    </span>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-white/5">
                                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">No</th>
                                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Nama</th>
                                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Alamat</th>
                                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Kesan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            @forelse($kesanPesan as $item)
                                            <tr class="hover:bg-white/5 transition group">
                                                <td class="px-6 py-4 text-sm font-bold text-slate-500">{{ $item['no'] }}</td>
                                                <td class="px-6 py-4">
                                                    <p class="text-sm font-bold text-white group-hover:text-indigo-400 transition">{{ $item['nama'] }}</p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-xs text-slate-400">{{ $item['alamat'] }}</p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-sm text-slate-300 italic">"{{ $item['kesan'] }}"</p>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center opacity-30">
                                                        <i data-lucide="message-square-off" class="w-12 h-12 mb-4"></i>
                                                        <p class="text-xs font-bold uppercase tracking-widest">Belum ada data</p>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification (Laravel session) -->
    @if(session('success'))
    <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 4000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] w-full max-w-sm px-4">
        <div class="bg-emerald-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4">
            <i data-lucide="check-circle" class="w-6 h-6"></i>
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Handle animation delays or specific logic if needed
        document.addEventListener('alpine:init', () => {
            // Additional Alpine state if needed
        });
    </script>
</body>
</html>
