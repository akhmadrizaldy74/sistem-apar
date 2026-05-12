@extends('layouts.public')

@section('title', 'Profil Pelanggan')

@section('content')
    <section class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-10 sm:py-14">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8" data-reveal>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke beranda
                </a>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.25em] text-red-600">Akun Pelanggan</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-gray-900 sm:text-4xl">Profil Saya</h1>
                        <p class="mt-2 max-w-2xl text-sm font-medium text-gray-500">
                            Lengkapi alamat pengiriman default supaya saat belanja berikutnya Anda tidak perlu isi data alamat lagi.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[2rem] border border-gray-100 bg-white p-4 shadow-xl shadow-gray-200/40 sm:p-8" data-reveal>
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-[2rem] border border-gray-100 bg-white p-4 shadow-xl shadow-gray-200/40 sm:p-8" data-reveal>
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="rounded-[2rem] border border-gray-100 bg-white p-4 shadow-xl shadow-gray-200/40 sm:p-8" data-reveal>
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
