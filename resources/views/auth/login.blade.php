<!DOCTYPE html>
<html lang="id">
<head>
    @section('title', 'Masuk - STAI Nurul Abror Al-Robbaniyin')
    @include('layouts.partials.head')
</head>
<body class="relative min-h-screen overflow-x-hidden bg-gradient-to-br from-[#0B1F3A] via-[#0A363D] to-[#062419] text-slate-800 antialiased">
    {{-- Latar Pola Titik Tipis & Ambient Blurred Light Orbs --}}
    <div aria-hidden="true" class="bg-dot-grid pointer-events-none fixed inset-0 opacity-10"></div>
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="animate-orb-slow absolute -top-24 -left-24 h-80 w-80 rounded-full bg-sky-400/25 blur-3xl sm:h-96 sm:w-96"></div>
        <div class="animate-orb-reverse absolute -right-24 -bottom-24 h-80 w-80 rounded-full bg-emerald-400/25 blur-3xl sm:h-96 sm:w-96"></div>
        <div class="animate-orb-slow absolute top-1/3 left-1/2 h-64 w-64 -translate-x-1/2 rounded-full bg-teal-400/15 blur-3xl"></div>
    </div>

    <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6 lg:p-8">
        {{-- Kartu Login Utama (Split-Screen Premium + Animasi Masuk Halus) --}}
        <div class="animate-login-enter grid w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/50 ring-1 ring-white/15 lg:grid-cols-12">
            {{-- Panel Kiri: Identitas Institusi (Gradien Biru ke Hijau Emerald) --}}
            <div class="relative flex flex-col justify-between overflow-hidden bg-gradient-to-br from-[#0F294A] via-[#0B3C3D] to-[#062419] p-7 text-white sm:p-10 lg:col-span-5">
                {{-- Dekorasi Cahaya Halus & Watermark Logo Kampus --}}
                <div aria-hidden="true" class="pointer-events-none absolute -top-16 -left-16 h-48 w-48 rounded-full bg-sky-400/15 blur-2xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -right-12 -bottom-12 h-48 w-48 rounded-full bg-emerald-400/15 blur-2xl"></div>
                <img
                    src="{{ asset('assets/img/logo-stainaa.png') }}"
                    alt=""
                    aria-hidden="true"
                    class="pointer-events-none absolute -right-14 -bottom-14 h-56 w-56 select-none object-contain opacity-[0.07]"
                >

                <div class="relative">
                    {{-- Logo STAINAA dalam Frame Kaca Glassmorphism --}}
                    <div class="animate-logo-enter flex flex-col items-center text-center lg:items-start lg:text-left">
                        <div class="group inline-flex h-20 w-20 items-center justify-center rounded-full border border-white/20 bg-white/10 p-2 shadow-[0_0_28px_rgba(56,189,248,0.22)] backdrop-blur-md transition duration-300 hover:scale-105 hover:border-emerald-300/50 hover:shadow-[0_0_32px_rgba(52,211,153,0.35)] sm:h-24 sm:w-24">
                            <div class="flex h-full w-full items-center justify-center rounded-full bg-white/95 p-1 shadow-inner">
                                <img
                                    src="{{ asset('assets/img/logo-stainaa.png') }}"
                                    alt="Logo STAI Nurul Abror Al-Robbaniyin"
                                    class="h-full w-full rounded-full object-contain drop-shadow-lg"
                                >
                            </div>
                        </div>

                        <h1 class="mt-5 text-xl font-extrabold leading-snug tracking-tight text-white sm:text-2xl">
                            STAI Nurul Abror Al-Robbaniyin
                        </h1>
                        <p class="mt-1.5 bg-gradient-to-r from-sky-300 via-teal-200 to-emerald-300 bg-clip-text text-[11px] font-extrabold uppercase tracking-[0.14em] text-transparent">
                            SISTEM APLIKASI QUISIONER &amp; EDOM
                        </p>
                        <p class="mt-3 text-sm leading-relaxed text-slate-200/85">
                            Portal Evaluasi Dosen Oleh Mahasiswa (EDOM) dan pengisian kuisioner akademik terintegrasi SIAKAD.
                        </p>
                    </div>
                </div>

                {{-- 2 Mini-Card Info Peran (Glassmorphism Cards) --}}
                <div class="relative mt-7 space-y-2.5 border-t border-white/10 pt-6">
                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 p-3 text-xs text-slate-200/90 backdrop-blur-md transition duration-200 hover:border-sky-300/30 hover:bg-white/10">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#38BDF8]/20 text-[#38BDF8] ring-1 ring-[#38BDF8]/30">
                            <span class="h-2 w-2 rounded-full bg-[#38BDF8]"></span>
                        </span>
                        <p class="leading-relaxed">
                            <strong class="font-bold text-white">Mahasiswa:</strong> Masuk menggunakan <strong>NIM</strong> dan password akun Anda.
                        </p>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 p-3 text-xs text-slate-200/90 backdrop-blur-md transition duration-200 hover:border-emerald-300/30 hover:bg-white/10">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#34D399]/20 text-[#34D399] ring-1 ring-[#34D399]/30">
                            <span class="h-2 w-2 rounded-full bg-[#34D399]"></span>
                        </span>
                        <p class="leading-relaxed">
                            <strong class="font-bold text-white">Admin:</strong> Masuk menggunakan <strong>Username</strong> administrator.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Panel Kanan: Form Login Interaktif --}}
            <div class="flex flex-col justify-between bg-white p-7 sm:p-10 lg:col-span-7">
                <div>
                    <div class="mb-6">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-teal-200/70 bg-gradient-to-r from-blue-50 to-emerald-50 px-3 py-1 text-xs font-bold text-teal-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                            Autentikasi Portal
                        </span>
                        <h2 class="mt-3 text-xl font-extrabold tracking-tight text-[#0F294A] sm:text-2xl">
                            Masuk ke Akun Anda
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Sistem akan mengarahkan Anda otomatis ke Panel Admin atau Portal Mahasiswa.
                        </p>
                    </div>

                    @include('layouts.partials.alerts')

                    <form id="login-form" method="POST" action="{{ route('login.post') }}" class="space-y-5">
                        @csrf

                        {{-- Field Username / NIM --}}
                        <div class="group">
                            <label for="username" class="block text-sm font-semibold text-slate-700 transition-colors group-focus-within:text-teal-700">
                                Username / NIM
                            </label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 transition-colors duration-200 group-focus-within:text-teal-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </span>
                                <input
                                    id="username"
                                    name="username"
                                    type="text"
                                    value="{{ old('username') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Masukkan Username atau NIM"
                                    class="block w-full rounded-xl border @error('username') border-rose-400 bg-rose-50/40 @else border-slate-300 bg-slate-50/40 @enderror py-3 pr-4 pl-11 text-sm text-slate-900 placeholder:text-slate-400 transition duration-200 focus:border-teal-600 focus:bg-white focus:ring-4 focus:ring-teal-500/15 focus:outline-none"
                                >
                            </div>
                            @error('username')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Field Password --}}
                        <div class="group">
                            <label for="password" class="block text-sm font-semibold text-slate-700 transition-colors group-focus-within:text-teal-700">
                                Password
                            </label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 transition-colors duration-200 group-focus-within:text-teal-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                </span>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Masukkan Password"
                                    class="block w-full rounded-xl border @error('password') border-rose-400 bg-rose-50/40 @else border-slate-300 bg-slate-50/40 @enderror py-3 pr-12 pl-11 text-sm text-slate-900 placeholder:text-slate-400 transition duration-200 focus:border-teal-600 focus:bg-white focus:ring-4 focus:ring-teal-500/15 focus:outline-none"
                                >
                                <button
                                    type="button"
                                    id="toggle-password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition-colors duration-200 hover:text-teal-700 focus:outline-none"
                                    aria-label="Tampilkan atau sembunyikan password"
                                >
                                    <svg id="eye-open" class="h-5 w-5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg id="eye-closed" class="hidden h-5 w-5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tombol Submit Gradien Biru -> Hijau Emerald dengan Hover Lift & Loading State --}}
                        <button
                            id="btn-login-submit"
                            type="submit"
                            class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#1D4ED8] via-[#0284C7] to-[#0B6B3A] px-4 py-3 text-sm font-bold text-white shadow-md shadow-teal-900/15 transition-all duration-200 hover:-translate-y-0.5 hover:from-[#1E40AF] hover:via-[#0369A1] hover:to-[#10B981] hover:shadow-lg hover:shadow-teal-600/25 focus:ring-4 focus:ring-teal-500/30 focus:outline-none disabled:pointer-events-none disabled:opacity-80"
                        >
                            <svg id="login-spinner" class="hidden h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="login-btn-text">Masuk</span>
                            <svg id="login-arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="mt-8 border-t border-slate-100 pt-5 text-center">
                    <p class="text-xs font-medium text-slate-400">
                        &copy; {{ date('Y') }} STAI Nurul Abror Al-Robbaniyin &middot; Alasbuluh, Wongsorejo, Banyuwangi.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
