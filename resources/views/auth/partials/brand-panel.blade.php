<div class="flex h-full flex-col items-center justify-center text-center">
    {{-- Favicon besar di tengah --}}
    <div class="mb-7">
        <span class="inline-flex h-24 w-24 items-center justify-center overflow-hidden rounded-3xl shadow-[0_16px_50px_rgba(0,0,0,0.25)] ring-4 ring-white/20 sm:h-28 sm:w-28">
            <img src="{{ asset('favicon-apar.svg') }}" alt="Logo PD Anugrah Utama" class="h-full w-full">
        </span>
    </div>

    {{-- Nama brand besar --}}
    <h2 class="text-3xl font-black uppercase tracking-wide text-white xl:text-4xl">
        PD Anugrah Utama
    </h2>

    {{-- Tagline singkat --}}
    <p class="mx-auto mt-4 max-w-xs text-sm leading-relaxed text-white/60">
        Melayani penjualan, refill, dan service APAR dengan proses yang mudah.
    </p>

    {{-- 3 poin layanan ringan --}}
    <ul class="mt-10 space-y-3.5 text-left">
        <li class="flex items-center gap-3">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/12 text-amber-300">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <span class="text-sm font-medium text-white/80">Penjualan APAR</span>
        </li>
        <li class="flex items-center gap-3">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/12 text-amber-300">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <span class="text-sm font-medium text-white/80">Refill & Service APAR</span>
        </li>
        <li class="flex items-center gap-3">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/12 text-amber-300">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <span class="text-sm font-medium text-white/80">Konsultasi WhatsApp</span>
        </li>
    </ul>
</div>
