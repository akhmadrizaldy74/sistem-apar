<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(auth()->user()->isAdmin())
                        <x-nav-link :href="route('admin.akun.index')" :active="request()->routeIs('admin.akun.*')">
                            Manajemen Akun
                        </x-nav-link>
                        <x-nav-link :href="route('admin.pelanggan.index')" :active="request()->routeIs('admin.pelanggan.*')">
                            Pelanggan
                        </x-nav-link>
                        <x-nav-link :href="route('admin.unit-apar.index')" :active="request()->routeIs('admin.unit-apar.*')">
                            Unit APAR
                        </x-nav-link>
                        <x-nav-link :href="route('admin.pesanan.index')" :active="request()->routeIs('admin.pesanan.*') || request()->routeIs('admin.service.*') || request()->routeIs('admin.refill.*')">
                            Pesanan
                        </x-nav-link>
                        <x-nav-link :href="route('admin.stok.index')" :active="request()->routeIs('admin.stok.*')">
                            Stok
                        </x-nav-link>
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                            <x-nav-link :href="'#'" @click.prevent="open = ! open" class="cursor-pointer" :class="{'text-red-700 font-bold': open || request()->routeIs('admin.jenis-apar.*') || request()->routeIs('admin.jenis-refill.*') || request()->routeIs('admin.peralatan.*') || request()->routeIs('admin.service-paket.*')}">
                                Master Data
                                <div class="ms-1 inline-block">
                                    <svg class="fill-current h-3 w-3 transition-transform duration-200" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-nav-link>
                            <div x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2"
                                    class="absolute z-50 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5"
                                    style="display: none;"
                                    @click="open = false">
                                <div class="py-2">
                                    <a href="{{ route('admin.jenis-apar.index') }}"
                                       class="block px-5 py-2.5 text-sm font-medium transition duration-150 ease-in-out
                                              {{ request()->routeIs('admin.jenis-apar.*') ? 'text-red-700 bg-red-50 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                                        Jenis APAR
                                    </a>
                                    <a href="{{ route('admin.jenis-refill.index') }}"
                                       class="block px-5 py-2.5 text-sm font-medium transition duration-150 ease-in-out
                                              {{ request()->routeIs('admin.jenis-refill.*') ? 'text-red-700 bg-red-50 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                                        Jenis Refil
                                    </a>
                                    <a href="{{ route('admin.peralatan.index') }}"
                                       class="block px-5 py-2.5 text-sm font-medium transition duration-150 ease-in-out
                                              {{ request()->routeIs('admin.peralatan.*', 'admin.service-paket.*') ? 'text-red-700 bg-red-50 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                                        Master Service &amp; Peralatan
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
